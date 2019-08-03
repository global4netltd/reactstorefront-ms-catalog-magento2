<?php

namespace G4NReact\MsCatalogMagento2\Model\Indexer;

use G4NReact\MsCatalogMagento2\Helper\Config as ConfigHelper;
use G4NReact\MsCatalogMagento2\Model\Puller\SearchTermsPuller;
use G4NReact\MsCatalog\PullerInterface;
use Magento\Framework\App\State as AppState;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class SearchTermsIndexer
 * @package G4NReact\MsCatalogMagento2\Model\Indexer
 */
class SearchTermsIndexer extends AbstractIndexer
{

    /**
     * @var SearchTermsPuller
     */
    protected $searchTermsPuller;

    /**
     * SearchTermsIndexer constructor.
     *
     * @param StoreManagerInterface $storeManager
     * @param Emulation $emulation
     * @param AppState $appState
     * @param ConfigHelper $configHelper
     * @param SearchTermsPuller $searchTermsPuller
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        Emulation $emulation,
        AppState $appState,
        ConfigHelper $configHelper,
        SearchTermsPuller $searchTermsPuller
    ) {
        $this->searchTermsPuller = $searchTermsPuller;
        parent::__construct($storeManager, $emulation, $appState, $configHelper);
    }

    /**
     * @return PullerInterface|null
     */
    public function getPuller()
    {
        return $this->searchTermsPuller;
    }
}
