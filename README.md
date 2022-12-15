# DBCache
DataObject-based cache for SilverStripe.

## Requirements
* SilverStripe 4

## Installation
```
composer require toastnz/dbcache
```

Set the default expiry time in minutes for cache entries
```yml
Toast\DBCache\Helpers\DBCache:
  expiry_minutes: 0
```

Whether to clear all cached data when <b>?flush</b> is invoked
```yml
Toast\DBCache\Extensions\ControllerDBCacheExtension:
  clear_on_flush: true
```

Manipulate cache entries
```php
// Cache "filters" for 10 minutes under the namespace "product"
DBCache::set('product.filters', json_encode($products), 10);

// Retrieve from cache
DBCache::get('product.filters');

// Remove from cache
DBCache::clear('product.filters');

// Flush cached data for the "product" namespace
DBCache::flush('product');

// Flush all cached data
DBCache::flush();
```
