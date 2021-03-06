<?php

namespace G4NReact\MsCatalogMagento2\Model\Puller;

use G4NReact\MsCatalog\Document;
use G4NReact\MsCatalog\QueryInterface;
use G4NReact\MsCatalog\ResponseInterface;
use G4NReact\MsCatalogMagento2\Helper\Config as ConfigHelper;
use G4NReact\MsCatalogMagento2\Helper\Query as QueryHelper;
use G4NReact\MsCatalogMagento2\Model\AbstractPuller;
use G4NReact\MsCatalogMagento2\Model\Attribute\SearchTerms;
use G4NReact\MsCatalogMagento2\Model\ResourceModel\ProductExtended;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Eav\Model\ResourceModel\Entity\Attribute;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Data\Collection as DataCollection;
use Magento\Framework\Event\Manager as EventManager;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class ProductPuller
 * @package G4NReact\MsCatalogMagento2\Model\Puller
 */
class ProductPuller extends AbstractPuller
{
    /**
     * @var string Type of object
     */
    const OBJECT_TYPE = 'product';

    /**
     * @var int
     */
    const MAX_CATEGORY_PRODUCT_POSITION = 10000;

    /**
     * @var ProductCollectionFactory
     */
    protected $productCollectionFactory;
    /**
     * @var Attribute
     */
    protected $eavAttribute;
    /**
     * @var EavConfig
     */
    protected $eavConfig;
    /**
     * @var JsonSerializer
     */
    protected $jsonSerializer;
    /**
     * @var SearchTerms
     */
    protected $searchTerms;
    /**
     * @var QueryHelper
     */
    protected $queryHelper;
    /**
     * @var EventManager
     */
    protected $eventManager;
    /**
     * @var ProductExtended
     */
    protected $productExtended;
    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * ProductPuller constructor.
     *
     * @param ProductCollectionFactory $productCollectionFactory
     * @param EavConfig $eavConfig
     * @param Attribute $eavAttribute
     * @param JsonSerializer $jsonSerializer
     * @param ConfigHelper $magento2ConfigHelper
     * @param SearchTerms $searchTerms
     * @param QueryHelper $queryHelper
     * @param EventManager $eventManager
     * @param ProductExtended $productExtended
     * @param ResourceConnection $resource
     * @param StoreManagerInterface $storeManager
     *
     * @throws NoSuchEntityException
     */
    public function __construct(
        ProductCollectionFactory $productCollectionFactory,
        EavConfig $eavConfig,
        Attribute $eavAttribute,
        JsonSerializer $jsonSerializer,
        ConfigHelper $magento2ConfigHelper,
        SearchTerms $searchTerms,
        QueryHelper $queryHelper,
        EventManager $eventManager,
        ProductExtended $productExtended,
        ResourceConnection $resource,
        StoreManagerInterface $storeManager
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->eavConfig = $eavConfig;
        $this->eavAttribute = $eavAttribute;
        $this->jsonSerializer = $jsonSerializer;
        $this->searchTerms = $searchTerms;
        $this->queryHelper = $queryHelper;
        $this->eventManager = $eventManager;
        $this->productExtended = $productExtended;
        $this->resource = $resource;
        $this->storeManager = $storeManager;
        $this->setType(self::OBJECT_TYPE);

        parent::__construct($magento2ConfigHelper);
    }

    /**
     * @return ProductCollection
     * @throws LocalizedException
     */
    public function getCollection(): ProductCollection
    {
        /** @var ProductCollection $productCollection */
        $productCollection = $this->productCollectionFactory->create();

        if ($this->getIds()) {
            $productCollection->addAttributeToFilter('entity_id', ['in' => $this->getIds()]);
        }

        $productCollection->addAttributeToSelect('*')
            ->addUrlRewrite()
            ->addStoreFilter()
            ->setPageSize($this->pageSize)
            ->setCurPage($this->curPage)
            ->addFinalPrice()
            ->addCategoryIds()
            ->addMediaGalleryData();

        $this->eventManager->dispatch('ms_catalog_get_product_collection', ['collection' => $productCollection]);

        $this->loadCategoryIds($productCollection);

        return $productCollection;
    }

    /**
     * @param ProductCollection $productCollection
     * @throws NoSuchEntityException
     */
    public function loadCategoryIds($productCollection)
    {
        $ids = $productCollection->getLoadedIds();

        $this->productExtended->eagerLoadCategoriesWithParents($ids, $productCollection);
    }

    /**
     * @return Document
     * @throws LocalizedException
     */
    public function current(): Document
    {
        /** @var Product $product */
        $product = $this->pageArray[$this->position];
        /** @var Document $document */
        $document = new Document();

        $eventData = new \stdClass();
        $eventData->product = $product;
        $eventData->document = $document;
        $eventData->disable = false;

        $this->eventManager->dispatch('prepare_document_from_product_before', ['eventData' => $eventData]);

        if ($eventData->disable === true) {
            return $document;
        }

        $document->setUniqueId($product->getId() . '_' . self::OBJECT_TYPE . '_' . $product->getStoreId());
        $document->setObjectId($product->getId());
        $document->setObjectType(self::OBJECT_TYPE);

        $this->handleCategoryId($product, $document);

        $this->addAttributes($product, $document);

        $this->addMediaGallery($product, $document);

        $this->handleRequestPath($document);

        $this->addUrl($document);

        $this->addCategoryPosition($product, $document);

        $eventData = [
            'product'  => $product,
            'document' => $document,
        ];

        $this->eventManager->dispatch('prepare_document_from_product_after', $eventData);

        return $document;
    }

    /**
     * @param Product $product
     * @param Document $document
     * @throws LocalizedException
     */
    protected function handleCategoryId(Product $product, Document $document): void
    {
        $document->setField(
            $this->queryHelper
                ->getFieldByAttributeCode('category_id', $product->getCategoryIds())
        );
        $product->unsetData('category_ids');
    }

    /**
     * @param Product $product
     * @param Document $document
     * @throws LocalizedException
     * @throws InputException
     */
    protected function addAttributes(Product $product, Document $document): void
    {
        foreach ($product->getData() as $field => $value) {
            $attribute = $this->eavConfig->getAttribute('catalog_product', $field);

            $searchTermField = $this->searchTerms->prepareSearchTermField($attribute->getAttributeCode());
            if ($searchTermField) {
                if ($field = $document->getField($searchTermField)) {
                    $field->setValue($field->getValue() . $value);
                } else {
                    $document->createField(
                        $searchTermField,
                        $value,
                        Document\Field::FIELD_TYPE_TEXT_SEARCH,
                        true,
                        false
                    );
                }
            }

            $document->setField(
                $this->queryHelper->getFieldByAttribute($attribute, $product->getData($attribute->getAttributeCode()))
            );

            // force creating Fields that should be indexed but have not any value @ToDo: temprarily - and are not multivalued
            foreach ($this->searchTerms->getForceIndexingAttributes() as $attributeCode) {
                if (!$document->getField($attributeCode)) {
                    $field = $this->queryHelper->getFieldByAttributeCode($attributeCode, null);
                    if (!$field->getMultiValued()) {
                        $document->setField($field);
                    }
                }
            }
        }
    }

    /**
     * @param Product $product
     * @param Document $document
     * @throws LocalizedException
     */
    protected function addMediaGallery(Product $product, Document $document): void
    {
        $mediaGalleryJson = $this->getMediaGalleryJson($product->getMediaGalleryImages());
        $document->setField(
            $this->queryHelper
                ->getFieldByAttributeCode('media_gallery', $mediaGalleryJson)
        );
    }

    /**
     * @param DataCollection $mediaGalleryImages
     *
     * @return bool|false|string
     */
    protected function getMediaGalleryJson(DataCollection $mediaGalleryImages)
    {
        $gallery = [];

        foreach ($mediaGalleryImages as $image) {
            $gallery[] = ['full' => $image->getUrl()];
        }

        return $this->jsonSerializer->serialize($gallery);
    }

    /**
     * @param Document $document
     */
    protected function handleRequestPath(Document $document): void
    {
        if ($requestPathField = $document->getField('request_path')) {
            $requestPath = (string)$requestPathField->getValue();
            $requestPath = '/' . ltrim($requestPath, '/');
            $requestPathField->setValue($requestPath);
        }
    }

    /**
     * @param Document $document
     *
     * @throws NoSuchEntityException
     */
    protected function addUrl(Document $document): void
    {
        if ($requestPathField = $document->getField('request_path')) {
            $document->setField(
                new Document\Field(
                    'url',
                    rtrim($this->storeManager->getStore()->getBaseUrl(), '/') . $requestPathField->getValue(),
                    Document\Field::FIELD_TYPE_STRING,
                    true,
                    false
                )
            );
        }
    }

    /**
     * @param Product $product
     * @param Document $document
     */
    protected function addCategoryPosition(Product $product, Document $document): void
    {
        if ($categoryPositions = $document->getFieldValue('category_positions')) {
            foreach ($categoryPositions as $categoryId => $position) {
                $finalPosition = self::MAX_CATEGORY_PRODUCT_POSITION - $position;
                $finalPosition = (($finalPosition === self::MAX_CATEGORY_PRODUCT_POSITION) || ($finalPosition < 0))
                    ? 0
                    : $finalPosition;
                if ($finalPosition) {
                    $document->createField(
                        "category_{$categoryId}_position",
                        $finalPosition,
                        Document\Field::FIELD_TYPE_INT,
                        true,
                        false
                    );
                }
            }
            $document->unsetField('category_positions');
        }
    }

    /**
     * @param QueryInterface|null $query
     *
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
