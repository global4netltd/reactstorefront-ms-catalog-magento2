<?php

namespace G4NReact\MsCatalogMagento2\Helper\Cms;

use Magento\Cms\Model\ResourceModel\Page;

/**
 * Class Field
 * @package G4NReact\MsCatalogMagento2\Helper\Cms
 */
class Field
{
    /** @var string object type cms */
    const OBJECT_TYPE = 'cms';

    /** @var string column name */
    const COLUMN_NAME = 'COLUMN_NAME';

    /** @var string data type */
    const DATA_TYPE = 'DATA_TYPE';

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
    protected static $overrideFieldTypeMap = [
        'store_id' => 'int',
        'is_active' => 'bool',
    ];

    /**
     * @var array
     */
    protected static $fieldMultivaluedMap = [
        'store_id',
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
    public function getFieldTypeByCmsColumnName(string $columnName): string
    {
        if (isset(self::$overrideFieldTypeMap[$columnName])) {
            return self::$overrideFieldTypeMap[$columnName];
        }
        if (!self::$columnsTypes) {
            $fields = $this->resourceModelPage->getConnection()->describeTable(self::TABLE_NAME_CMS_PAGE);
            $this->prepareColumnTypes($fields);
        }

        if (isset(self::$columnsTypes[$columnName])) {
            return self::$columnsTypes[$columnName];
        }

        return 'string';
    }

    /**
     * @param array $fields
     */
    protected function prepareColumnTypes(array $fields)
    {
        $res = [];
        foreach ($fields as $field) {
            if (isset($field[self::COLUMN_NAME]) && isset($field[self::DATA_TYPE]))
                $res[$field[self::COLUMN_NAME]] = $field[self::DATA_TYPE];
        }

        self::$columnsTypes = $res;
    }

    /**
     * @param string $fieldName
     * @param $value
     *
     * @return bool
     */
    public static function getIsCmsMultivalued(string $fieldName, $value): bool
    {
        if (in_array($fieldName, self::$fieldMultivaluedMap) || is_array($value)) {
            return true;
        }

        return false;
    }
}
