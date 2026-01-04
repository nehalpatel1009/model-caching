<?php

namespace Nehal\ModelCaching\Cache;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Nehal\ModelCaching\Contracts\CacheKeyGeneratorInterface;
use Illuminate\Support\Str;

class QueryCacheKeyGenerator implements CacheKeyGeneratorInterface
{
    public function make(Model $model, QueryBuilder $query, array $columns = ['*'], array $eagerLoad = []): string
    {
        $components = [
            'class' => get_class($model),
            'connection' => $model->getConnectionName(),
            'sql' => $query->toSql(),
            'bindings' => $query->getBindings(),
            'columns' => $columns,
            'eagerLoad' => $this->normalizeEagerLoad($eagerLoad),
        ];

        return $this->getPrefix($model) . ':' . sha1(serialize($components));
    }

    public function makeTags(Model $model, array $eagerLoad = []): array
    {
        $tags = [$model->getTable()];

        // Add tags for eager-loaded relationships
        if (!empty($eagerLoad)) {
            foreach (array_keys($eagerLoad) as $relationName) {
                try {
                    $relation = $this->getRelation($model, $relationName);
                    $relatedModel = $relation->getRelated();
                    $tags[] = $relatedModel->getTable();
                } catch (\Exception $e) {
                    // Skip invalid relationships
                    continue;
                }
            }
        }

        return array_unique($tags);
    }

    /**
     * Normalize eager load array for consistent cache key generation.
     */
    protected function normalizeEagerLoad(array $eagerLoad): array
    {
        // Sort keys to ensure consistent cache keys regardless of order
        ksort($eagerLoad);
        
        // Convert constraints to a serializable format
        $normalized = [];
        foreach ($eagerLoad as $relation => $constraints) {
            if (is_callable($constraints)) {
                // For callable constraints, we can't serialize them
                // Use the relation name only
                $normalized[$relation] = null;
            } else {
                $normalized[$relation] = $constraints;
            }
        }
        
        return $normalized;
    }

    /**
     * Get a relation instance from the model.
     */
    protected function getRelation(Model $model, string $relationName): Relation
    {
        return collect(explode('.', $relationName))
            ->reduce(function ($carry, $name) use ($model) {
                $carry = $carry ?: $model;
                $carry = $this->getRelatedModel($carry);
                return $carry->{$name}();
            });
    }

    /**
     * Get the related model from a relation or model instance.
     */
    protected function getRelatedModel($carry): Model
    {
        if ($carry instanceof Relation) {
            return $carry->getQuery()->getModel();
        }

        return $carry;
    }

    protected function getPrefix(Model $model): string
    {
        $prefix = config('model-caching.cache-prefix', 'model-cache');
        
        if (property_exists($model, 'cachePrefix') && $model->cachePrefix) {
            $prefix = $model->cachePrefix;
        }

        $connection = $model->getConnectionName();
        $database = $model->getConnection()->getDatabaseName();

        if (config('model-caching.use-database-keying', true)) {
            $prefix = "{$prefix}:{$connection}:{$database}";
        }

        return $prefix;
    }
}
