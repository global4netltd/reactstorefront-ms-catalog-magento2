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
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Eav\Model\ResourceModel\Entity\Attribute;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Event\Manager as EventManager;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Magento\Inventory\Model\SourceItemRepository;
use Magento\Inventory\Model\ResourceModel\SourceItem\CollectionFactory as SourceItemCollectionFactory;
use Magento\Inventory\Model\ResourceModel\SourceItem\Collection as SourceItemCollection;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\Store\Model\StoreManagerInterface;
use G4NReact\MsCatalogMagento2\Helper\ProductPuller as HelperProductPuller;
use Magento\Catalog\Model\Product\Image\UrlBuilder as ImageUrlBuilder;

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
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var SourceItemCollectionFactory
     */
    protected $sourceItemCollectionFactory;

    /**
     * @var SourceItemRepository
     */
    protected $sourceItemRepository;

    /**
     * @var ImageUrlBuilder
     */
    protected $imageUrlBuilder;

    /**
     * ProductPuller constructor
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
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SourceItemRepository $sourceItemRepository
     * @param ImageUrlBuilder $imageUrlBuilder
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
        StoreManagerInterface $storeManager,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SourceItemRepository $sourceItemRepository,
        SourceItemCollectionFactory $sourceItemCollectionFactory,
        ImageUrlBuilder $imageUrlBuilder
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
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sourceItemRepository = $sourceItemRepository;
        $this->sourceItemCollectionFactory = $sourceItemCollectionFactory;
        $this->imageUrlBuilder = $imageUrlBuilder;
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

        if ($this->magento2ConfigHelper->getShouldSkipDisabledProducts()) {
            $productCollection->addAttributeToFilter(
                'status',
                \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED
            );
        }

        $productCollection->addAttributeToSelect('*')
            ->addUrlRewrite()
            ->addStoreFilter()
            ->setPageSize($this->pageSize)
            ->setCurPage($this->curPage)
            ->addFinalPrice()
            ->addCategoryIds()
            ->addMediaGalleryData();

        $start = microtime(true);
        $productCollection = $this->prepareReviewsOnProduct($productCollection, $this->prepareReviewsData($productCollection));
        \G4NReact\MsCatalog\Profiler::increaseTimer('prepareReviewsOnProduct', (microtime(true) - $start));

        $start = microtime(true);
        $productCollection = $this->assingStockToProductsCollection($productCollection);
        \G4NReact\MsCatalog\Profiler::increaseTimer('assingStockToProductsCollection', (microtime(true) - $start));

        $start = microtime(true);
        $productCollection = $this->assingStockToProductsCollectionLegacy($productCollection);
        \G4NReact\MsCatalog\Profiler::increaseTimer('assingStockToProductsCollectionLegacy', (microtime(true) - $start));

        $start = microtime(true);
        $this->eventManager->dispatch('ms_catalog_get_product_collection', ['collection' => $productCollection]);
        \G4NReact\MsCatalog\Profiler::increaseTimer(
            'observer => ms_catalog_get_product_collection',
            (microtime(true) - $start)
        );

        $start = microtime(true);
        $this->loadCategoryIds($productCollection);
        \G4NReact\MsCatalog\Profiler::increaseTimer(
            'getCollection > loadCategoryIds',
            (microtime(true) - $start)
        );

        $start = microtime(true);
        $this->eventManager->dispatch('ms_catalog_m2_product_puller_after_load', ['collection' => $productCollection]);
        \G4NReact\MsCatalog\Profiler::increaseTimer('observer => ms_catalog_m2_product_puller_after_load', (microtime(true) - $start));

        return $productCollection;
    }

    /**
     * @param ProductCollection $productCollection
     * @param array $reviews
     *
     * @return ProductCollection
     */
    protected function prepareReviewsOnProduct(ProductCollection $productCollection, array $reviews): ProductCollection
    {
        foreach ($productCollection as $product) {
            if (
                isset($reviews[$product->getId()])
                && isset($reviews[$product->getId()]['reviews_count'])
                && isset($reviews[$product->getId()]['rating_summary'])
            ) {
                $review = $reviews[$product->getId()];
                $product
                    ->setReviewsCount((int)$review['reviews_count'])
                    ->setReviewsAverageRating($this->prepareAverageRating($review['rating_summary']));
            } else {
                $product
                    ->setReviewsCount(0)
                    ->setReviewsAverageRating(0);
            }
        }

        return $productCollection;
    }


    /**
     * @param ProductCollection $productCollection
     *
     * @return array
     * @throws NoSuchEntityException
     */
    protected function prepareReviewsData(ProductCollection $productCollection): array
    {
        $select = $productCollection->getSelect()->join(
            ['rating' => $productCollection->getTable('review_entity_summary')],
            'rating.entity_pk_value = e.entity_id AND rating.store_id = ' . (int)$this->storeManager->getStore()->getId(),
            ['reviews_count', 'rating_summary']
        );

        $reviews = $productCollection->getConnection()->fetchAll($select);

        $preparedReviews = [];
        foreach ($reviews as $review) {
            if (isset($review['entity_id'])) {
                $preparedReviews[$review['entity_id']] = $review;
            }
        }

        return $preparedReviews;
    }

    /**
     * @param int $ratingSummary
     *
     * @return float
     */
    protected function prepareAverageRating(int $ratingSummary): float
    {
        return $ratingSummary / 20;
    }

    /**
     * @param ProductCollection $productCollection
     *
     * @return ProductCollection
     */
    protected function assingStockToProductsCollection(ProductCollection $productCollection)
    {
        $skus = $productCollection->getColumnValues('sku');

        if (!$skus) {
            return $productCollection;
        }

        /** @var SourceItemCollection $sourceItemCollection */
        $sourceItemCollection = $this->sourceItemCollectionFactory->create();
        $sourceItemCollection
            ->addFieldToSelect('sku')
            ->addFieldToSelect('source_code')
            ->addFieldToSelect('quantity')
            ->addFieldToSelect('status')
            ->addFieldToFilter('sku', ['in' => $skus]);

        $sources = [];
        /** @var SourceItemInterface $sourceItem */
        foreach ($sourceItemCollection as $sourceItem) {
            $sku = $sourceItem->getSku();
            $sourceCode = $sourceItem->getSourceCode();
            $quantity = (int)$sourceItem->getQuantity();
            $status = (int)$sourceItem->getStatus();
            $totalQty = isset($sources[$sku]['total_qty']) ? ($sources[$sku]['total_qty'] + $quantity) : $quantity;

            $sources[$sku]['total_qty'] = $totalQty;
            $sources[$sku]['sources'][] = [
                'source_code' => $sourceCode,
                'quantity' => $quantity,
                'status' => $status
            ];
        }

        if (!$sources) {
            return $productCollection;
        }

        foreach ($productCollection as $product) {
            $sku = $product->getSku();
            $product->setData('inventory_sources', isset($sources[$sku]['sources']) ? json_encode($sources[$sku]['sources']) : '[]');
            $product->setStockTotalQty($sources[$sku]['total_qty'] ?? 0);
        }

        return $productCollection;
    }

    /**
     * @param ProductCollection $collection
     *
     * @return ProductCollection
     */
    protected function assingStockToProductsCollectionLegacy(ProductCollection $collection)
    {
        $skus = [];
        foreach ($collection as $product) {
            $skus[] = $product->getSku();
        }
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(SourceItemInterface::SKU, $skus, 'in')
            ->create();
        $stocks = $this->sourceItemRepository->getList($searchCriteria)->getItems();
        $stockData = [];
        foreach ($stocks as $stock) {
            if (isset($stock['sku']) && isset($stock['quantity']) && isset($stock['source_code'])) {
                $stockTotalQty = isset($stockData[$stock['sku']]) ? $stockData[$stock['sku']]['total_qty'] + $stock['quantity'] : $stock['quantity'];
                $stockData[$stock['sku']]['total_qty'] = $stockTotalQty;
                $stockData[$stock['sku']][$stock['source_code']] = $stock['quantity'];
            }
        }
        foreach ($collection as $product) {
            if (isset($stockData[$product->getSku()])) {
                foreach ($stockData[$product->getSku()] as $key => $productStock) {
                    if ($key != 'total_qty') {
                        $product->setData(HelperProductPuller::prepareFieldNameBySourceCode($key), $productStock);
                    } else {
                        $product->setStockTotalQty((int)$productStock);
                    }
                }
            } else {
                $product->setStockTotalQty(0);
            }
        }
        return $collection;
    }

    /**
     * @param ProductCollection $productCollection
     *
     *
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

        $start = microtime(true);
        $this->eventManager->dispatch('prepare_document_from_product_before', ['eventData' => $eventData]);
        \G4NReact\MsCatalog\Profiler::increaseTimer('observer => prepare_document_from_product_before', (microtime(true) - $start));

        if ($eventData->disable === true) {
            return $document;
        }

        $document->setUniqueId($product->getId() . '_' . self::OBJECT_TYPE . '_' . $product->getStoreId());
        $document->setObjectId($product->getId());
        $document->setObjectType(self::OBJECT_TYPE);

        $start = microtime(true);
        $this->handleCategoryId($product, $document);
        \G4NReact\MsCatalog\Profiler::increaseTimer('handleCategoryId', (microtime(true) - $start));

        $start = microtime(true);
        $this->addAttributes($product, $document);
        \G4NReact\MsCatalog\Profiler::increaseTimer('addAttributes', (microtime(true) - $start));

        $start = microtime(true);
        $this->handleImages($product, $document);
        \G4NReact\MsCatalog\Profiler::increaseTimer('addMediaGallery', (microtime(true) - $start));

        $start = microtime(true);
        $this->handleRequestPath($document);
        \G4NReact\MsCatalog\Profiler::increaseTimer('handleRequestPath', (microtime(true) - $start));

        $start = microtime(true);
        $this->addUrl($document);
        \G4NReact\MsCatalog\Profiler::increaseTimer('addUrl', (microtime(true) - $start));

        $start = microtime(true);
        $this->addCategoryPosition($product, $document);
        \G4NReact\MsCatalog\Profiler::increaseTimer('addCategoryPosition', (microtime(true) - $start));

        $eventData = [
            'product' => $product,
            'document' => $document,
        ];

        $start = microtime(true);
        $this->eventManager->dispatch('prepare_document_from_product_after', $eventData);
        \G4NReact\MsCatalog\Profiler::increaseTimer('observer => prepare_document_from_product_after', (microtime(true) - $start));

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
            if (is_object($value)) {
                continue;
            }

            $attribute = $this->eavConfig->getAttribute('catalog_product', $field);

            $start = microtime(true);
            $searchTermField = $this->searchTerms->prepareSearchTermField($attribute->getAttributeCode());
            if ($searchTermField) {
                $textValue = $value;
                if ($attribute->getFrontend()) {
                    $textValue = $attribute->getFrontend()->getValue($product);
                }
                if ($field = $document->getField($searchTermField)) {
                    $field->setValue($field->getValue() . ' ' . $textValue);
                } else {
                    $document->createField(
                        $searchTermField,
                        $textValue,
                        Document\Field::FIELD_TYPE_TEXT_SEARCH,
                        true,
                        false
                    );
                }
            }
            \G4NReact\MsCatalog\Profiler::increaseTimer(' ====> addAttributes > add searchTermField', (microtime(true) - $start));

            $start = microtime(true);
            $document->setField(
                $this->queryHelper->getFieldByAttribute($attribute, $product->getData($attribute->getAttributeCode()))
            );
            \G4NReact\MsCatalog\Profiler::increaseTimer(' ====> addAttributes > add all attributes', (microtime(true) - $start));

            $start = microtime(true);
            // force creating Fields that should be indexed but have not any value @ToDo: temporarily - and are not multivalued
            foreach ($this->searchTerms->getForceIndexingAttributes() as $attributeCode) {
                if (!$document->getField($attributeCode)) {
                    $field = $this->queryHelper->getFieldByAttributeCode($attributeCode, null);
                    if (!$field->getMultiValued()) {
                        $document->setField($field);
                    }
                }
            }

            $document = $this->setFieldIsVisibleOnFront($attribute, $document, $value);
            \G4NReact\MsCatalog\Profiler::increaseTimer(' ====> addAttributes > add force indexing attributes', (microtime(true) - $start));
        }
    }

    /**
     * @param AbstractAttribute $attribute
     * @param Document $document
     * @param $value
     *
     * @return Document
     */
    protected function setFieldIsVisibleOnFront(AbstractAttribute $attribute, Document $document, $value)
    {
        if ($attribute->getIsVisibleOnFront() && $value) {
            if (!$document->getField('attribute_codes_is_visible_on_front')) {
                $document->setField(
                    new Document\Field(
                        'attribute_codes_is_visible_on_front',
                        $this->jsonSerializer->serialize([$attribute->getAttributeCode() => $value]),
                        Document\Field::FIELD_TYPE_TEXT,
                        false,
                        false
                    )
                );
            } else {
                $attributeCodesField = $document->getField('attribute_codes_is_visible_on_front');
                $data = $this->jsonSerializer->unserialize($attributeCodesField->getValue());
                $data[$attribute->getAttributeCode()] = $value;

                $attributeCodesField->setValue($this->jsonSerializer->serialize($data));
            }
        }

        return $document;
    }

    /**
     * @param Product $product
     * @param Document $document
     * @throws LocalizedException
     */
    protected function handleImages(Product $product, Document $document): void
    {
        $mediaGallery = $this->getGalleryImages($product);
        $mediaGalleryJson = $this->jsonSerializer->serialize($mediaGallery);

        if ($document->getField('image') && isset($mediaGallery[0]['large_image_url'])) {
            $document->setFieldValue('image', $mediaGallery[0]['large_image_url']);
        }
        if ($document->getField('medium_image') && isset($mediaGallery[0]['medium_image_url'])) {
            $document->setFieldValue('medium_image', $mediaGallery[0]['medium_image_url']);
        } elseif (isset($mediaGallery[0]['medium_image_url'])) {
            $document->createField(
                'medium_image',
                $mediaGallery[0]['medium_image_url'],
                Document\Field::FIELD_TYPE_STRING,
                false,
                false
            );
        }
        if ($document->getField('small_image') && isset($mediaGallery[0]['small_image_url'])) {
            $document->setFieldValue('small_image', $mediaGallery[0]['small_image_url']);
        }
        if ($document->getField('thumbnail') && isset($mediaGallery[0]['small_image_url'])) {
            $document->setFieldValue('thumbnail', $mediaGallery[0]['small_image_url']);
        }
        if ($document->getField('swatch_image') && isset($mediaGallery[0]['swatch_image_url'])) {
            $document->setFieldValue('swatch_image', $mediaGallery[0]['swatch_image_url']);
        }

        $document->setField(
            $this->queryHelper
                ->getFieldByAttributeCode('media_gallery', $mediaGalleryJson)
        );
    }

    /**
     * @param ProductInterface $product
     * @return array
     */
    public function getGalleryImages(ProductInterface $product): array
    {
        $gallery = [];
        $images = $product->getMediaGalleryImages();
        if ($images instanceof \Magento\Framework\Data\Collection) {
            /** @var $image \Magento\Framework\DataObject */
            foreach ($images as $image) {
                $swatchSmallImageUrl = $this->imageUrlBuilder
                    ->getUrl($image->getFile(), 'product_swatch_image_small');
                $smallImageUrl = $this->imageUrlBuilder
                    ->getUrl($image->getFile(), 'product_page_image_small');
                $mediumImageUrl = $this->imageUrlBuilder
                    ->getUrl($image->getFile(), 'product_page_image_medium');
                $largeImageUrl = $this->imageUrlBuilder
                    ->getUrl($image->getFile(), 'product_page_image_large');

                $gallery[] = [
                    'swatch_image_url' => $swatchSmallImageUrl,
                    'small_image_url' => $smallImageUrl,
                    'medium_image_url' => $mediumImageUrl,
                    'large_image_url' => $largeImageUrl,
                    'position' => $image->getPosition(),
                    'label' => $image->getLabel() ?: $product->getName(),
                    'media_type' => $image->getMediaType(),
                    'disabled' => !!$image->getDisabled(),
                ];
            }
        }

        return $gallery;
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
