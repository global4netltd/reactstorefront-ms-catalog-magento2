<?php

namespace G4NReact\MsCatalogMagento2\Cron;

use G4NReact\MsCatalogMagento2\Model\IndexerInterface;

/**
 * Class ReindexAll
 * @package G4NReact\MsCatalogMagento2\Cron
 */
class ReindexAll
{
    /**
     * @var IndexerInterface
     */
    protected $indexer;

    /**
     * ReindexAll constructor
     * @param IndexerInterface $indexer
     */
//    public function __construct(IndexerInterface $indexer) {
//        $this->indexer = $indexer;
//    }

    /**
     * @return void
     */
    public function execute() {
//        $this->indexer->reindexAll();
    }
}
