<?php

namespace Nehal\ModelCaching\Cache;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Nehal\ModelCaching\Contracts\CacheKeyGeneratorInterface;

class QueryCacheKeyGenerator implements CacheKeyGeneratorInterface
{
    public function make(Model $model, QueryBuilder $query, array $columns = ['*']): string
    {
        $components = [
            'class' => get_class($model),
            'connection' => $model->getConnectionName(),
            'sql' => $query->toSql(),
            'bindings' => $query->getBindings(),
            'columns' => $columns,
        ];

        return $this->getPrefix($model) . ':' . sha1(serialize($components));
    }

    public function makeTags(Model $model): array
    {
        return [$model->getTable()];
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
