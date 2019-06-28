<?php

namespace G4NReact\MsCatalogMagento2\Helper;

use G4NReact\MsCatalog\Helper;
use G4NReact\MsCatalog\Config;
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
class MsCatalog extends AbstractHelper
{
    /**
     * @var string Base engine options path in configuration
     */
    const BASE_ENGINE_CONFIG_PATH = 'ms_catalog_indexer/engine_settings/';

    /**
     * @var array
     */
    public static $multiValuedAttributeFrontendInput = [
        'select',
        'multiselect',
    ];

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
     * @return array
     * @throws NoSuchEntityException
     */
    public function getSearchEngineConfiguration(): array
    {
        $engine = (int)$this->getConfigByPath('ms_catalog_indexer/engine_settings/engine');

        $pullerPageSize = (int)$this->getConfigByPath('ms_catalog_indexer/indexer_settings/puller_pagesize');
        $pusherPageSize = (int)$this->getConfigByPath('ms_catalog_indexer/indexer_settings/pusher_pagesize');
        $deleteIndexBeforeReindex = !!$this->getConfigByPath('ms_catalog_indexer/indexer_settings/pusher_delete_index');

        $engineConnectionParams = [];
        if (!isset(Helper::$engines[$engine])) {
            // log error, throw exception etc.
            return [];
        }

        $searchEngineParams = Helper::$engines[$engine];
        $engineCode = Helper::$engines[$engine]['code'];
        foreach (Helper::$engines[$engine]['connection'] as $connectionParamName) {
            $engineConnectionParams[$connectionParamName] = $this->getConfigByPath(
                self::BASE_ENGINE_CONFIG_PATH . $engineCode . '_' . $connectionParamName
            );
        }
        $searchEngineParams['connection'] = $engineConnectionParams;
        $searchEngineParams['puller_page_size'] = $pullerPageSize;
        $searchEngineParams['pusher_page_size'] = $pusherPageSize;
        $searchEngineParams['pusher_delete_index'] = $deleteIndexBeforeReindex;

        return $searchEngineParams;
    }

    /**
     * @return array
     * @throws NoSuchEntityException
     */
    public function getEcommerceEngineConfiguration(): array
    {
        $pullerPageSize = (int)$this->getConfigByPath('ms_catalog_indexer/indexer_settings/puller_pagesize');
        $pusherPageSize = (int)$this->getConfigByPath('ms_catalog_indexer/indexer_settings/pusher_pagesize');
        $deleteIndexBeforeReindex = !!$this->getConfigByPath('ms_catalog_indexer/indexer_settings/pusher_delete_index');

        $ecommerceEngineConfiguration = [
            'namespace' => 'MsCatalogMagento2\Model',
            'puller_page_size' => $pullerPageSize,
            'pusher_page_size' => $pusherPageSize,
            'pusher_delete_index' => $deleteIndexBeforeReindex,
        ];

        return $ecommerceEngineConfiguration;
    }

    /**
     * @param array $pullerParams
     * @param array $pusherParams
     * @return Config|null
     */
    public function getConfiguration($pullerParams, $pusherParams): ?Config
    {
        return new Config($pullerParams, $pusherParams);
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
