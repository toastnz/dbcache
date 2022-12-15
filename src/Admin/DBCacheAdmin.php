<?php

namespace Toast\DBCache\Admin;

use SilverStripe\Admin\ModelAdmin;
use Toast\DBCache\Models\DBCacheEntry;

class DBCacheAdmin extends ModelAdmin
{
    private static $url_segment = 'dbcache';

    private static $menu_title = 'Cached Content';

    private static $menu_icon_class = 'font-icon-p-list';

    public $showImportForm = false;

    private static $managed_models = [
        DBCacheEntry::class => [
            'title' => 'Cached Entries',
        ]
    ];


}
