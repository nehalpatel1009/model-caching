<?php

namespace Nehal\ModelCaching\Observers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Nehal\ModelCaching\Contracts\CacheKeyGeneratorInterface;

class CacheInvalidationObserver
{
    protected CacheRepository $cache;
    protected CacheKeyGeneratorInterface $keyGenerator;

    public function __construct(CacheRepository $cache, CacheKeyGeneratorInterface $keyGenerator)
    {
        $this->cache = $cache;
        $this->keyGenerator = $keyGenerator;
    }

    public function created(Model $model)
    {
        $this->invalidate($model);
    }

    public function updated(Model $model)
    {
        $this->invalidate($model);
    }

    public function deleted(Model $model)
    {
        $this->invalidate($model);
    }

    public function restored(Model $model)
    {
        $this->invalidate($model);
    }

    protected function invalidate(Model $model)
    {
        // When invalidating, we only have the model itself, not eager loads
        // The tags will include the model's table, which will invalidate
        // all caches tagged with this model (including those with eager loads)
        $tags = $this->keyGenerator->makeTags($model, []);

        // Check if cache store supports tags
        $store = $this->cache->getStore();
        $supportsTags = method_exists($store, 'tags') || 
                       $store instanceof \Illuminate\Cache\TaggableStore;

        try {
            if ($supportsTags) {
                // Use reflection or call_user_func to avoid static analysis issues
                if (is_callable([$this->cache, 'tags'])) {
                    $taggedCache = call_user_func([$this->cache, 'tags'], $tags);
                    if (is_callable([$taggedCache, 'flush'])) {
                        call_user_func([$taggedCache, 'flush']);
                    }
                }
            } else {
                // For non-taggable stores, flush entire cache
                if (is_callable([$this->cache, 'flush'])) {
                    call_user_func([$this->cache, 'flush']);
                }
            }
        } catch (\Exception $e) {
            // Silently fail if cache operations are not supported
            // This can happen with certain cache drivers
        }
    }
}
