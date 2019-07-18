<?php

namespace G4NReact\MsCatalogMagento2\Helper\Cms;

/**
 * Class Field
 * @package G4NReact\MsCatalogMagento2\Helper\Cms
 */
class Field
{
    /** @var string object type cms */
    const OBJECT_TYPE = 'cms';

    /**
     * @var array
     */
    public static $fieldTypeMap = [
        'page_id' => 'int',
        'title' => 'string',
        'page_layout' => 'string',
        'meta_keywords' => 'string',
        'meta_description' => 'string',
        'identifier' => 'string',
        'content_heading' => 'string',
        'content' => 'text',
        'creation_time' => 'datetime',
        'update_time' => 'datetime',
        'is_active' => 'bool',
        'sort_order' => 'int',
        'store_id' => 'int',
    ];
}
