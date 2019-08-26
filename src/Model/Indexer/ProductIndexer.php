<?php

namespace G4NReact\MsCatalogMagento2\Model\Indexer;

use G4NReact\MsCatalog\PullerInterface;
use G4NReact\MsCatalogMagento2\Helper\Config as ConfigHelper;
use G4NReact\MsCatalogMagento2\Model\Puller\ProductPuller;
use Magento\Framework\App\State as AppState;
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
     * ProductIndexer constructor
     *
     * @param ProductPuller $productPuller
     * @param StoreManagerInterface $storeManager
     * @param Emulation $emulation
     * @param AppState $appState
     * @param ConfigHelper $configHelper
     */
    public function __construct(
        ProductPuller $productPuller,
        StoreManagerInterface $storeManager,
        Emulation $emulation,
        AppState $appState,
        ConfigHelper $configHelper
    ) {
        $this->productPuller = $productPuller;
        parent::__construct($storeManager, $emulation, $appState, $configHelper);
    }

    /**
     * @return null|PullerInterface
     */
    public function getPuller()
    {
        return $this->productPuller;
    }
}
