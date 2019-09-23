<?php

namespace G4NReact\MsCatalogMagento2\Model\Puller;

use G4NReact\MsCatalog\Document;
use G4NReact\MsCatalog\QueryInterface;
use G4NReact\MsCatalog\ResponseInterface;
use G4NReact\MsCatalogMagento2\Helper\Config as ConfigHelper;
use G4NReact\MsCatalogMagento2\Helper\SearchTerms\SearchTermsField;
use G4NReact\MsCatalogMagento2\Model\AbstractPuller;
use Magento\Framework\Event\Manager;
use Magento\Search\Model\Query;
use Magento\Search\Model\ResourceModel\Query\CollectionFactory;
use Magento\Search\Model\ResourceModel\Query\Collection;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Search\Model\ResourceModel\SynonymGroup\CollectionFactory as SynonymGroupCollFactory;

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
     * @var SynonymGroupCollFactory
     */
    protected $synonymGroupCollFactory;

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
        SearchTermsField $searchTermsField,
        SynonymGroupCollFactory $synonymGroupCollFactory
    ) {
        $this->searchQueryCollFactory = $searchQueryCollFactory;
        $this->eventManager = $eventManager;
        $this->searchTermsField = $searchTermsField;
        $this->synonymGroupCollFactory = $synonymGroupCollFactory;
        parent::__construct($magento2ConfigHelper);
    }

    /**
     * @return Collection|mixed
     * @throws NoSuchEntityException
     */
    public function getCollection()
    {
        $collection = $this->searchQueryCollFactory->create();

        if ($this->getIds()) {
            $collection->addFieldToFilter('main_table.query_id', ['in' => $this->getIds()]);
        }

        $collection
            ->addStoreFilter($this->magento2ConfigHelper->getStore()->getId())
            ->setPageSize($this->getPageSize())
            ->setCurPage($this->getCurPage());

//        $this->addSynonymsToSearchTermsCollection($collection);

        $this->eventManager->dispatch('ms_catalog_get_search_terms_collection', ['collection' => $collection]);

        return $collection;
    }

    /**
     * @return \Magento\Search\Model\ResourceModel\SynonymGroup\Collection
     * @throws NoSuchEntityException
     */
    protected function getSynonymGroupCollection()
    {
        return $this->synonymGroupCollFactory->create()
            ->addFieldToFilter('store_id', $this->magento2ConfigHelper->getStore()->getId())
            ->setCurPage($this->getCurPage())
            ->setPageSize($this->getPageSize());
    }

    /**
     * @param Collection $collection
     *
     * @throws NoSuchEntityException
     */
    protected function addSynonymsToSearchTermsCollection(Collection $collection)
    {
        $queryTextArr = [];
        foreach ($collection as $item) {
            $item
                ->setData(SearchTermsField::REACT_STORE_FRONT_ID, SearchTermsField::REACT_STORE_FRONT_ID_SEARCH_TERM)
                ->setData('is_synonym', false);
            $queryTextArr[] =
                [
                    'query_text' => $item->getQueryText(),
                    'item'       => $item
                ];
        }

        $synonymGroupCollection = $this->getSynonymGroupCollection();
        $searchTermIdMax = max($collection->getAllIds());

        foreach ($synonymGroupCollection as $synonyms) {
            $queryTextIterator = 0;
            foreach ($queryTextArr as $queryText) {
                $synonymsText = explode(',', $synonyms->getSynonyms());
                $synonymKey = array_search(trim($queryText['query_text']), $synonymsText);
                if ($synonymKey !== false) {
                    unset($synonymsText[$synonymKey]);
                    foreach ($synonymsText as $key => $synonymText) {
                        $newSynonym = clone $queryText['item'];
                        /** @var int $newSynonymId - set custom id to prevent from exception if synonym has the same id than search term */
                        $newSynonymId = $searchTermIdMax + $synonyms->getId() + $key + $queryTextIterator;
                        $newSynonym
                            ->setData(SearchTermsField::REACT_STORE_FRONT_ID, SearchTermsField::REACT_STORE_FRONT_ID_SYNONYM)
                            ->setData(SearchTermsField::IS_SYNONYM, true)
                            ->setQueryText($synonymText)
                            ->setData(SearchTermsField::ORIGINAL_SEARCH_TERM_VALUE, $queryText['query_text'])
                            ->setId($newSynonymId);

                        $collection->addItem($newSynonym);
                    }
                }
                $queryTextIterator++;
            }
        }
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
            'document'    => $document,
        ];
        $this->eventManager->dispatch('prepare_document_from_search_term_before', ['eventData' => $eventData]);

//        $document->setUniqueId($searchTerm->getId() . '_' . SearchTermsField::OBJECT_TYPE . '_' . $storeId . '_' . $searchTerm->getReactStoreFrontId());
        $document->setUniqueId($searchTerm->getId() . '_' . SearchTermsField::OBJECT_TYPE . '_' . $storeId);
        $document->setObjectId($searchTerm->getId());
        $document->setObjectType(SearchTermsField::OBJECT_TYPE);

        foreach ($searchTerm->getData() as $field => $value) {
            $document->createField(
                $field,
                $searchTerm->getData($field),
                $this->searchTermsField->getFieldTypeByColumnName($field),
                SearchTermsField::getIsIndexable($field),
                SearchTermsField::getIsMultiValued($field, $value)
            );
        }

        $eventData = [
            'search_term' => $searchTerm,
            'document'    => $document,
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
