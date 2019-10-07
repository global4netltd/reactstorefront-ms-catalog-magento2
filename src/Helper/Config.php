<?php

namespace G4NReact\MsCatalogMagento2\Helper;

use Exception;
use G4NReact\MsCatalog\Config as MsCatalogConfig;
use G4NReact\MsCatalog\Helper as ConfigHelper;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class MsCatalog
 * @package G4NReact\MsCatalogMagento2\Helper
 */
class Config extends AbstractHelper
{
    /**
     * @var string Base engine options path in configuration
     */
    const BASE_ENGINE_CONFIG_PATH = 'ms_catalog_indexer/engine_settings/';

    /** @var string category attributes use in react storefront */
    const CATEGORY_ATTRIBUTES_USE_IN_REACT_STOREFRONT_XML = 'ms_catalog_indexer/indexer_settings/category_attributes_use_in_react_storefront';

    /** @var string category attributes force indexing in react storefron */
    const CATEGORY_ATTRIBUTES_FORCE_INDEXING_IN_REACT_STOREFRONT_XML = 'ms_catalog_indexer/indexer_settings/category_attributes_force_indexing_in_react_storefront';

    /** @var string product attributes base stats */
    const PRODUCT_ATTRIBUTES_BASE_STATS_XML = 'ms_catalog_indexer/indexer_settings/product_attributes_base_stats';

    /** @var string product attributes base facets */
    const PRODUCT_ATTRIBUTES_BASE_FACETS_XML = 'ms_catalog_indexer/indexer_settings/product_attributes_base_facets';

    /** @var string show out of stock */
    const SHOW_OUT_OF_STOCK_XML = 'cataloginventory/options/show_out_of_stock';

    /** @var string remove missing objects */
    const REMOVE_MISSING_OBJECTS = 'ms_catalog_indexer/indexer_settings/remove_missing_objects';

    /**
     * @var MsCatalogConfig[]
     */
    public $config = [];

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * MsCatalog constructor
     *
     * @param StoreManagerInterface $storeManager
     * @param Context $context
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        Context $context
    ) {
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * @return StoreInterface[]
     */
    public function getAllStores()
    {
        return $this->storeManager->getStores();
    }

    /**
     * @return StoreInterface
     * @throws NoSuchEntityException
     */
    public function getStore(): StoreInterface
    {
        return $this->storeManager->getStore();
    }

    /**
     * @return WebsiteInterface
     * @throws LocalizedException
     */
    public function getWebiste(): WebsiteInterface
    {
        return $this->storeManager->getWebsite();
    }

    /**
     * @return MsCatalogConfig|null
     * @throws NoSuchEntityException
     * @throws Exception
     */
    public function getConfiguration(): ?MsCatalogConfig
    {
        if (!isset($this->config[$this->getStore()->getId()])) {
            $engine = (int)$this->getConfigByPath('ms_catalog_indexer/engine_settings/engine');

            $isDebugEnabled = (bool)$this->getConfigByPath('ms_catalog_indexer/general_settings/logging');
            $pullerPageSize = (int)$this->getConfigByPath('ms_catalog_indexer/indexer_settings/puller_pagesize');
            $pusherPageSize = (int)$this->getConfigByPath('ms_catalog_indexer/indexer_settings/pusher_pagesize');
            $pullerTimeout = (int)$this->getConfigByPath('ms_catalog_indexer/indexer_settings/puller_timeout');
            $pusherTimeout = (int)$this->getConfigByPath('ms_catalog_indexer/indexer_settings/pusher_timeout');
            $deleteIndexBeforeReindex = !!$this->getConfigByPath('ms_catalog_indexer/indexer_settings/pusher_delete_index');
            $removeMissingObjects = !!$this->getConfigByPath(self::REMOVE_MISSING_OBJECTS);

            $engineConnectionParams = [];
            if (!isset(ConfigHelper::$engines[$engine])) {
                throw new Exception(sprintf('Unknown engine: %s.', $engine));
            }

            $configParams = ConfigHelper::$engines[$engine];
            $engineCode = ConfigHelper::$engines[$engine]['code'];
            foreach (ConfigHelper::$engines[$engine]['connection'] as $connectionParamName) {
                $engineConnectionParams[$connectionParamName] = $this->getConfigByPath(
                    self::BASE_ENGINE_CONFIG_PATH . $engineCode . '_' . $connectionParamName
                );
            }
            $configParams['connection'] = $engineConnectionParams;
            $configParams['puller_page_size'] = $pullerPageSize;
            $configParams['pusher_page_size'] = $pusherPageSize;
            $configParams['puller_timeout'] = $pullerTimeout;
            $configParams['pusher_timeout'] = $pusherTimeout;
            $configParams['pusher_delete_index'] = $deleteIndexBeforeReindex;
            $configParams['pusher_remove_missing_objects'] = $removeMissingObjects;
            $configParams['debug_enabled'] = $isDebugEnabled;

            $this->config[$this->getStore()->getId()] = new MsCatalogConfig($configParams);
        }

        return $this->config[$this->getStore()->getId()];
    }

    /**
     * @return bool
     * @throws NoSuchEntityException
     */
    public function isIndexerEnabled(): bool
    {
        return $this->getConfigByPath('ms_catalog_indexer/general_settings/enabled') ? true : false;
    }

    /**
     * @param string $configPath
     *
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getConfigByPath($configPath)
    {
        return $this->scopeConfig->getValue(
            $configPath,
            ScopeInterface::SCOPE_STORE,
            $this->getStore()->getId()
        );
    }

    /**
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getProductAttributesBaseFacets()
    {
        return $this->prepareDataFromMultiSelectConfig(
            $this->scopeConfig->getValue(self::PRODUCT_ATTRIBUTES_BASE_FACETS_XML, ScopeInterface::SCOPE_STORE, $this->getStore()->getId())
        );
    }

    /**
     * @return array
     * @throws NoSuchEntityException
     */
    public function getProductAttributesBaseStats()
    {
        return $this->prepareDataFromMultiSelectConfig(
            $this->scopeConfig->getValue(self::PRODUCT_ATTRIBUTES_BASE_STATS_XML, ScopeInterface::SCOPE_STORE, $this->storeManager->getStore()->getId())
        );
    }

    /**
     * @return array
     * @throws NoSuchEntityException
     */
    public function getCategoryAttributesUseInReact()
    {
        return $this->prepareDataFromMultiSelectConfig(
            $this->scopeConfig->getValue(self::CATEGORY_ATTRIBUTES_USE_IN_REACT_STOREFRONT_XML, ScopeInterface::SCOPE_STORE, $this->getStore()->getId())
        );
    }

    /**
     * @return array
     * @throws NoSuchEntityException
     */
    public function getCategoryAttributesForceIndexingInReact()
    {
        return $this->prepareDataFromMultiSelectConfig(
            $this->scopeConfig->getValue(self::CATEGORY_ATTRIBUTES_FORCE_INDEXING_IN_REACT_STOREFRONT_XML, ScopeInterface::SCOPE_STORE, $this->getStore()->getId())
        );
    }

    /**
     * @param $data
     *
     * @return array
     */
    public function prepareDataFromMultiSelectConfig($data)
    {
        if ($data) {
            return explode(',', $data);
        }

        return [];
    }

    /**
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getShowOutOfStockProducts()
    {
        return $this->scopeConfig->getValue(
            self::SHOW_OUT_OF_STOCK_XML,
            ScopeInterface::SCOPE_STORE,
            $this->getStore()->getId()
        );
    }

    /**
     * @return bool
     * @throws NoSuchEntityException
     */
    public function getShouldRemoveMissingObjects()
    {
        return (bool)$this->getConfigByPath(self::REMOVE_MISSING_OBJECTS);
    }
}
