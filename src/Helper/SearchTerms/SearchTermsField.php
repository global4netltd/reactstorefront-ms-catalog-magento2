<?php

namespace G4NReact\MsCatalogMagento2\Helper\SearchTerms;

use G4NReact\MsCatalogMagento2\Helper\AbstractFieldHelper;
use Magento\Search\Model\ResourceModel\Query;

/**
 * Class SearchTermsField
 * @package G4NReact\MsCatalogMagento2\Helper\SearchTerms
 */
class SearchTermsField extends AbstractFieldHelper
{
    /** @var columns types */
    public static $columnsTypes;
    
    /** @var string set object type */
    const OBJECT_TYPE = 'search_term';
    
    /** @var string table name */
    const TABLE_NAME_SEARCH_QUERY = 'search_query';

    /**
     * @var Query
     */
    protected $searchQueryResourceModel;

    /**
     * SearchTermsField constructor.
     *
     * @param Query $searchQueryResourceModel
     */
    public function __construct(
        Query $searchQueryResourceModel
    )
    {
        $this->searchQueryResourceModel = $searchQueryResourceModel;
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
            $fields = $this->searchQueryResourceModel->getConnection()->describeTable(self::TABLE_NAME_SEARCH_QUERY);
            self::$columnsTypes = $this->prepareColumnTypes($fields);
        }

        if (isset(self::$columnsTypes[$columnName])) {
            return self::$columnsTypes[$columnName];
        }

        return 'string';
    }

    /**
     * @param string $fieldName
     *
     * @return bool
     */
    public static function getIsIndexable(string $fieldName): bool
    {
        return true;
    }
}
