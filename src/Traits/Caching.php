<?php

namespace Nehal\ModelCaching\Traits;

use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Cache\TaggableStore;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Model;
use Nehal\ModelCaching\Contracts\CacheKeyGeneratorInterface;

trait Caching
{
    protected bool $shouldCache = true;
    protected ?int $cacheCooldownSeconds = null;
    protected ?int $cacheTtl = null;

    /**
     * Get the cache repository instance.
     */
    protected function getCacheRepository(): CacheRepository
    {
        $cache = Container::getInstance()->make('cache');
        $store = $this->getCacheStore();

        if ($store) {
            $cache = $cache->store($store);
        }

        return $cache;
    }

    /**
     * Get cache store name from config or model property.
     */
    protected function getCacheStore(): ?string
    {
        if (property_exists($this, 'cacheStore') && $this->cacheStore) {
            return $this->cacheStore;
        }

        return config('model-caching.store');
    }

    /**
     * Check if caching is enabled.
     */
    protected function isCachingEnabled(): bool
    {
        if (!config('model-caching.enabled', true)) {
            return false;
        }

        return $this->shouldCache;
    }

    /**
     * Disable caching for this query.
     */
    public function disableCache()
    {
        $this->shouldCache = false;
        return $this;
    }

    /**
     * Enable caching for this query.
     */
    public function enableCache()
    {
        $this->shouldCache = true;
        return $this;
    }

    /**
     * Set cache TTL.
     */
    public function cacheFor(int $seconds)
    {
        $this->cacheTtl = $seconds;
        return $this;
    }

    /**
     * Set cache cooldown period.
     */
    public function withCacheCooldownSeconds(?int $seconds = null)
    {
        $this->cacheCooldownSeconds = $seconds ?? $this->getDefaultCacheCooldownSeconds();
        return $this;
    }

    /**
     * Get default cache cooldown seconds from model or config.
     */
    protected function getDefaultCacheCooldownSeconds(): ?int
    {
        if (property_exists($this, 'cacheCooldownSeconds')) {
            return $this->cacheCooldownSeconds;
        }

        return null;
    }

    /**
     * Get cache TTL.
     */
    protected function getCacheTtl(): int
    {
        if ($this->cacheTtl !== null) {
            return $this->cacheTtl;
        }

        if (property_exists($this, 'cacheTtl')) {
            return $this->cacheTtl;
        }

        return config('model-caching.cache_duration', 3600);
    }

    /**
     * Get cache key generator.
     */
    protected function getCacheKeyGenerator(): CacheKeyGeneratorInterface
    {
        return Container::getInstance()->make(CacheKeyGeneratorInterface::class);
    }

    /**
     * Check if cache store supports tags.
     */
    protected function cacheSupportsTags(CacheRepository $cache): bool
    {
        return method_exists($cache->getStore(), 'tags') || 
               $cache->getStore() instanceof TaggableStore;
    }
}

