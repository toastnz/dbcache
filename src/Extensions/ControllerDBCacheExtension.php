<?php

namespace Toast\DBCache\Extensions;

use SilverStripe\Core\Extension;
use Toast\DBCache\Helpers\DBCache;
use SilverStripe\Security\Permission;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Injector\Injectable;

class ControllerDBCacheExtension extends Extension
{
    use Injectable;
    use Configurable;

    private static $clear_on_flush;

    public function onBeforeInit()
    {
        if (Permission::check('ADMIN')) {
            if ($this->owner->getRequest()->requestVar('flush')) {
                if ($this->config()->clear_on_flush) {
                    DBCache::flush();
                }
            }
        }
    }

}