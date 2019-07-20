<?php

namespace G4NReact\MsCatalogMagento2\Model\Puller;

use G4NReact\MsCatalog\Document;
use G4NReact\MsCatalog\QueryInterface;
use G4NReact\MsCatalog\ResponseInterface;
use G4NReact\MsCatalogMagento2\Helper\Cms\Field;
use G4NReact\MsCatalogMagento2\Model\AbstractPuller;
use G4NReact\MsCatalogMagento2\Helper\Config as ConfigHelper;
use Magento\Cms\Model\ResourceModel\Page\Collection as CmsPageCollection;
use Magento\Cms\Model\ResourceModel\Page\CollectionFactory as CmsPageCollectionFactory;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Framework\Event\Manager as EventManager;
use Magento\Eav\Model\ResourceModel\Entity\Attribute;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class CmsPuller
 * @package G4NReact\MsCatalogMagento2\Model\Puller
 */
class CmsPuller extends AbstractPuller
{
    /**
     * @var string Type of object
     */
    const OBJECT_TYPE = 'cms';

    /**
     * @var CmsPageCollectionFactory
     */
    protected $cmsPageCollectionFactory;

    /**
     * @var Attribute
     */
    protected $eavAttribute;

    /**
     * @var EavConfig
     */
    protected $eavConfig;

    /**
     * @var EventManager
     */
    protected $eventManager;

    /**
     * @var Field
     */
    protected $helperCmsField;

    /**
     * CmsPuller constructor
     *
     * @param CmsPageCollectionFactory $cmsPageCollectionFactory
     * @param EavConfig $eavConfig
     * @param Attribute $eavAttribute
     * @param ConfigHelper $magento2ConfigHelper
     * @param EventManager $eventManager
     * @param Field $helperCmsField
     */
    public function __construct(
        CmsPageCollectionFactory $cmsPageCollectionFactory,
        EavConfig $eavConfig,
        Attribute $eavAttribute,
        ConfigHelper $magento2ConfigHelper,
        EventManager $eventManager,
        Field $helperCmsField
    ) {
        $this->cmsPageCollectionFactory = $cmsPageCollectionFactory;
        $this->eavConfig = $eavConfig;
        $this->eavAttribute = $eavAttribute;
        $this->eventManager = $eventManager;
        $this->helperCmsField = $helperCmsField;
        $this->setType(self::OBJECT_TYPE);

        parent::__construct($magento2ConfigHelper);
    }

    /**
     * @return CmsPageCollection
     * @throws NoSuchEntityException
     */
    public function getCollection(): CmsPageCollection
    {
        $cmsPageCollection = $this->cmsPageCollectionFactory->create();

        if ($this->ids !== null) {
            $cmsPageCollection->addAttributeToFilter('entity_id', array('in' => $this->ids));
        }

        /** @var CmsPageCollection $cmsPageCollection */
        $cmsPageCollection
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
            ->addStoreFilter($this->magento2ConfigHelper->getStore()->getId())
            ->setPageSize($this->pageSize)
            ->setCurPage($this->curPage);

        $this->eventManager->dispatch('ms_catalog_get_cms_page_collection', ['collection' => $cmsPageCollection]);

        return $cmsPageCollection;
    }

    /**
     * @return Document
     * @throws NoSuchEntityException
     */
    public function current(): Document
    {
        $page = $this->pageArray[$this->position];
        $storeId = $this->magento2ConfigHelper->getStore()->getId();

        $document = new Document();

        $eventData = [
            'cms_page' => $page,
            'document' => $document,
        ];
        $this->eventManager->dispatch('prepare_document_from_cms_page_before', ['eventData' => $eventData]);

        $document->setUniqueId($page->getId() . '_' . self::OBJECT_TYPE . '_' . $storeId);
        $document->setObjectId($page->getId());
        $document->setObjectType(self::OBJECT_TYPE);

        foreach ($page->getData() as $field => $value) {
            $document->createField(
                $field,
                $page->getData($field),
                $this->helperCmsField->getFieldTypeByCmsColumnName($field) ?? Document\Field::FIELD_TYPE_STRING,
                false,
                Field::getIsCmsMultivalued($field, $value)
            );
        }

        $eventData = [
            'cms_page' => $page,
            'document' => $document,
        ];
        $this->eventManager->dispatch('prepare_document_from_cms_page_after', ['eventData' => $eventData]);

        return $document;
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
