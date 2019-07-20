<?php

namespace G4NReact\MsCatalogMagento2\Console\Command;

use G4NReact\MsCatalogMagento2\Model\Indexer\AbstractIndexer;
use G4NReact\MsCatalogMagento2\Model\Indexer\CategoryIndexer;

/**
 * Class ReindexCategory
 * @package G4NReact\MsCatalogMagento2\Console\Command
 */
class ReindexCategory extends AbstractReindex
{
    /**
     * @var CategoryIndexer
     */
    protected $categoryIndexer;

    /**
     * ReindexCategory constructor
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
     * @return AbstractIndexer
     */
    public function getIndexer()
    {
        return $this->categoryIndexer;
    }
}
