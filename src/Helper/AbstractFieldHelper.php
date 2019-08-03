<?php

namespace G4NReact\MsCatalogMagento2\Helper;

/**
 * Class AbstractFieldHelper
 * @package G4NReact\MsCatalog\Helper
 */
abstract class AbstractFieldHelper implements FieldHelperInterface
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

    /**
     * @param string $fieldName
     * @param $value
     *
     * @return bool
     */
    public static function getIsMultiValued(string $fieldName, $value): bool
    {
        if (is_array($value)) {
            return true;
        }

        return false;
    }
}
