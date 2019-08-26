<?php

namespace G4NReact\MsCatalogMagento2\Console\Command;

use G4NReact\MsCatalogMagento2\Model\Indexer\AbstractIndexer;
use G4NReact\MsCatalogMagento2\Model\Indexer\ProductIndexer;

/**
 * Class ReindexProduct
 * @package G4NReact\MsCatalogMagento2\Console\Command
 */
class ReindexProduct extends AbstractReindex
{
    /**
     * @var ProductIndexer
     */
    protected $productIndexer;

    /**
     * ReindexAllProduct constructor
     *
     * @param ProductIndexer $productIndexer
     * @param string|null $name
     */
    public function __construct(
        ProductIndexer $productIndexer,
        string $name = null
    ) {
        $this->productIndexer = $productIndexer;
        parent::__construct($name);
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
     * @return AbstractIndexer
     */
    public function getIndexer()
    {
        return $this->productIndexer;
    }
}
