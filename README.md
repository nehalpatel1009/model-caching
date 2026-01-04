# Eloquent Power Cache

A lightweight, opt-in model caching package for Laravel.

## Installation

```bash
composer require nehalpatel1009/model-caching
```

## Usage

Simply add the `CachesQueries` trait to any Eloquent model you want to cache.

```php
use Nehal\ModelCaching\Traits\CachesQueries;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use CachesQueries;
    
    // Optional: Configure cache TTL (seconds)
    protected $cacheTtl = 3600;
    
    // Optional: Configure cache prefix
    protected $cachePrefix = 'products_cache';
}
```

### How it works

- Automatically caches `get()`, `first()`, `paginate()`, and `find()` queries.
- Cache keys are generated based on the query SQL, bindings, and model class.
- Automatically flushes the cache when the model is created, updated, deleted, or restored.
- Uses Cache Tags (if supported by your driver) to scope invalidation to the specific model table.

### Disabling Cache

You can disable caching for a specific query:

```php
$products = Product::withoutCache()->get();
```

### Configuration

You can override the cache store per model:

```php
protected $cacheStore = 'redis';
```

## Testing

```bash
composer test
```
