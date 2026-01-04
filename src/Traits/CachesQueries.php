<?php

namespace Nehal\ModelCaching\Traits;

use Nehal\ModelCaching\Builders\CachingBuilder;
use Nehal\ModelCaching\Observers\CacheInvalidationObserver;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Nehal\ModelCaching\Contracts\CacheKeyGeneratorInterface;

trait CachesQueries
{
    use Caching;
    use ModelCaching;

    /**
     * Boot the trait.
     */
    public static function bootCachesQueries()
    {
        static::observe(app(CacheInvalidationObserver::class));
    }

    /**
     * Create a new Eloquent query builder for the model.
     */
    public function newEloquentBuilder($query)
    {
        $builder = new CachingBuilder($query);
        $builder->setModel($this);
        
        // Resolve dependencies
        $store = $this->getCacheStore();
        $cache = app('cache');
        
        if ($store) {
            $cache = $cache->store($store);
        } else {
            $cache = $cache->store();
        }

        $builder->setCacheRepository($cache);
        $builder->setKeyGenerator(app(CacheKeyGeneratorInterface::class));

        return $builder;
    }
}

