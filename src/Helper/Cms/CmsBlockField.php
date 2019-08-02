<?php

namespace G4NReact\MsCatalogMagento2\Helper\Cms;


use G4NReact\MsCatalogMagento2\Helper\AbstractFieldHelper;
use Magento\Cms\Model\ResourceModel\Block as ResourceCmsBlock;

/**
 * Class CmsBlockField
 * @package G4NReact\MsCatalogMagento2\Helper\Cms
 */
class CmsBlockField extends AbstractFieldHelper
{
    /** @var string object type cms block */
    const OBJECT_TYPE = 'cms_block';
    
    /** @var string cms block table name */
    const TABLE_NAME_CMS_BLOCK = 'cms_block';

    /**
     * @var array
     */
    public static $columnsTypes = [];
    
    /**
     * @var ResourceCmsBlock
     */
    protected $resourceCmsBlock;

    /**
     * CmsBlockField constructor.
     *
     * @param ResourceCmsBlock $resourceCmsBlock
     */
    public function __construct(
        ResourceCmsBlock $resourceCmsBlock
    )
    {
        $this->resourceCmsBlock = $resourceCmsBlock;
    }

    /**
     * @param string $columnName
     *
     * @return string
     */
    public function getFieldTypeByCmsColumnName(string $columnName): string
    {
        if (!self::$columnsTypes) {
            $fields = $this->resourceCmsBlock->getConnection()->describeTable(self::TABLE_NAME_CMS_BLOCK);
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

    /**
     * @param string $fieldName
     * @param $value
     *
     * @return bool
     */
    public static function getIsMultiValued(string $fieldName, $value): bool
    {
        if(is_array($value)){
            return true;
        }
        
        return false;
    }
}
