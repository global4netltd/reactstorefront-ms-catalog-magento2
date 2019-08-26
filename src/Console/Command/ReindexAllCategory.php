<?php

namespace G4NReact\MsCatalogMagento2\Console\Command;

use G4NReact\MsCatalogMagento2\Model\Indexer\AbstractIndexer;
use G4NReact\MsCatalogMagento2\Model\Indexer\CategoryIndexer;
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
     * @param CategoryIndexer $categoryIndexer
     * @param string|null $name
     */
    public function __construct(
        CategoryIndexer $categoryIndexer,
        string $name = null
    ) {
        parent::__construct($name);
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
