<?php

namespace G4NReact\MsCatalogMagento2\Model\Indexer;

use G4NReact\MsCatalog\PullerInterface;
use G4NReact\MsCatalogMagento2\Helper\Config as ConfigHelper;
use G4NReact\MsCatalogMagento2\Model\Puller\ProductPuller;
use Magento\Framework\App\State as AppState;
use Magento\Framework\Event\Manager as EventManager;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class ProductIndexer
 * @package G4NReact\MsCatalogMagento2\Model\Indexer
 */
class ProductIndexer extends AbstractIndexer
{
    /**
     * @var ProductPuller
     */
    protected $productPuller;

    /**
     * @var EventManager
     */
    protected $eventManager;

    /**
     * ProductIndexer constructor
     *
     * @param ProductPuller $productPuller
     * @param EventManager $eventManager
     * @param StoreManagerInterface $storeManager
     * @param Emulation $emulation
     * @param AppState $appState
     * @param ConfigHelper $configHelper
     */
    public function __construct(
        ProductPuller $productPuller,
        EventManager $eventManager,
        StoreManagerInterface $storeManager,
        Emulation $emulation,
        AppState $appState,
        ConfigHelper $configHelper
    ) {
        $this->productPuller = $productPuller;
        $this->eventManager = $eventManager;
        parent::__construct($storeManager, $emulation, $appState, $configHelper);
    }

    /**
     * @return null|PullerInterface
     */
    public function getPuller()
    {
        return $this->productPuller;
    }

    /**
     * After reindex
     */
    public function afterReindex()
    {
        $this->eventManager->dispatch('product_indexer_reindex_after', ['to_delete_ids' => $this->getPuller()->getToDeleteIds(), 'to_clean_cache_ids' => $this->getPuller()->getToCleanCacheIds()]);
    }
}
