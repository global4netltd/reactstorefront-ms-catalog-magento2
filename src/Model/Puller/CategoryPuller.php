<?php

namespace G4NReact\MsCatalogMagento2\Model\Puller;

use G4NReact\MsCatalog\Document;
use G4NReact\MsCatalog\QueryInterface;
use G4NReact\MsCatalog\ResponseInterface;
use G4NReact\MsCatalogMagento2\Model\AbstractPuller;
use G4NReact\MsCatalogMagento2\Helper\MsCatalog as MsCatalogHelper;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Category\Collection as CategoryCollection;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Eav\Model\ResourceModel\Entity\Attribute;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class CategoryPuller
 * @package G4NReact\MsCatalogMagento2\Model\Puller
 */
class CategoryPuller extends AbstractPuller
{
    /**
     * @var CategoryCollectionFactory
     */
    protected $categoryCollectionFactory;

    /**
     * @var Attribute
     */
    protected $eavAttribute;

    /**
     * @var EavConfig
     */
    protected $eavConfig;

    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * CategoryPuller constructor
     *
     * @param CategoryCollectionFactory $categoryCollectionFactory
     * @param EavConfig $eavConfig
     * @param Attribute $eavAttribute
     * @param MsCatalogHelper $msCatalogHelper
     * @param ResourceConnection $resource
     */
    public function __construct(
        CategoryCollectionFactory $categoryCollectionFactory,
        EavConfig $eavConfig,
        Attribute $eavAttribute,
        MsCatalogHelper $msCatalogHelper,
        ResourceConnection $resource
    ) {
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->eavConfig = $eavConfig;
        $this->eavAttribute = $eavAttribute;
        $this->resource = $resource;

        parent::__construct($msCatalogHelper);
    }

    /**
     * @return CategoryCollection
     */
    public function getCollection(): CategoryCollection
    {
        $categoryCollection = $this->categoryCollectionFactory->create();

        if ($this->ids !== null) {
            $categoryCollection->addAttributeToFilter('entity_id', array('in' => $this->ids));
        }

        $categoryCollection->addAttributeToSelect('*')
            ->setPageSize($this->pageSize)
            ->setCurPage($this->curPage);

        return $categoryCollection;
    }

    /**
     * @return Document
     * @throws LocalizedException
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
        
        if(!$document->getData('store_id')){
            $document->setField(
                'store_id',
                $category->getStoreId(),
                '',
                true
            );
        }

        foreach ($category->getData() as $field => $value) {
            $attribute = $this->eavConfig->getAttribute('catalog_category', $field);
            $document->setField(
                $field,
                $category->getData($field),
                $attribute->getBackendType(),
                $attribute->getIsFilterable() ? true : false,
                in_array($attribute->getFrontendInput(), MsCatalogHelper::$multiValuedAttributeFrontendInput)
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

    /**
     * @param QueryInterface|null $query
     * @return ResponseInterface
     */
    public function pull(QueryInterface $query = null): ResponseInterface
    {
        // TODO: Implement pull() method.
    }
}
