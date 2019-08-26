<?php

namespace G4NReact\MsCatalogMagento2\Model\Indexer;

use G4NReact\MsCatalog\PullerInterface;
use G4NReact\MsCatalogMagento2\Helper\Config as ConfigHelper;
use G4NReact\MsCatalogMagento2\Model\Puller\CategoryPuller;
use Magento\Framework\App\State as AppState;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class CategoryIndexer
 * @package G4NReact\MsCatalogMagento2\Model\Indexer
 */
class CategoryIndexer extends AbstractIndexer
{
    /**
     * @var CategoryPuller
     */
    protected $categoryPuller;

    /**
     * CategoryIndexer constructor
     *
     * @param CategoryPuller $categoryPuller
     * @param StoreManagerInterface $storeManager
     * @param Emulation $emulation
     * @param AppState $appState
     * @param ConfigHelper $configHelper
     */
    public function __construct(
        CategoryPuller $categoryPuller,
        StoreManagerInterface $storeManager,
        Emulation $emulation,
        AppState $appState,
        ConfigHelper $configHelper
    ) {
        $this->categoryPuller = $categoryPuller;
        parent::__construct($storeManager, $emulation, $appState, $configHelper);
    }

    /**
     * @return null|PullerInterface
     */
    public function getPuller()
    {
        return $this->categoryPuller;
    }
}
