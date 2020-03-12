<?php

namespace G4NReact\MsCatalogMagento2\Console\Command;

use G4NReact\MsCatalogMagento2\Model\Indexer\AbstractIndexer;
use G4NReact\MsCatalogMagento2\Model\Indexer\CategoryIndexer;
use Magento\Store\Model\StoreManagerInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class ReindexCategory
 * @package G4NReact\MsCatalogMagento2\Console\Command
 */
class ReindexAllCategory extends AbstractReindex
{
    /**
     * @var CategoryIndexer
     */
    protected $categoryIndexer;

    /**
     * ReindexAllCategory constructor
     *
     * @param StoreManagerInterface $storeManager
     * @param CategoryIndexer $categoryIndexer
     * @param string|null $name
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        CategoryIndexer $categoryIndexer,
        string $name = null
    ) {
        parent::__construct($storeManager, $name);
        $this->categoryIndexer = $categoryIndexer;
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
            new InputOption(
                self::INPUT_OPTION_STORE_ID,
                null,
                InputOption::VALUE_OPTIONAL,
                self::REQUIRED_OPTION_INFO,
                false
            )
        ];
    }

    /**
     * @return AbstractIndexer
     */
    public function getIndexer()
    {
        return $this->categoryIndexer;
    }
}
