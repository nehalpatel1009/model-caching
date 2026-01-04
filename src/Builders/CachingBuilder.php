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

        $key = $this->generateCacheKey($columns);
        $tags = $this->keyGenerator->makeTags($this->getModel());
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

        $key = $this->generateCacheKey($columns, 'first');
        $tags = $this->keyGenerator->makeTags($this->getModel());
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

        $key = $this->generateCacheKey($columns, "find:{$id}");
        $tags = $this->keyGenerator->makeTags($this->getModel());
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

        $key = $this->generateCacheKey($columns, 'findMany:' . implode(',', (array) $ids));
        $tags = $this->keyGenerator->makeTags($this->getModel());
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

        $key = $this->generateCacheKey($columns, "paginate:{$page}:{$perPage}");
        $tags = $this->keyGenerator->makeTags($this->getModel());
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

        $key = $this->generateCacheKey($columns, "simplePaginate:{$page}:{$perPage}");
        $tags = $this->keyGenerator->makeTags($this->getModel());
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

        $key = $this->generateCacheKey(['*'], 'count');
        $tags = $this->keyGenerator->makeTags($this->getModel());
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

        $key = $this->generateCacheKey(['*'], 'exists');
        $tags = $this->keyGenerator->makeTags($this->getModel());
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
    protected function generateCacheKey($columns, $suffix = ''): string
    {
        $baseKey = $this->keyGenerator->make($this->getModel(), $this->getQuery(), $columns);
        
        if ($suffix) {
            return $baseKey . ':' . $suffix;
        }

        return $baseKey;
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

