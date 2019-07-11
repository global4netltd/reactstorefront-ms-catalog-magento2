<?php

namespace G4NReact\MsCatalogMagento2\Console\Command;

use G4NReact\MsCatalogMagento2\Helper\Config as ConfigHelper;
use G4NReact\MsCatalogMagento2\Model\Puller\ProductPuller;
use Magento\Framework\App\State as AppState;
use Magento\Store\Model\App\Emulation;

/**
 * Class ReindexProduct
 * @package G4NReact\MsCatalogMagento2\Console\Command
 */
class ReindexProduct extends AbstractReindex
{
    /**
     * @var ProductPuller
     */
    protected $productPuller;

    /**
     * ReindexProduct constructor
     *
     * @param ProductPuller $productPuller
     * @param ConfigHelper $magento2ConfigHelper
     * @param Emulation $emulation
     * @param AppState $appState
     * @param string|null $name
     */
    public function __construct(
        ProductPuller $productPuller,
        ConfigHelper $magento2ConfigHelper,
        Emulation $emulation,
        AppState $appState,
        ?string $name = null
    ) {
        $this->productPuller = $productPuller;
        parent::__construct($magento2ConfigHelper, $emulation, $appState, $name);
    }

    /**
     * @return string
     */
    public function getCommandName(): string
    {
        return 'g4nreact:reindex:product';
    }

    /**
     * @return string
     */
    public function getCommandDescription(): string
    {
        return 'Reindexes products';
    }

    /**
     * @return ProductPuller
     */
    public function getPuller()
    {
        return $this->productPuller;
    }
}
