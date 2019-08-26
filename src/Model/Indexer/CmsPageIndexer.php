<?php

namespace G4NReact\MsCatalogMagento2\Model\Indexer;

use G4NReact\MsCatalog\PullerInterface;
use G4NReact\MsCatalogMagento2\Helper\Config as ConfigHelper;
use G4NReact\MsCatalogMagento2\Model\Puller\CmsPagePuller;
use Magento\Framework\App\State as AppState;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class CmsPageIndexer
 * @package G4NReact\MsCatalogMagento2\Model\Indexer
 */
class CmsPageIndexer extends AbstractIndexer
{
    /**
     * @var CmsPagePuller
     */
    protected $cmsPuller;

    /**
     * CmsPageIndexer constructor
     *
     * @param CmsPuller $cmsPuller
     * @param StoreManagerInterface $storeManager
     * @param Emulation $emulation
     * @param AppState $appState
     * @param ConfigHelper $configHelper
     */
    public function __construct(
        CmsPagePuller $cmsPuller,
        StoreManagerInterface $storeManager,
        Emulation $emulation,
        AppState $appState,
        ConfigHelper $configHelper
    ) {
        $this->cmsPuller = $cmsPuller;
        parent::__construct($storeManager, $emulation, $appState, $configHelper);
    }

    /**
     * @return null|PullerInterface
     */
    public function getPuller()
    {
        return $this->cmsPuller;
    }
}
