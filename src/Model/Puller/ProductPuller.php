<?php

namespace G4NReact\MsCatalogMagento2\Model\Puller;

use G4NReact\MsCatalogIndexer\Config;
use G4NReact\MsCatalog\Document;
use G4NReact\MsCatalogMagento2\Model\AbstractPuller;

/**
 * Class ProductPuller
 * @package G4NReact\MsCatalogMagento2\Model\Puller
 */
class ProductPuller extends AbstractPuller
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute
     */
    protected $eavAttribute;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Config
     */
    protected $eavConfig;

    /**
     * ProductPuller constructor
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->eavConfig = $eavConfig;
        $this->eavAttribute = $eavAttribute;

        parent::__construct($scopeConfig);
    }

    /**
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getCollection()
    {
        $collection = $this->productCollectionFactory->create();

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
}
