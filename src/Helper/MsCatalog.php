<?php

namespace G4NReact\MsCatalogMagento2\Helper;

use G4NReact\MsCatalogIndexer\Config;
use Magento\Framework\App\Helper\AbstractHelper;

/**
 * Class MsCatalog
 * @package G4NReact\MsCatalogMagento2\Helper
 */
class MsCatalog extends AbstractHelper
{
    /**
     * @return Config
     */
    public function getConfiguration(): Config
    {
        $engine = (int)$this->getConfigByPath('ms_catalog_indexer/engine_settings/engine');
        $host = (string)$this->getConfigByPath('ms_catalog_indexer/engine_settings/host');
        $port = (int)$this->getConfigByPath('ms_catalog_indexer/engine_settings/port');
        $path = '/';
        $collection = (string)$this->getConfigByPath('ms_catalog_indexer/engine_settings/collection') ?: '';
        $core = (string)$this->getConfigByPath('ms_catalog_indexer/engine_settings/core');

        $pageSize = (int)$this->getConfigByPath('ms_catalog_indexer/indexer_settings/pagesize');
        $deleteIndexBeforeReindex = !!$this->getConfigByPath('ms_catalog_indexer/indexer_settings/delete_before_reindex');

        return new Config($engine, $host, $port, $path, $collection, $core, $pageSize, $deleteIndexBeforeReindex);
    }

    /**
     * @param string $configPath
     * @return mixed
     */
    public function getConfigByPath($configPath)
    {
        return $this->scopeConfig->getValue(
            $configPath,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
