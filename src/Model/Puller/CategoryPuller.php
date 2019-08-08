<?php

namespace G4NReact\MsCatalogMagento2\Model\Puller;

use G4NReact\MsCatalog\Document;
use G4NReact\MsCatalog\QueryInterface;
use G4NReact\MsCatalog\ResponseInterface;
use G4NReact\MsCatalogMagento2\Helper\Config as ConfigHelper;
use G4NReact\MsCatalogMagento2\Helper\Query as QueryHelper;
use G4NReact\MsCatalogMagento2\Model\AbstractPuller;
use Magento\Catalog\Api\Data\CategoryAttributeInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\ResourceModel\Category\Collection as CategoryCollection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Eav\Model\ResourceModel\Entity\Attribute;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Event\Manager as EventManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class CategoryPuller
 * @package G4NReact\MsCatalogMagento2\Model\Puller
 */
class CategoryPuller extends AbstractPuller
{
    /**
     * @var string Type of object
     */
    const OBJECT_TYPE = 'category';

    /**
     * @var array fully loaded count of products from facets
     */
    public $productsCount;
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
     * @var Magento2HelperQuery
     */
    protected $helperQuery;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var EventManager
     */
    protected $eventManager;

    /**
     * CategoryPuller constructor
     *
     * @param CategoryCollectionFactory $categoryCollectionFactory
     * @param EavConfig $eavConfig
     * @param Attribute $eavAttribute
     * @param ConfigHelper $magento2ConfigHelper
     * @param ResourceConnection $resource
     * @param QueryHelper $helperQuery
     * @param StoreManagerInterface $storeManager
     * @param EventManager $eventManager
     * @throws NoSuchEntityException
     */
    public function __construct(
        CategoryCollectionFactory $categoryCollectionFactory,
        EavConfig $eavConfig,
        Attribute $eavAttribute,
        ConfigHelper $magento2ConfigHelper,
        ResourceConnection $resource,
        QueryHelper $helperQuery,
        StoreManagerInterface $storeManager,
        EventManager $eventManager
    ) {
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->eavConfig = $eavConfig;
        $this->eavAttribute = $eavAttribute;
        $this->resource = $resource;
        $this->helperQuery = $helperQuery;
        $this->storeManager = $storeManager;
        $this->eventManager = $eventManager;
        $this->setType(self::OBJECT_TYPE);

        parent::__construct($magento2ConfigHelper);
    }

    /**
     * @return CategoryCollection
     * @throws LocalizedException
     */
    public function getCollection(): CategoryCollection
    {
        /** @var CategoryCollection $categoryCollection */
        $categoryCollection = $this->categoryCollectionFactory->create();

        if ($this->ids !== null) {
            $categoryCollection->addAttributeToFilter('entity_id', ['in' => $this->ids]);
        }

        $categoryCollection->addAttributeToSelect('*')
            ->addAttributeToFilter('entity_id', ['gt' => 2])
            ->setStoreId($this->storeManager->getStore()->getId())
            ->addIsActiveFilter()
            ->addUrlRewriteToResult()
            ->setPageSize($this->pageSize)
            ->setCurPage($this->curPage);

        $this->eventManager->dispatch('ms_catalog_m2_category_puller_before_load', ['collection' => $categoryCollection]);
        $categoryCollection->load();
        $this->eventManager->dispatch('ms_catalog_m2_category_puller_after_load', ['collection' => $categoryCollection]);


        return $categoryCollection;
    }

    /**
     * @return Document
     * @throws LocalizedException
     */
    public function current(): Document
    {
        /** @var Category $category */
        $category = $this->pageArray[$this->position];

        $document = new Document();

        $eventData = [
            'category' => $category,
            'document' => $document,
        ];
        $this->eventManager->dispatch('prepare_document_from_category_before', ['eventData' => $eventData]);

        $document->setUniqueId($category->getId() . '_' . self::OBJECT_TYPE . '_' . $category->getStoreId());
        $document->setObjectId($category->getId());
        $document->setObjectType(self::OBJECT_TYPE);

        $filterableAttributesCodes = $this->getFilterableAttributesCodes($category->getId());
        $filterableAttributesCodesList = '';
        $glue = '';
        if (is_array($filterableAttributesCodes)) {
            foreach ($filterableAttributesCodes as $attributeCode) {
                $filterableAttributesCodesList .= ($glue . $attributeCode . '_facet');
                $glue = ', ';
            }
        }
        $document->createField(
            'category_facets',
            $filterableAttributesCodesList,
            Document\Field::FIELD_TYPE_STRING,
            false
        );

        if (!$document->getData('store_id')) {
            $document->createField(
                'store_id',
                $category->getStoreId(),
                $this->helperQuery
                    ->getAttributeFieldType($this->eavConfig->getAttribute('catalog_category', 'store_id')),
                true
            );
        }

        foreach ($category->getData() as $field => $value) {
            $attribute = $this->eavConfig->getAttribute('catalog_category', $field);

            $document->setField(
                $this->helperQuery->getFieldByAttribute($attribute, $value)
            );
        }

        $document->setField(
            $this->helperQuery->getFieldByAttributeCode(
                'product_count',
                $this->getProductCount($category->getId()),
                CategoryAttributeInterface::ENTITY_TYPE_CODE
            )
        );

        if ($requestPathField = $document->getField('request_path')) {
            $requestPath = (string)$requestPathField->getValue();
            $requestPath = '/' . ltrim($requestPath, '/');
            $requestPathField->setValue($requestPath);
            $requestPathField->setIndexable(true);

            if ($urlPathField = $document->getField('url_path')) { // @ToDo: Temporarily. I hope so...
                $urlPathField->setValue($requestPath);
            } else {
                $document->createField(
                    'url_path',
                    $requestPath,
                    $requestPathField->getType(),
                    false,
                    $requestPathField->getMultiValued()
                );
            }
        }

        $this->addUrl($document);

        $eventData = [
            'category' => $category,
            'document' => $document,
        ];
        $this->eventManager->dispatch('prepare_document_from_category_after', $eventData);

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
     * @param Document $document
     *
     * @throws NoSuchEntityException
     */
    protected function addUrl(Document $document) : void
    {
        if ($requestPathField = $document->getField('request_path')) {
            $document->setField(
                new Document\Field(
                    'url',
                    rtrim($this->storeManager->getStore()->getBaseUrl(), '/') . $requestPathField->getValue(),
                    'string',
                    true,
                    false
                )
            );
        }
    }

    /**
     * @param $categoryId
     * @return int|mixed
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function getProductCount($categoryId)
    {
        if ($this->productsCount === null) {
            $this->productsCount = $this->helperQuery->getCategoriesProductsCount($this->storeManager->getStore()->getId());
        }

        return $this->productsCount[$categoryId] ?? 0;
    }

    /**
     * @param QueryInterface|null $query
     * @return ResponseInterface
     */
    public function pull(QueryInterface $query = null): ResponseInterface
    {
        // TODO: Implement pull() method.
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return self::OBJECT_TYPE;
    }
}
