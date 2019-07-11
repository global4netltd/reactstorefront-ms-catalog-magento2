<?php

namespace G4NReact\MsCatalogMagento2\Console\Command;

use G4NReact\MsCatalogMagento2\Helper\Config as ConfigHelper;
use G4NReact\MsCatalogMagento2\Model\Puller\CategoryPuller;
use Magento\Framework\App\State as AppState;
use Magento\Store\Model\App\Emulation;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class ReindexCategory
 * @package G4NReact\MsCatalogMagento2\Console\Command
 */
class ReindexAllCategory extends AbstractReindex
{
    /**
     * @var CategoryPuller
     */
    protected $categoryPuller;

    /**
     * ReindexCategory constructor
     *
     * @param CategoryPuller $categoryPuller
     * @param ConfigHelper $magento2ConfigHelper
     * @param Emulation $emulation
     * @param AppState $appState
     * @param string|null $name
     */
    public function __construct(
        CategoryPuller $categoryPuller,
        ConfigHelper $magento2ConfigHelper,
        Emulation $emulation,
        AppState $appState,
        ?string $name = null
    ) {
        $this->categoryPuller = $categoryPuller;
        parent::__construct($magento2ConfigHelper, $emulation, $appState, $name);
    }

    /**
     * @return string
     */
    public function getCommandName(): string
    {
        return 'g4nreact:reindexall:category';
    }

    /**
     * @return string
     */
    public function getCommandDescription(): string
    {
        return 'Reindexes all categories';
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
     * @return CategoryPuller
     */
    public function getPuller(): CategoryPuller
    {
        return $this->categoryPuller;
    }
}
