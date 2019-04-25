<?php

namespace G4NReact\MsCatalogMagento2\Model\Puller;

use G4NReact\MsCatalogIndexer\Config;
use G4NReact\MsCatalog\Document;
use G4NReact\MsCatalogMagento2\Model\AbstractPuller;

/**
 * Class CategoryPuller
 * @package G4NReact\MsCatalogMagento2\Model\Puller
 */
class CategoryPuller extends AbstractPuller
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $categoryCollectionFactory;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute
     */
    protected $eavAttribute;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Config
     */
    protected $eavConfig;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * ProductPuller constructor
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\ResourceConnection $resource
    ) {
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->eavConfig = $eavConfig;
        $this->eavAttribute = $eavAttribute;
        $this->resource = $resource;

        parent::__construct($scopeConfig);
    }

    /**
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getCollection()
    {
        $collection = $this->categoryCollectionFactory->create();

        if ($this->ids !== null) {
            $collection->addAttributeToFilter('entity_id', array('in' => $this->ids));
        }

        $collection->addAttributeToSelect('*')
            ->setPageSize($this->pageSize)
            ->setCurPage($this->curPage);

        return $collection;
    }

    /**
     * @return Document|mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function current(): Document
    {
        $category = $this->pageArray[$this->position];

        $document = new Document();

        $document->setUniqueId($category->getId() . '_' . 'category' . '_' . $category->getStoreId());
        $document->setObjectId($category->getId());
        $document->setObjectType('category'); // @ToDo: move it to const

        $filterableAttributesCodes = $this->getFilterableAttributesCodes($category->getId());
        $filterableAttributesCodesList = '';
        $glue = '';
        if (is_array($filterableAttributesCodes)) {
            foreach ($filterableAttributesCodes as $attributeCode) {
                $filterableAttributesCodesList .= ($glue . $attributeCode . '_facet');
                $glue = ', ';
            }
        }
        $document->setField(
            'category_facets',
            $filterableAttributesCodesList,
            'string',
            false
        );

        foreach ($category->getData() as $field => $value) {
            $attribute = $this->eavConfig->getAttribute('catalog_category', $field);
            $document->setField(
                $field,
                $category->getData($field),
                $attribute->getBackendType(),
                $attribute->getIsFilterable() ? true : false
            );
        }

        return $document;
    }

    /**
     * @param int $categoryId
     * @return array
     */
    public function getFilterableAttributesCodes($categoryId)
    {
        $connection = $this->resource->getConnection();

        $select = $connection->select()
            ->from(['ea' => $connection->getTableName('eav_attribute')], 'ea.attribute_code')
            ->join(['eea' => $connection->getTableName('eav_entity_attribute')], 'ea.attribute_id = eea.attribute_id')
            ->join(['cea' => $connection->getTableName('catalog_eav_attribute')], 'ea.attribute_id = cea.attribute_id')
            ->join(['cpe' => $connection->getTableName('catalog_product_entity')], 'eea.attribute_set_id = cpe.attribute_set_id')
            ->join(['ccp' => $connection->getTableName('catalog_category_product')], 'cpe.entity_id = ccp.product_id')
            ->where('cea.is_filterable = ?', 1)
            ->where('ccp.category_id = ?', $categoryId)
            ->group('ea.attribute_id');

        $attributeCodes = $connection->fetchCol($select);

        return $attributeCodes;
    }
}
