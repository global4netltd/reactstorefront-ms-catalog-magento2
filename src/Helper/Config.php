<?php

namespace G4NReact\MsCatalogMagento2\Helper;

use Exception;
use G4NReact\MsCatalog\Config as MsCatalogConfig;
use G4NReact\MsCatalog\Helper as ConfigHelper;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Api\Data\StoreInterface;
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
     * @return MsCatalogConfig|null
     * @throws NoSuchEntityException
     * @throws Exception
     */
    public function getConfiguration(): ?MsCatalogConfig
    {
        $engine = (int)$this->getConfigByPath('ms_catalog_indexer/engine_settings/engine');

        $isDebugEnabled = (bool)$this->getConfigByPath('ms_catalog_indexer/general_settings/logging');
        $pullerPageSize = (int)$this->getConfigByPath('ms_catalog_indexer/indexer_settings/puller_pagesize');
        $pusherPageSize = (int)$this->getConfigByPath('ms_catalog_indexer/indexer_settings/pusher_pagesize');
        $deleteIndexBeforeReindex = !!$this->getConfigByPath('ms_catalog_indexer/indexer_settings/pusher_delete_index');

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
        $configParams['pusher_delete_index'] = $deleteIndexBeforeReindex;
        $configParams['debug_enabled'] = $isDebugEnabled;

        return new MsCatalogConfig($configParams);
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
}
