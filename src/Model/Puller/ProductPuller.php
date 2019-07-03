<?php

namespace G4NReact\MsCatalogMagento2\Model\Puller;

use G4NReact\MsCatalog\Document;
use G4NReact\MsCatalog\QueryInterface;
use G4NReact\MsCatalog\ResponseInterface;
use G4NReact\MsCatalogMagento2\Model\AbstractPuller;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Eav\Model\ResourceModel\Entity\Attribute;
use Magento\Framework\Data\Collection as DataCollection;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use G4NReact\MsCatalogMagento2\Helper\MsCatalog as MsCatalogHelper;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class ProductPuller
 * @package G4NReact\MsCatalogMagento2\Model\Puller
 */
class ProductPuller extends AbstractPuller
{
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
     * ProductPuller constructor
     *
     * @param ProductCollectionFactory $productCollectionFactory
     * @param EavConfig $eavConfig
     * @param Attribute $eavAttribute
     * @param JsonSerializer $jsonSerializer
     * @param MsCatalogHelper $msCatalogHelper
     */
    public function __construct(
        ProductCollectionFactory $productCollectionFactory,
        EavConfig $eavConfig,
        Attribute $eavAttribute,
        JsonSerializer $jsonSerializer,
        MsCatalogHelper $msCatalogHelper
    )
    {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->eavConfig = $eavConfig;
        $this->eavAttribute = $eavAttribute;
        $this->jsonSerializer = $jsonSerializer;

        parent::__construct($msCatalogHelper);
    }

    /**
     * @return ProductCollection
     * @throws LocalizedException
     */
    public function getCollection(): ProductCollection
    {
        /** @var ProductCollection $productCollection */
        $productCollection = $this->productCollectionFactory->create();

        $this->eventManager->dispatch(
            'before_ms_catalog_magento_product_puller_collection',
            ['product_collection' => $productCollection]
        );

        if ($this->ids !== null) {
            $productCollection->addAttributeToFilter('entity_id', array('in' => $this->ids));
        }

        $productCollection->addAttributeToSelect('*')
            ->addMediaGalleryData()
            ->setPageSize($this->pageSize)
            ->setCurPage($this->curPage);

        $this->eventManager->dispatch(
            'after_ms_catalog_magento_product_puller_collection',
            ['product_collection' => $productCollection]
        );

        return $productCollection;
    }

    /**
     * @return Document
     * @throws LocalizedException
     */
    public function current(): Document
    {
        /** @var Product $product */
        $product = $this->pageArray[$this->position];

        $document = new Document();

        $this->eventManager->dispatch(
            'before_ms_catalog_magento_product_document',
            ['document' => $document]
        );

        $document->setUniqueId($product->getId() . '_' . 'product' . '_' . $product->getStoreId());
        $document->setObjectId($product->getId());
        $document->setObjectType('product'); // @ToDo: move it to const

        foreach ($product->getData() as $field => $value) {
            $attribute = $this->eavConfig->getAttribute('catalog_product', $field);
            $document->setField(
                $field,
                $product->getData($field),
                $attribute->getBackendType(),
                $attribute->getIsFilterable() ? true : false,
                in_array($attribute->getFrontendInput(), MsCatalogHelper::$multiValuedAttributeFrontendInput)
            );
        }

        $mediaGalleryJson = $this->getMediaGalleryJson($product->getMediaGalleryImages());
        $document->setField(
            'media_gallery',
            $mediaGalleryJson,
            'string',
            false,
            false
        );

        $this->eventManager->dispatch(
            'after_ms_catalog_magento_product_document',
            ['document' => $document]
        );

        return $document;
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
     * @param QueryInterface|null $query
     *
     * @return ResponseInterface
     */
    public function pull(QueryInterface $query = null): ResponseInterface
    {
        // TODO: Implement pull() method.
    }
}
