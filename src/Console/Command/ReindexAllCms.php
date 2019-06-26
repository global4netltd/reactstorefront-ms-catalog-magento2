<?php

namespace G4NReact\MsCatalogMagento2\Console\Command;

use G4NReact\MsCatalogMagento2\Helper\MsCatalog as MsCatalogHelper;
use G4NReact\MsCatalogMagento2\Model\Puller\CmsPuller;
use Magento\Framework\App\State as AppState;
use Magento\Store\Model\App\Emulation;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class ReindexAllCms
 * @package G4NReact\MsCatalogMagento2\Console\Command
 */
class ReindexAllCms extends AbstractReindex
{
    /**
     * @var CmsPuller
     */
    protected $cmsPuller;

    /**
     * ReindexAllCms constructor
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
        return 'g4nreact:reindexall:cms';
    }

    /**
     * @return string
     */
    public function getCommandDescription(): string
    {
        return 'Reindexes all cms';
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
     * @return CmsPuller
     */
    public function getPuller(): CmsPuller
    {
        return $this->cmsPuller;
    }
}
