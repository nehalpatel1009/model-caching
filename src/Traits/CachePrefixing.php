<?php

namespace Nehal\ModelCaching\Traits;

use Illuminate\Container\Container;
use Illuminate\Support\Str;

trait CachePrefixing
{
    protected ?string $cachedPrefix = null;

    protected function getCachePrefix(): string
    {
        if ($this->cachedPrefix !== null) {
            return $this->cachedPrefix;
        }

        $prefix = collect([
            'nehal:model-caching',
            $this->getDatabasePrefix(),
            $this->getConfigPrefix(),
            $this->getModelPrefix()
        ])
        ->filter()
        ->implode(':');

        $this->cachedPrefix = $prefix . ':';
        return $this->cachedPrefix;
    }

    protected function getDatabasePrefix(): ?string
    {
        if (!config('model-caching.use-database-keying', false)) {
            return null;
        }

        return collect([
            $this->getConnectionName(),
            $this->getDatabaseName()
        ])
        ->filter()
        ->implode(':');
    }

    protected function getConfigPrefix(): ?string
    {
        return config('model-caching.cache-prefix');
    }

    protected function getModelPrefix(): ?string
    {
        if (!$this->model || !property_exists($this->model, 'cachePrefix')) {
            return null;
        }

        return $this->model->cachePrefix;
    }

    protected function getDatabaseName(): string
    {
        return $this->model->getConnection()->getDatabaseName();
    }

    protected function getConnectionName(): string
    {
        return $this->model->getConnection()->getName();
    }

    protected function slugify(string $value): string
    {
        return Str::slug($value);
    }

    public function clearCachePrefix(): void
    {
        $this->cachedPrefix = null;
    }
}
