<?php

namespace Nehal\ModelCaching\Builders;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Nehal\ModelCaching\Contracts\CacheKeyGeneratorInterface;
use Nehal\ModelCaching\Traits\Caching;
use Illuminate\Cache\TaggableStore;

class CachingBuilder extends Builder
{
    use Caching;

    protected CacheRepository $cache;
    protected CacheKeyGeneratorInterface $keyGenerator;
    protected bool $avoidCache = false;

    /**
     * Set the cache repository.
     */
    public function setCacheRepository(CacheRepository $cache): self
    {
        $this->cache = $cache;
        return $this;
    }

    /**
     * Set the cache key generator.
     */
    public function setKeyGenerator(CacheKeyGeneratorInterface $keyGenerator): self
    {
        $this->keyGenerator = $keyGenerator;
        return $this;
    }

    /**
     * Disable caching for this query.
     */
    public function disableCache()
    {
        $this->avoidCache = true;
        $this->shouldCache = false;
        return $this;
    }

    /**
     * Alias for disableCache() for better API consistency.
     */
    public function withoutCache()
    {
        return $this->disableCache();
    }

    /**
     * Enable caching for this query.
     */
    public function enableCache()
    {
        $this->avoidCache = false;
        $this->shouldCache = true;
        return $this;
    }

    /**
     * Execute the query as a "select" statement.
     */
    public function get($columns = ['*'])
    {
        if (!$this->shouldCache()) {
            return parent::get($columns);
        }

        $eagerLoad = $this->getEagerLoads();
        $key = $this->generateCacheKey($columns, '', $eagerLoad);
        $tags = $this->keyGenerator->makeTags($this->getModel(), $eagerLoad);
        $ttl = $this->getCacheTtl();

        $callback = function () use ($columns) {
            return parent::get($columns);
        };

        return $this->remember($key, $tags, $ttl, $callback);
    }

    /**
     * Get the first record matching the attributes.
     */
    public function first($columns = ['*'])
    {
        if (!$this->shouldCache()) {
            return parent::first($columns);
        }

        $eagerLoad = $this->getEagerLoads();
        $key = $this->generateCacheKey($columns, 'first', $eagerLoad);
        $tags = $this->keyGenerator->makeTags($this->getModel(), $eagerLoad);
        $ttl = $this->getCacheTtl();

        $callback = function () use ($columns) {
            return parent::first($columns);
        };

        return $this->remember($key, $tags, $ttl, $callback);
    }

    /**
     * Find a model by its primary key.
     */
    public function find($id, $columns = ['*'])
    {
        if (!$this->shouldCache()) {
            return parent::find($id, $columns);
        }

        $eagerLoad = $this->getEagerLoads();
        $key = $this->generateCacheKey($columns, "find:{$id}", $eagerLoad);
        $tags = $this->keyGenerator->makeTags($this->getModel(), $eagerLoad);
        $ttl = $this->getCacheTtl();

        $callback = function () use ($id, $columns) {
            return parent::find($id, $columns);
        };

        return $this->remember($key, $tags, $ttl, $callback);
    }

    /**
     * Find multiple models by their primary keys.
     */
    public function findMany($ids, $columns = ['*'])
    {
        if (!$this->shouldCache()) {
            return parent::findMany($ids, $columns);
        }

        $eagerLoad = $this->getEagerLoads();
        $key = $this->generateCacheKey($columns, 'findMany:' . implode(',', (array) $ids), $eagerLoad);
        $tags = $this->keyGenerator->makeTags($this->getModel(), $eagerLoad);
        $ttl = $this->getCacheTtl();

        $callback = function () use ($ids, $columns) {
            return parent::findMany($ids, $columns);
        };

        return $this->remember($key, $tags, $ttl, $callback);
    }

    /**
     * Paginate the given query.
     */
    public function paginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null, $total = null)
    {
        if (!$this->shouldCache()) {
            return parent::paginate($perPage, $columns, $pageName, $page, $total);
        }

        $page = $page ?: \Illuminate\Pagination\Paginator::resolveCurrentPage($pageName);
        $perPage = $perPage ?: $this->getModel()->getPerPage();

        $eagerLoad = $this->getEagerLoads();
        $key = $this->generateCacheKey($columns, "paginate:{$page}:{$perPage}", $eagerLoad);
        $tags = $this->keyGenerator->makeTags($this->getModel(), $eagerLoad);
        $ttl = $this->getCacheTtl();

        $callback = function () use ($perPage, $columns, $pageName, $page, $total) {
            return parent::paginate($perPage, $columns, $pageName, $page, $total);
        };

        return $this->remember($key, $tags, $ttl, $callback);
    }

    /**
     * Get a paginator only supporting simple next and previous links.
     */
    public function simplePaginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null)
    {
        if (!$this->shouldCache()) {
            return parent::simplePaginate($perPage, $columns, $pageName, $page);
        }

        $page = $page ?: \Illuminate\Pagination\Paginator::resolveCurrentPage($pageName);
        $perPage = $perPage ?: $this->getModel()->getPerPage();

        $eagerLoad = $this->getEagerLoads();
        $key = $this->generateCacheKey($columns, "simplePaginate:{$page}:{$perPage}", $eagerLoad);
        $tags = $this->keyGenerator->makeTags($this->getModel(), $eagerLoad);
        $ttl = $this->getCacheTtl();

        $callback = function () use ($perPage, $columns, $pageName, $page) {
            return parent::simplePaginate($perPage, $columns, $pageName, $page);
        };

        return $this->remember($key, $tags, $ttl, $callback);
    }

    /**
     * Get the count of records.
     */
    public function count($columns = '*')
    {
        if (!$this->shouldCache()) {
            return parent::count($columns);
        }

        $eagerLoad = $this->getEagerLoads();
        $key = $this->generateCacheKey(['*'], 'count', $eagerLoad);
        $tags = $this->keyGenerator->makeTags($this->getModel(), $eagerLoad);
        $ttl = $this->getCacheTtl();

        $callback = function () use ($columns) {
            return parent::count($columns);
        };

        return $this->remember($key, $tags, $ttl, $callback);
    }

    /**
     * Determine if any rows exist for the current query.
     */
    public function exists()
    {
        if (!$this->shouldCache()) {
            return parent::exists();
        }

        $eagerLoad = $this->getEagerLoads();
        $key = $this->generateCacheKey(['*'], 'exists', $eagerLoad);
        $tags = $this->keyGenerator->makeTags($this->getModel(), $eagerLoad);
        $ttl = $this->getCacheTtl();

        $callback = function () {
            return parent::exists();
        };

        return $this->remember($key, $tags, $ttl, $callback);
    }

    /**
     * Check if caching should be used.
     */
    protected function shouldCache(): bool
    {
        if ($this->avoidCache) {
            return false;
        }

        return $this->isCachingEnabled();
    }

    /**
     * Generate cache key.
     */
    protected function generateCacheKey($columns, $suffix = '', array $eagerLoad = []): string
    {
        $baseKey = $this->keyGenerator->make($this->getModel(), $this->getQuery(), $columns, $eagerLoad);
        
        if ($suffix) {
            return $baseKey . ':' . $suffix;
        }

        return $baseKey;
    }

    /**
     * Get eager load relationships from the builder.
     */
    protected function getEagerLoads(): array
    {
        // Access the eagerLoad property from the parent Builder class
        if (property_exists($this, 'eagerLoad') && is_array($this->eagerLoad)) {
            return $this->eagerLoad;
        }

        return [];
    }

    /**
     * Remember the result in cache.
     */
    protected function remember(string $key, array $tags, int $ttl, callable $callback)
    {
        if ($this->cacheSupportsTags($this->cache)) {
            return $this->cache->tags($tags)->remember($key, $ttl, $callback);
        }

        return $this->cache->remember($key, $ttl, $callback);
    }
}

