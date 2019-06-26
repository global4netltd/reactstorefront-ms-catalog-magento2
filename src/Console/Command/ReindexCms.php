<?php

namespace G4NReact\MsCatalogMagento2\Console\Command;

use G4NReact\MsCatalogMagento2\Helper\MsCatalog as MsCatalogHelper;
use G4NReact\MsCatalogMagento2\Model\Puller\CmsPuller;
use Magento\Framework\App\State as AppState;
use Magento\Store\Model\App\Emulation;

/**
 * Class ReindexCms
 * @package G4NReact\MsCatalogMagento2\Console\Command
 */
class ReindexCms extends AbstractReindex
{
    /**
     * @var CmsPuller
     */
    protected $cmsPuller;

    /**
     * ReindexCms constructor
     *
     * @param CmsPuller $cmsPuller
     * @param MsCatalogHelper $msCatalogHelper
     * @param Emulation $emulation
     * @param AppState $appState
     * @param string|null $name
     */
    public function __construct(
        CmsPuller $cmsPuller,
        MsCatalogHelper $msCatalogHelper,
        Emulation $emulation,
        AppState $appState,
        ?string $name = null
    ) {
        $this->cmsPuller = $cmsPuller;
        parent::__construct($msCatalogHelper, $emulation, $appState, $name);
    }

    /**
     * @return string
     */
    public function getCommandName(): string
    {
        return 'g4nreact:reindex:cms';
    }

    /**
     * @return string
     */
    public function getCommandDescription(): string
    {
        return 'Reindexes CMS';
    }

    /**
     * @return CmsPuller
     */
    public function getPuller()
    {
        return $this->cmsPuller;
    }
}
