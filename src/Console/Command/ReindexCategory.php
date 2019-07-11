<?php

namespace G4NReact\MsCatalogMagento2\Console\Command;

use G4NReact\MsCatalogMagento2\Helper\Config as ConfigHelper;
use G4NReact\MsCatalogMagento2\Model\Puller\CategoryPuller;
use Magento\Framework\App\State as AppState;
use Magento\Store\Model\App\Emulation;

/**
 * Class ReindexCategory
 * @package G4NReact\MsCatalogMagento2\Console\Command
 */
class ReindexCategory extends AbstractReindex
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
        return 'g4nreact:reindex:category';
    }

    /**
     * @return string
     */
    public function getCommandDescription(): string
    {
        return 'Reindexes categories';
    }

    /**
     * @return CategoryPuller
     */
    public function getPuller(): CategoryPuller
    {
        return $this->categoryPuller;
    }
}
