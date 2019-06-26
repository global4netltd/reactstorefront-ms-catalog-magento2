<?php

namespace G4NReact\MsCatalogMagento2\Console\Command;

use G4NReact\MsCatalogMagento2\Helper\MsCatalog as MsCatalogHelper;
use G4NReact\MsCatalogMagento2\Model\Puller\ProductPuller;
use Magento\Framework\App\State as AppState;
use Magento\Store\Model\App\Emulation;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class ReindexAllProduct
 * @package G4NReact\MsCatalogMagento2\Console\Command
 */
class ReindexAllProduct extends AbstractReindex
{
    /**
     * @var ProductPuller
     */
    protected $productPuller;

    /**
     * ReindexAllProduct constructor
     *
     * @param ProductPuller $productPuller
     * @param MsCatalogHelper $msCatalogHelper
     * @param Emulation $emulation
     * @param AppState $appState
     * @param string|null $name
     */
    public function __construct(
        ProductPuller $productPuller,
        MsCatalogHelper $msCatalogHelper,
        Emulation $emulation,
        AppState $appState,
        ?string $name = null
    ) {
        $this->productPuller = $productPuller;
        parent::__construct($msCatalogHelper, $emulation, $appState, $name);
    }

    /**
     * @return string
     */
    public function getCommandName(): string
    {
        return 'g4nreact:reindexall:product';
    }

    /**
     * @return string
     */
    public function getCommandDescription(): string
    {
        return 'Reindexes all products';
    }

    /**
     * @return array
     */
    public function getInputOptions(): array
    {
        return [
            new InputOption(
                self::INPUT_OPTION_IDS,
                null,
                InputOption::VALUE_OPTIONAL,
                self::REQUIRED_OPTION_INFO,
                []
            ),
            new InputOption(
                self::INPUT_OPTION_ALL,
                null,
                InputOption::VALUE_OPTIONAL,
                self::REQUIRED_OPTION_INFO,
                true
            ),
        ];
    }

    /**
     * @return ProductPuller
     */
    public function getPuller(): ProductPuller
    {
        return $this->productPuller;
    }
}
