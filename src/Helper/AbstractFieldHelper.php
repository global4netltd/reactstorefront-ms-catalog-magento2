<?php

namespace G4NReact\MsCatalogMagento2\Helper;

/**
 * Class AbstractFieldHelper
 * @package G4NReact\MsCatalog\Helper
 */
abstract class AbstractFieldHelper
{
    /** @var string column name */
    const COLUMN_NAME = 'COLUMN_NAME';

    /** @var string data type */
    const DATA_TYPE = 'DATA_TYPE';

    /**
     * @var array
     */
    protected static $overrideFieldTypeMap = [
        'store_id' => 'int',
        'is_active' => 'bool',
        '_first_store_id' => 'int'
    ];
    
    /**
     * @param string $columnName
     *
     * @return string
     */
    abstract public function getFieldTypeByCmsColumnName(string $columnName) : string;

    /**
     * @param string $fieldName
     *
     * @return bool
     */
    abstract public static function getIsIndexable(string $fieldName) : bool;

    /**
     * @param string $fieldName
     * @param $value
     *
     * @return bool
     */
    abstract public static function getIsMultiValued(string $fieldName, $value): bool;

    /**
     * @param array $fields
     *
     * @return array
     */
    protected function prepareColumnTypes(array $fields)
    {
        $res = [];
        foreach ($fields as $field) {
            if (isset($field[self::COLUMN_NAME]) && isset($field[self::DATA_TYPE]))
                $res[$field[self::COLUMN_NAME]] = $field[self::DATA_TYPE];
        }

        return $res;
    }
}
