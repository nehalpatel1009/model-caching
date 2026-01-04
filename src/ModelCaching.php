<?php

namespace Nehal\ModelCaching;

use Illuminate\Container\Container;
use Illuminate\Contracts\Cache\Repository as CacheRepository;

class ModelCaching
{
    /**
     * Run a closure with caching disabled.
     */
    public function runDisabled(callable $closure)
    {
        $originalSetting = config('model-caching.enabled', true);
        
        config(['model-caching.enabled' => false]);

        try {
            $result = $closure();
        } finally {
            config(['model-caching.enabled' => $originalSetting]);
        }

        return $result;
    }

    /**
     * Clear cache for a specific model.
     */
    public function clearModel(string $modelClass): bool
    {
        if (!class_exists($modelClass)) {
            return false;
        }

        $model = new $modelClass;
        $keyGenerator = Container::getInstance()->make(\Nehal\ModelCaching\Contracts\CacheKeyGeneratorInterface::class);
        $tags = $keyGenerator->makeTags($model, []);
        
        $cache = Container::getInstance()->make('cache');
        $store = config('model-caching.store');
        
        if ($store) {
            $cache = $cache->store($store);
        }

        $store = $cache->getStore();
        $supportsTags = method_exists($store, 'tags') || 
                       $store instanceof \Illuminate\Cache\TaggableStore;

        if ($supportsTags) {
            return $cache->tags($tags)->flush();
        }

        return $cache->flush();
    }

    /**
     * Clear all model caches.
     */
    public function clearAll(): bool
    {
        $cache = Container::getInstance()->make('cache');
        $store = config('model-caching.store');
        
        if ($store) {
            $cache = $cache->store($store);
        }

        return $cache->flush();
    }
}
