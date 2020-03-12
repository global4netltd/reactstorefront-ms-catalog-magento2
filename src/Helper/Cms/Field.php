<?php

namespace G4NReact\MsCatalogMagento2\Helper\Cms;

use G4NReact\MsCatalogMagento2\Helper\AbstractFieldHelper;
use Magento\Cms\Model\ResourceModel\Page;

/**
 * Class Field
 * @package G4NReact\MsCatalogMagento2\Helper\Cms
 */
class Field extends AbstractFieldHelper
{
    /** @var string object type cms */
    const OBJECT_TYPE = 'cms_page';

    /** @var string table name cms page */
    const TABLE_NAME_CMS_PAGE = 'cms_page';

    /** @var array $columnsTypes */
    public static $columnsTypes = [];

    /**
     * @deprecated
     *
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

    /**
     * @var array
     */
    protected static $fieldMultivaluedMap = [

    ];

    /**
     * @var Page
     */
    protected $resourceModelPage;

    /**
     * Field constructor.
     *
     * @param Page $resourceModelPage
     */
    public function __construct(
        Page $resourceModelPage
    )
    {
        $this->resourceModelPage = $resourceModelPage;
    }

    /**
     * @param string $columnName
     *
     * @return string
     */
    public function getFieldTypeByColumnName(string $columnName): string
    {
        if (isset(self::$overrideFieldTypeMap[$columnName])) {
            return self::$overrideFieldTypeMap[$columnName];
        }
        if (!self::$columnsTypes) {
            $fields = $this->resourceModelPage->getConnection()->describeTable(self::TABLE_NAME_CMS_PAGE);
            self::$columnsTypes = $this->prepareColumnTypes($fields);
        }

        if (isset(self::$columnsTypes[$columnName])) {
            return self::$columnsTypes[$columnName];
        }

        return 'string';
    }

    /**
     * @param string $fieldName
     * @param $value
     *
     * @return bool
     */
    public static function getIsMultiValued(string $fieldName, $value): bool
    {
        if (in_array($fieldName, self::$fieldMultivaluedMap) || is_array($value)) {
            return true;
        }

        return false;
    }

    /**
     * @param string $fieldName
     *
     * @return bool
     */
    public static function getIsIndexable(string $fieldName) : bool 
    {
        return true;
    }
}
