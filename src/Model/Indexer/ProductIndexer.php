<?php

namespace G4NReact\MsCatalogMagento2\Model\Indexer;

use G4NReact\MsCatalog\PullerInterface;
use G4NReact\MsCatalogMagento2\Helper\Config as ConfigHelper;
use G4NReact\MsCatalogMagento2\Model\Puller\ProductPuller;
use Magento\Catalog\Model\Indexer\Product\Price as PriceIndexer;
use Magento\Framework\App\State as AppState;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class ProductIndexer
 * @package G4NReact\MsCatalogMagento2\Model\Indexer
 */
class ProductIndexer extends AbstractIndexer
{
    /**
     * @var PriceIndexer
     */
    protected $priceIndexer;

    /**
     * @var ProductPuller
     */
    protected $productPuller;

    /**
     * ProductIndexer constructor
     *
     * @param PriceIndexer $priceIndexer
     * @param ProductPuller $productPuller
     * @param StoreManagerInterface $storeManager
     * @param Emulation $emulation
     * @param AppState $appState
     * @param ConfigHelper $configHelper
     */
    public function __construct(
        PriceIndexer $priceIndexer,
        ProductPuller $productPuller,
        StoreManagerInterface $storeManager,
        Emulation $emulation,
        AppState $appState,
        ConfigHelper $configHelper
    ) {
        $this->priceIndexer = $priceIndexer;
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

    /**
     * @inheritdoc
     */
    public function beforeReindex(StoreInterface $store, array $ids = [])
    {
        // start store emulation
        $this->emulation->startEnvironmentEmulation($store->getId(), 'frontend', true);
        if ($this->configHelper->isIndexerEnabled()) {

            if ($ids) {
                if ($ids = $this->prepareIds($ids)) {
                    $this->priceIndexer->executeList($ids);
                }
            } else {
                $this->priceIndexer->executeFull();
            }
        }

        // end store emulation
        $this->emulation->stopEnvironmentEmulation();
    }
}
