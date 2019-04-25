<?php

namespace G4NReact\MsCatalogMagento2\Model;

use G4NReact\MsCatalogIndexer\Config;
use G4NReact\MsCatalog\Document;

/**
 * Class AbstractPuller
 * @package G4NReact\MsCatalogMagento2\Model
 */
abstract class AbstractPuller implements \Iterator
{
    const PAGE_SIZE_DEFAULT = 10;

    public $totalSize = 1000;
    public $curPage;
    public $pageSize = 10;

    public $position = 0;
    public $totalPosition = 0;

    public $pageArray;
    public $ids;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Puller constructor
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->position = 0;
        $this->totalSize = $this->getCollection()->getSize();
        $this->curPage = 0;

        $this->pageSize = $this->getConfigByPath('ms_catalog_indexer/indexer_settings/pagesize') ?: self::PAGE_SIZE_DEFAULT;
    }

    /**
     * @return int
     */
    public function getPageSize(): int
    {
        return $this->pageSize;
    }

    /**
     * @param int $pageSize
     * @return Puller
     */
    public function setPageSize(int $pageSize): Puller
    {
        $this->pageSize = $pageSize;

        return $this;
    }
    
    public function getCollection()
    {
    }

    /**
     * @return mixed
     */
    public function getIds()
    {
        return $this->ids;
    }

    /**
     * @param mixed $ids
     * @return Puller
     */
    public function setIds($ids)
    {
        $this->ids = $ids;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function rewind()
    {
        $this->position = 0;
        $this->curPage = 0;
    }

    /**
     * @return Document
     */
    public function current(): Document
    {
        $document = new Document();

        return $document;
    }

    /**
     * @inheritdoc
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * @inheritdoc
     */
    public function next()
    {
        ++$this->position;
        ++$this->totalPosition;
    }

    /**
     * @inheritdoc
     */
    public function valid()
    {
        if ($this->totalPosition == $this->totalSize) {
            return false;
        }

        if ($this->position == $this->pageSize) {
            $this->position = 0;
        }

        if ($this->position == 0) {
            $collection = $this->getCollection();
            $this->curPage++;

            $this->pageArray = array();
            foreach ($collection as $item) {
                $this->pageArray[] = $item;
            }
        }

        return isset($this->pageArray[$this->position]);
    }

    /**
     * @return Config
     */
    public function getConfiguration(): Config
    {
        $engine = (int)$this->getConfigByPath('ms_catalog_indexer/engine_settings/engine');
        $host = (string)$this->getConfigByPath('ms_catalog_indexer/engine_settings/host');
        $port = (int)$this->getConfigByPath('ms_catalog_indexer/engine_settings/port');
        $path = (string)$this->getConfigByPath('ms_catalog_indexer/engine_settings/path');
        $core = (string)$this->getConfigByPath('ms_catalog_indexer/engine_settings/core');

        $pageSize = (int)$this->getConfigByPath('ms_catalog_indexer/indexer_settings/pagesize');
        $deleteIndexBeforeReindex = !!$this->getConfigByPath('ms_catalog_indexer/indexer_settings/delete_before_reindex');

        return new Config($engine, $host, $port, $path, $core, $pageSize, $deleteIndexBeforeReindex);
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
