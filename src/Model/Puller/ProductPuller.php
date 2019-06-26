<?php

namespace G4NReact\MsCatalogMagento2\Model\Puller;

use G4NReact\MsCatalog\Document;
use G4NReact\MsCatalogMagento2\Model\AbstractPuller;
use Iterator;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Eav\Model\ResourceModel\Entity\Attribute;
use G4NReact\MsCatalogMagento2\Helper\MsCatalog as MsCatalogHelper;

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
     * ProductPuller constructor
     *
     * @param ProductCollectionFactory $productCollectionFactory
     * @param EavConfig $eavConfig
     * @param Attribute $eavAttribute
     * @param MsCatalogHelper $msCatalogHelper
     */
    public function __construct(
        ProductCollectionFactory $productCollectionFactory,
        EavConfig $eavConfig,
        Attribute $eavAttribute,
        MsCatalogHelper $msCatalogHelper
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->eavConfig = $eavConfig;
        $this->eavAttribute = $eavAttribute;

        parent::__construct($msCatalogHelper);
    }

    /**
     * @return ProductCollection
     */
    public function getCollection(): ProductCollection
    {
        $productCollection = $this->productCollectionFactory->create();

        if ($this->ids !== null) {
            $productCollection->addAttributeToFilter('entity_id', array('in' => $this->ids));
        }

        $productCollection->addAttributeToSelect('*')
            ->setPageSize($this->pageSize)
            ->setCurPage($this->curPage);

        return $productCollection;
    }

    /**
     * @return Document
     */
    public function current(): Document
    {
        $product = $this->pageArray[$this->position];

        $document = new Document();

        $document->setUniqueId($product->getId() . '_' . 'product' . '_' . $product->getStoreId());
        $document->setObjectId($product->getId());
        $document->setObjectType('product'); // @ToDo: move it to const

        foreach ($product->getData() as $field => $value) {
            $attribute = $this->eavConfig->getAttribute('catalog_product', $field);
            $document->setField(
                $field,
                $product->getData($field),
                $attribute->getBackendType(),
                $attribute->getIsFilterable() ? true : false
            );
        }

        return $document;
    }

    /**
     * @return Iterator
     */
    public function pull(): Iterator
    {
        // TODO: Implement pull() method.
    }
}
