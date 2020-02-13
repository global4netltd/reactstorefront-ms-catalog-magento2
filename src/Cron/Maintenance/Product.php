<?php

namespace G4NReact\MsCatalogMagento2\Cron\Maintenance;

use G4NReact\MsCatalog\Client\ClientFactory;
use G4NReact\MsCatalogMagento2\Helper\Config as ConfigHelper;
use G4NReact\MsCatalogMagento2\Helper\Query as QueryHelper;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
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
class Product
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
    protected $productCollection;

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
     * @param CollectionFactory $productCollection
     * @param ConfigHelper $configHelper
     * @param QueryHelper $queryHelper
     * @param LoggerInterface $logger
     */
    public function __construct(
        AppState $appState,
        StoreManagerInterface $storeManager,
        Emulation $emulation,
        CollectionFactory $productCollection,
        ConfigHelper $configHelper,
        QueryHelper $queryHelper,
        LoggerInterface $logger
    ) {
        $this->appState = $appState;
        $this->storeManager = $storeManager;
        $this->emulation = $emulation;
        $this->productCollection = $productCollection;
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
            $this->appState->emulateAreaCode('frontend', function () use ($store) {
                $this->removeProductNotExistInMagento($store);
            });
        }
    }

    /**
     * @param StoreInterface $store
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function removeProductNotExistInMagento(StoreInterface $store)
    {
        // start store emulation
        $this->emulation->startEnvironmentEmulation($store->getId(), Area::AREA_FRONTEND, true);

        $searchEngineConfig = $this->configHelper->getConfiguration();
        if ($searchEngineConfig->getRemoveNotExisting()) {
            $solrProducts = $this->getEntityIdFromSolr();
            $magentoProducts = $this->getEntityIdFromMagento();

            if (empty($solrProducts) || empty($magentoProducts)) {
                return;
            }

            $toRemoveIds = [];
            foreach ($solrProducts as $product) {
                if (!in_array($product['id'], $magentoProducts)) {
                    $toRemoveIds[] = $product['solr_id'];
                }
            }

            if (!empty($toRemoveIds)) {
                try {
                    $searchEngineClient = ClientFactory::create($searchEngineConfig);
                    $searchEngineClient->deleteByIds($toRemoveIds);
                } catch (\Exception $e) {
                    $this->logger->error('Problem with remove from solr', ['remove_ids' => $toRemoveIds, 'message' => $e->getMessage(), 'exception' => $e]);
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
            [$this->queryHelper->getFieldByAttributeCode('object_type', 'product')]
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
        return $id . '_product_' . $this->storeManager->getStore()->getId();
    }

    /**
     * @return array
     */
    public function getEntityIdFromMagento()
    {
        $productCollection = $this->productCollection->create();
        $productCollection->addAttributeToSelect('entity_id')
            ->addStoreFilter();

        $entityIds = [];
        foreach ($productCollection as $product) {
            $entityIds[] = $product->getEntityId();
        }
        
        return $entityIds;
    }
}
