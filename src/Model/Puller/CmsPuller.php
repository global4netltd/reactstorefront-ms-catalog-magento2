<?php

namespace G4NReact\MsCatalogMagento2\Model\Puller;

use G4NReact\MsCatalogIndexer\Config;
use G4NReact\MsCatalog\Document;
use G4NReact\MsCatalogMagento2\Model\AbstractPuller;

/**
 * Class CmsPuller
 * @package G4NReact\MsCatalogMagento2\Model\Puller
 */
class CmsPuller extends AbstractPuller
{
    public static $fieldTypeMap = [
        'page_id' => 'int',
        'title' => 'string',
        'page_layout' => 'string',
        'meta_keywords' => 'string',
        'meta_description' => 'string',
        'identifier' => 'string',
        'content_heading' => 'string',
        'content' => 'string',
        'creation_time' => 'datetime',
        'update_time' => 'datetime',
        'is_active' => 'bool',
        'sort_order' => 'int',
        'store_id' => 'int',
    ];

    /**
     * @var \Magento\Cms\Model\ResourceModel\Page\CollectionFactory
     */
    protected $pageCollectionFactory;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute
     */
    protected $eavAttribute;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Config
     */
    protected $eavConfig;

    /**
     * CmsPuller constructor
     * @param \Magento\Cms\Model\ResourceModel\Page\CollectionFactory $pageCollectionFactory
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Cms\Model\ResourceModel\Page\CollectionFactory $pageCollectionFactory,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->pageCollectionFactory = $pageCollectionFactory;
        $this->eavConfig = $eavConfig;
        $this->eavAttribute = $eavAttribute;

        parent::__construct($scopeConfig);
    }

    /**
     * @return \Magento\Cms\Model\ResourceModel\Page\Collection|void
     */
    public function getCollection()
    {
        $collection = $this->pageCollectionFactory->create();

        if ($this->ids !== null) {
            $collection->addAttributeToFilter('entity_id', array('in' => $this->ids));
        }

        $collection
            ->addFieldToSelect('page_id')
            ->addFieldToSelect('title')
            ->addFieldToSelect('page_layout')
            ->addFieldToSelect('meta_keywords')
            ->addFieldToSelect('meta_description')
            ->addFieldToSelect('identifier')
            ->addFieldToSelect('content_heading')
            ->addFieldToSelect('content')
            ->addFieldToSelect('creation_time')
            ->addFieldToSelect('update_time')
            ->addFieldToSelect('is_active')
            ->addFieldToSelect('sort_order')
            ->setPageSize($this->pageSize)
            ->setCurPage($this->curPage);

        return $collection;
    }

    /**
     * @return Document
     */
    public function current(): Document
    {
        $page = $this->pageArray[$this->position];

        $document = new Document();

        $document->setUniqueId($page->getId() . '_' . 'cms' . '_' . $page->getStoreId()[0]);
        $document->setObjectId($page->getId());
        $document->setObjectType('cms'); // @ToDo: move it to const

        foreach ($page->getData() as $field => $value) {
            $document->setField(
                $field,
                $page->getData($field),
                self::$fieldTypeMap[$field] ?? 'string',
                false
            );
        }

        return $document;
    }
}
