<?php

namespace G4NReact\MsCatalogMagento2\Model\Puller;

use G4NReact\MsCatalog\Document;
use G4NReact\MsCatalog\QueryInterface;
use G4NReact\MsCatalog\ResponseInterface;
use G4NReact\MsCatalogMagento2\Helper\Config as ConfigHelper;
use G4NReact\MsCatalogMagento2\Helper\SearchTerms\SearchTermsField;
use G4NReact\MsCatalogMagento2\Model\AbstractPuller;
use Magento\Framework\Event\Manager;
use Magento\Search\Model\ResourceModel\Query\CollectionFactory;
use Magento\Search\Model\ResourceModel\Query\Collection;
use Magento\Framework\Exception\NoSuchEntityException;

class SearchTermsPuller extends AbstractPuller
{

    /**
     * @var CollectionFactory
     */
    protected $searchQueryCollFactory;

    /**
     * @var Manager
     */
    protected $eventManager;

    /**
     * @var SearchTermsField
     */
    protected $searchTermsField;

    /**
     * SearchTermsPuller constructor.
     *
     * @param ConfigHelper $magento2ConfigHelper
     * @param CollectionFactory $searchQueryCollFactory
     * @param Manager $eventManager
     * @param SearchTermsField $searchTermsField
     *
     * @throws NoSuchEntityException
     */
    public function __construct(
        ConfigHelper $magento2ConfigHelper,
        CollectionFactory $searchQueryCollFactory,
        Manager $eventManager,
        SearchTermsField $searchTermsField
    ) {
        $this->searchQueryCollFactory = $searchQueryCollFactory;
        $this->eventManager = $eventManager;
        $this->searchTermsField = $searchTermsField;
        parent::__construct($magento2ConfigHelper);
    }

    /**
     * @return Collection|mixed
     * @throws NoSuchEntityException
     */
    public function getCollection()
    {
        $collection = $this->searchQueryCollFactory->create()
            ->addStoreFilter($this->magento2ConfigHelper->getStore()->getId())
            ->setPageSize($this->getPageSize())
            ->setCurPage($this->getCurPage());

        $this->eventManager->dispatch('ms_catalog_get_search_terms_collection', ['collection' => $collection]);
        
        return $collection;
    }

    /**
     * @return Document
     * @throws NoSuchEntityException
     */
    public function current(): Document
    {
        $searchTerm = $this->pageArray[$this->position];
        $storeId = $this->magento2ConfigHelper->getStore()->getId();

        $document = new Document();

        $eventData = [
            'search_term' => $searchTerm,
            'document' => $document,
        ];
        $this->eventManager->dispatch('prepare_document_from_search_term_before', ['eventData' => $eventData]);

        $document->setUniqueId($searchTerm->getId() . '_' . SearchTermsField::OBJECT_TYPE . '_' . $storeId);
        $document->setObjectId($searchTerm->getId());
        $document->setObjectType(SearchTermsField::OBJECT_TYPE);

        foreach ($searchTerm->getData() as $field => $value) {
            $document->createField(
                $field,
                $searchTerm->getData($field),
                $this->searchTermsField->getFieldTypeByCmsColumnName($field),
                SearchTermsField::getIsIndexable($field),
                SearchTermsField::getIsMultiValued($field,$value)
            );
        }

        $eventData = [
            'search_term' => $searchTerm,
            'document' => $document,
        ];
        $this->eventManager->dispatch('prepare_document_from_search_term_after', $eventData);

        return $document;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return SearchTermsField::OBJECT_TYPE;
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
