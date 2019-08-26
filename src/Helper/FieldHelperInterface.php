<?php

namespace G4NReact\MsCatalogMagento2\Helper;

/**
 * Interface FieldHelperInterface
 * @package G4NReact\MsCatalogMagento2\Helper
 */
interface FieldHelperInterface
{
    /**
     * @param string $columnName
     *
     * @return string
     */
    public function getFieldTypeByColumnName(string $columnName) : string;

    /**
     * @param string $fieldName
     *
     * @return bool
     */
    public static function getIsIndexable(string $fieldName) : bool;

    /**
     * @param string $fieldName
     * @param $value
     *
     * @return bool
     */
    public static function getIsMultiValued(string $fieldName, $value): bool;
}
