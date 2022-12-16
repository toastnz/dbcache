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

    protected static $disabled_namespaces = false;

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

        $namespace = self::getNamespaceFromKey($key);
        $statuses = self::status();

        if (isset($statuses[$namespace]) && !$statuses[$namespace]) {
            return null;
        }

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

    public static function disable($namespace = null)
    {
        if ($namespace) {
            if (!is_array(self::$disabled_namespaces)) {
                self::$disabled_namespaces = [];
            }

            self::$disabled_namespaces[] = $namespace;

        } else {
            self::$disabled_namespaces = true;

        }        
    }

    public static function enable($namespace = null)
    {
        if ($namespace) {
            if (is_array(self::$disabled_namespaces)) {
                if (($key = array_search($namespace, self::$disabled_namespaces)) !== false) {
                    unset(self::$disabled_namespaces[$key]);
                }

                if (!count(self::$disabled_namespaces)) {
                    self::$disabled_namespaces = null;
                }

            } else {
                self::$disabled_namespaces = null;                
            }

        } else {
            self::$disabled_namespaces = null;

        }        
    }    

    public static function status()
    {
        $recordedNamespaces = [];

        foreach(DBCacheEntry::get() as $entry) {
            if ($entry->Key) {
                if (strstr($entry->Key, '.')) {
                    if ($namespace = self::getNamespaceFromKey($entry->Key)) {
                        if (!in_array($namespace, $recordedNamespaces)) {
                            $recordedNamespaces[] = $namespace;
                        }
                    }
                }                
            }
        }

        $disabledNamespaces = array_values(is_array(self::$disabled_namespaces) ? self::$disabled_namespaces : []);
        
        $output = [];
        foreach(array_unique($recordedNamespaces + $disabledNamespaces) as $namespace) {
            $output[$namespace] = (self::$disabled_namespaces === true) || in_array($namespace, $disabledNamespaces) ? false : true;
        }

        return $output;
    }


    private static function expiryIfRequired(string $key)
    {
        if ($cacheEntry = DBCacheEntry::get()->find('Key', $key)) {
            if ($cacheEntry->Expires && strtotime($cacheEntry->Expires) < time()) {
                $cacheEntry->delete();
            }
        }
    }

    private static function getNamespaceFromKey(string $key)
    {
        if (strstr($key, '.')) {
            return explode('.', $key)[0];
        }
    }


}