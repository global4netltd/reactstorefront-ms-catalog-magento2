<?php

namespace G4NReact\MsCatalogMagento2\Model\Indexer;

use G4NReact\MsCatalogMagento2\Helper\Config as ConfigHelper;
use G4NReact\MsCatalogMagento2\Model\Puller\CmsBlockPuller;
use Magento\Framework\App\State as AppState;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class CmsBlockIndexer
 * @package G4NReact\MsCatalogMagento2\Model\Indexer
 */
class CmsBlockIndexer extends AbstractIndexer
{
    /**
     * @var CmsBlockPuller
     */
    protected $cmsBlockPuller;

    /**
     * CmsBlockIndexer constructor.
     *
     * @param StoreManagerInterface $storeManager
     * @param Emulation $emulation
     * @param AppState $appState
     * @param ConfigHelper $configHelper
     * @param CmsBlockPuller $cmsBlockPuller
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        Emulation $emulation,
        AppState $appState,
        ConfigHelper $configHelper,
        CmsBlockPuller $cmsBlockPuller
    ) {
        $this->cmsBlockPuller = $cmsBlockPuller;
        parent::__construct($storeManager, $emulation, $appState, $configHelper);
    }

    /**
     * @return null|PullerInterface
     */
    public function getPuller()
    {
        return $this->cmsBlockPuller;
    }
}
