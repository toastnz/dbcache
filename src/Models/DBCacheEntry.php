<?php

namespace Toast\DBCache\Models;

use SilverStripe\ORM\DataObject;

class DBCacheEntry extends DataObject
{
    private static $table_name = 'DBCacheEntry';

    private static $db = [
        'Key' => 'Varchar(255)',
        'Value' => 'Text',
        'Expires' => 'Datetime'
    ];

    private static $summary_fields = [
        'Key' => 'Key',
        'Size' => 'Size',
        'LastEdited.Nice' => 'Last Updated',
        'ExpiresForSummary' => 'Expires On'
    ];

    private static $searchable_fields = [
        'Key'
    ];

    private static $default_sort = 'LastEdited DESC';
    
    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->removeByName('Value');

        foreach ($fields as $field) {
            $field->setReadonly(true);
        }
        
        return $fields;
    }

    public function getSize()
    {
        return $this->formatBytes(strlen((string)$this->Value));
    }

    public function getExpiresForSummary()
    {
        if ($this->Expires) {
            return $this->dbObject('Expires')->Nice();
        }

        return 'Never';
    }

    private function formatBytes($bytes, $precision = 2) 
    { 
        $units = array('B', 'KB', 'MB', 'GB', 'TB'); 
        $bytes = max($bytes, 0); 
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
        $pow = min($pow, count($units) - 1);     
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow]; 
    } 

    public function canCreate($member = null, $context = [])
    {
        return false;
    }

    public function canEdit($member = null)
    {
        return false;
    }

}