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
    
    /** @var string if synonym this is original search term value */
    const ORIGINAL_SEARCH_TERM_VALUE = 'original_search_term_value';
    
    /** @var bool is synonym  */
    const IS_SYNONYM = 'is_synonym';
    
    /** @var string react store front id => describe type of search term document */
    const REACT_STORE_FRONT_ID = 'react_store_front_id';
    
    /** @var int react store front id if search term */
    const REACT_STORE_FRONT_ID_SEARCH_TERM = 1;
    
    /** @var int react store front id if synonym */
    const REACT_STORE_FRONT_ID_SYNONYM = 2;

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
    public function getFieldTypeByColumnName(string $columnName): string
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
