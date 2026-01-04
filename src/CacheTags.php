<?php

namespace Nehal\ModelCaching;

use Nehal\ModelCaching\Traits\CachePrefixing;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;

class CacheTags
{
    use CachePrefixing;

    protected array $eagerLoad;
    protected Model $model;
    protected $query;
    protected ?Collection $cachedTags = null;

    public function __construct(
        array $eagerLoad,
        Model $model,
        $query
    ) {
        $this->eagerLoad = $eagerLoad;
        $this->model = $model;
        $this->query = $query;
    }

    public function make(): array
    {
        if ($this->cachedTags !== null) {
            return $this->cachedTags->toArray();
        }

        $this->cachedTags = collect($this->eagerLoad)
            ->keys()
            ->map(fn(string $relationName) => $this->getRelationTag($relationName))
            ->prepend($this->getModelTag())
            ->values()
            ->unique();

        return $this->cachedTags->toArray();
    }

    protected function getRelationTag(string $relationName): string
    {
        $relation = $this->getRelation($relationName);
        $modelClass = get_class($relation->getQuery()->getModel());
        
        return $this->getCachePrefix() . Str::slug($modelClass);
    }

    protected function getModelTag(): string
    {
        return $this->getCachePrefix() . Str::slug(get_class($this->model));
    }

    protected function getRelation(string $relationName): Relation
    {
        return collect(explode('.', $relationName))
            ->reduce(function ($carry, $name) {
                $carry = $carry ?: $this->model;
                $carry = $this->getRelatedModel($carry);
                return $carry->{$name}();
            });
    }

    protected function getRelatedModel($carry): Model
    {
        if ($carry instanceof Relation) {
            return $carry->getQuery()->getModel();
        }

        return $carry;
    }

    public function getCacheKey(): string
    {
        return $this->getCachePrefix() . Str::slug(get_class($this->model)) . ':' . $this->model->getKey();
    }

    public function getCacheDuration(): int
    {
        return config('model-caching.cache_duration', 3600);
    }

    public function getCacheTags(): array
    {
        return $this->make();
    }
}
