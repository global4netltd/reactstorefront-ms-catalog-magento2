<?php

namespace G4NReact\MsCatalogMagento2\Cron\Maintenance;

use G4NReact\MsCatalog\Client\ClientFactory;
use G4NReact\MsCatalogMagento2\Helper\Config as ConfigHelper;
use G4NReact\MsCatalogMagento2\Helper\Query as QueryHelper;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\App\Area;
use Magento\Framework\App\State as AppState;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Class ReindexAll
 * @package G4NReact\MsCatalogMagento2\Cron
 */
class Category
{
    /**
     * @var AppState
     */
    protected $appState;
    
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Emulation
     */
    protected $emulation;
    
    /**
     * @var CollectionFactory
     */
    protected $categoryCollection;

    /**
     * @var ConfigHelper
     */
    protected $configHelper;

    /**
     * @var QueryHelper
     */
    protected $queryHelper;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Product constructor.
     * @param AppState $appState
     * @param StoreManagerInterface $storeManager
     * @param Emulation $emulation
     * @param CollectionFactory $categoryCollection
     * @param ConfigHelper $configHelper
     * @param QueryHelper $queryHelper
     * @param LoggerInterface $logger
     */
    public function __construct(
        AppState $appState,
        StoreManagerInterface $storeManager,
        Emulation $emulation,
        CollectionFactory $categoryCollection,
        ConfigHelper $configHelper,
        QueryHelper $queryHelper,
        LoggerInterface $logger
    ) {
        $this->appState = $appState;
        $this->storeManager = $storeManager;
        $this->emulation = $emulation;
        $this->categoryCollection = $categoryCollection;
        $this->configHelper = $configHelper;
        $this->queryHelper = $queryHelper;
        $this->logger = $logger;
    }

    /**
     * @return void
     */
    public function execute() {
        $stores = $this->storeManager->getStores();
        foreach ($stores as $store) {
            $this->appState->emulateAreaCode(Area::AREA_FRONTEND, function () use ($store) {
                $this->removeCategoryNotActiveInMagento($store);
            });
        }
    }

    /**
     * @param StoreInterface $store
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function removeCategoryNotActiveInMagento(StoreInterface $store)
    {
        // start store emulation
        $this->emulation->startEnvironmentEmulation($store->getId(), Area::AREA_FRONTEND, true);

        $searchEngineConfig = $this->configHelper->getConfiguration();
        if ($searchEngineConfig->getRemoveNotActive()) {
            $solrCategories = $this->getEntityIdFromSolr();
            $magentoCategories = $this->getEntityIdFromMagento();

            if (empty($solrCategories) || empty($magentoCategories)) {
                return;
            }

            $toRemoveIds = [];
            foreach ($solrCategories as $category) {
                if (!in_array($category['id'], $magentoCategories)) {
                    $toRemoveIds[] = $category['solr_id'];
                }
            }

            if (!empty($toRemoveIds)) {
                try {
                    $searchEngineClient = ClientFactory::create($searchEngineConfig);
                    $searchEngineClient->deleteByIds($toRemoveIds);
                } catch (\Exception $e) {
                    $this->logger->error('Problem with remove categories from solr', ['remove_ids' => $toRemoveIds, 'message' => $e->getMessage(), 'exception' => $e]);
                }
            }
        }

        // end store emulation
        $this->emulation->stopEnvironmentEmulation();
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getEntityIdFromSolr()
    {
        $searchEngineConfig = $this->configHelper->getConfiguration();
        $searchEngineClient = ClientFactory::create($searchEngineConfig);

        $query = $searchEngineClient->getQuery();
        $query->setPageStart(0);
        $query->setPageSize(500000);
        $query->addFilters([
            [$this->queryHelper->getFieldByAttributeCode('store_id', $this->storeManager->getStore()->getId())],
            [$this->queryHelper->getFieldByAttributeCode('object_type', 'category')]
        ]);

        $query->addFieldsToSelect([
            $this->queryHelper->getFieldByAttributeCode('id')
        ]);

        $entityIds = [];
        $documentCollection = $query->getResponse()->getDocumentsCollection();
        foreach ($documentCollection as $document) {
            $id = $document->getFieldValue('id');
            $entityIds[] = [
                'id'      => $id,
                'solr_id' => $this->createUniqueId($id)
            ];
        }
        
        return $entityIds;
    }

    /**
     * @param $id
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function createUniqueId($id)
    {
        return $id . '_category_' . $this->storeManager->getStore()->getId();
    }

    /**
     * @return array
     */
    public function getEntityIdFromMagento()
    {
        $categoryCollection = $this->categoryCollection->create();
        $categoryCollection->addAttributeToSelect('entity_id')
            ->setStoreId($this->storeManager->getStore()->getId())
            ->addIsActiveFilter();

        $entityIds = [];
        foreach ($categoryCollection as $category) {
            if ($category->getIsActive()) {
                $entityIds[] = $category->getEntityId();
            }
        }
        
        return $entityIds;
    }
}
