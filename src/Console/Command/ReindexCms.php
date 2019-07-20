<?php

namespace G4NReact\MsCatalogMagento2\Console\Command;

use G4NReact\MsCatalogMagento2\Model\Indexer\AbstractIndexer;
use G4NReact\MsCatalogMagento2\Model\Indexer\CmsIndexer;

/**
 * Class ReindexCms
 * @package G4NReact\MsCatalogMagento2\Console\Command
 */
class ReindexCms extends AbstractReindex
{
    /**
     * @var CmsIndexer
     */
    protected $cmsIndexer;

    /**
     * ReindexCms constructor
     *
     * @param CmsIndexer $cmsIndexer
     * @param string|null $name
     */
    public function __construct(
        CmsIndexer $cmsIndexer,
        string $name = null
    ) {
        $this->cmsIndexer = $cmsIndexer;
        parent::__construct($name);
    }

    /**
     * @return string
     */
    public function getCommandName(): string
    {
        return 'g4nreact:reindex:cms';
    }

    /**
     * @return string
     */
    public function getCommandDescription(): string
    {
        return 'Reindexes CMS';
    }

    /**
     * @return AbstractIndexer
     */
    public function getIndexer()
    {
        return $this->cmsIndexer;
    }
}
