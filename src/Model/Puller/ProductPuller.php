<?php

namespace G4NReact\MsCatalogMagento2\Model\Puller;

use G4NReact\MsCatalog\Document;
use G4NReact\MsCatalog\QueryInterface;
use G4NReact\MsCatalog\ResponseInterface;
use G4NReact\MsCatalogMagento2\Model\AbstractPuller;
use G4NReact\MsCatalogMagento2\Model\Attribute\SearchTerms;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Eav\Model\ResourceModel\Entity\Attribute;
use Magento\Framework\Data\Collection as DataCollection;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use G4NReact\MsCatalogMagento2\Helper\Config as ConfigHelper;
use G4NReact\MsCatalogMagento2\Helper\Query as QueryHelper;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class ProductPuller
 * @package G4NReact\MsCatalogMagento2\Model\Puller
 */
class ProductPuller extends AbstractPuller
{
    /** @var string product */
    const PRODUCT = 'product';
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
     * ProductPuller constructor
     *
     * @param ProductCollectionFactory $productCollectionFactory
     * @param EavConfig $eavConfig
     * @param Attribute $eavAttribute
     * @param JsonSerializer $jsonSerializer
     * @param ConfigHelper $magento2ConfigHelper
     */
    public function __construct(
        ProductCollectionFactory $productCollectionFactory,
        EavConfig $eavConfig,
        Attribute $eavAttribute,
        JsonSerializer $jsonSerializer,
        ConfigHelper $magento2ConfigHelper,
        SearchTerms $searchTerms
    )
    {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->eavConfig = $eavConfig;
        $this->eavAttribute = $eavAttribute;
        $this->jsonSerializer = $jsonSerializer;
        $this->searchTerms = $searchTerms;

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

        if ($this->ids !== null) {
            $productCollection->addAttributeToFilter('entity_id', array('in' => $this->ids));
        }

        $productCollection->addAttributeToSelect('*')
            ->addStoreFilter()
            ->setPageSize($this->pageSize)
            ->setCurPage($this->curPage)
            ->addMediaGalleryData();

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

        $document->setUniqueId($product->getId() . '_' . self::PRODUCT . '_' . $product->getStoreId());
        $document->setObjectId($product->getId());
        $document->setObjectType(self::PRODUCT);

        foreach ($product->getData() as $field => $value) {
            $attribute = $this->eavConfig->getAttribute('catalog_product', $field);

            if ($searchTermField = $this->searchTerms->prepareSearchTermField($attribute->getAttributeCode())) {
                if ($document->getField($searchTermField)) {
                    $document->createField($searchTermField, $document->getField($searchTermField) . ' ' . $product->getData($attribute->getAttributeCode()));
                } else {
                    $document->createField(
                        $searchTermField,
                        $product->getData($attribute->getAttributeCode()),
                        'string',
                        true,
                        true
                    );
                }
            }

            $document->createField(
                $field,
                $product->getData($field),
                $this->magento2ConfigHelper->getAttributeFieldType($attribute),
                $attribute->getIsFilterable() ? true : false,
                in_array($attribute->getFrontendInput(), QueryHelper::$multiValuedAttributeFrontendInput)
            );
        }

        $mediaGalleryJson = $this->getMediaGalleryJson($product->getMediaGalleryImages());
        $document->createField(
            'media_gallery',
            $mediaGalleryJson,
            'string',
            false,
            false
        );

        $document->createField(
            'category_ids',
            $product->getCategoryIds(),
            'int',
            true,
            true
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

