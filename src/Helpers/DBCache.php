<?php

namespace Toast\DBCache\Helpers;

use SilverStripe\ORM\DB;
use Toast\DBCache\Models\DBCacheEntry;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Injector\Injectable;

class DBCache
{
    use Injectable;
    use Configurable;

    private static $expiry_minutes;

    public static function set(string $key, string $value, int $expiryMinutes = 0)
    {
        if (!$expiryMinutes) {
            if ($defaultExpiryMinutes = (int)self::config()->expiry_minutes) {
                $expiryMinutes = $defaultExpiryMinutes;
            }
        }

        if (!$cacheEntry = DBCacheEntry::get()->find('Key', $key)) {
            $cacheEntry = DBCacheEntry::create();
            $cacheEntry->Key = $key;
        }

        $cacheEntry->Value = $value;
        $cacheEntry->Expires = $expiryMinutes ? date('Y-m-d H:i:s', strtotime('+' . $expiryMinutes . ' minutes')) : null;
        $cacheEntry->write();
    }

    public static function get(string $key)
    {
        self::expiryIfRequired($key);

        if ($cacheEntry = DBCacheEntry::get()->find('Key', $key)) {
            return $cacheEntry->Value;
        }
    }

    public static function clear(string $key)
    {
        if ($cacheEntry = DBCacheEntry::get()->find('Key', $key)) {
            $cacheEntry->delete();
        }
    }

    public static function flush($namespace = null)    
    {
        if ($namespace) {
            $cacheEntries = DBCacheEntry::get()
                ->filter('Key:StartsWith', $namespace . '.');
                
            foreach ($cacheEntries as $cacheEntry) {
                $cacheEntry->delete();
            }

        } else {
            DB::query('TRUNCATE TABLE DBCacheEntry');
        }
    }

    private static function expiryIfRequired(string $key)
    {
        if ($cacheEntry = DBCacheEntry::get()->find('Key', $key)) {
            if ($cacheEntry->Expires && strtotime($cacheEntry->Expires) < time()) {
                $cacheEntry->delete();
            }
        }
    }


}