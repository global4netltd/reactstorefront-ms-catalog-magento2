<?php

namespace G4NReact\MsCatalogMagento2\Console\Command;

use G4NReact\MsCatalogMagento2\Model\Indexer\AbstractIndexer;
use G4NReact\MsCatalogMagento2\Model\Indexer\CmsPageIndexer;

/**
 * Class ReindexCmsPage
 * @package G4NReact\MsCatalogMagento2\Console\Command
 */
class ReindexCmsPage extends AbstractReindex
{
    /**
     * @var CmsPageIndexer
     */
    protected $cmsIndexer;

    /**
     * ReindexCmsPage constructor
     *
     * @param CmsIndexer $cmsIndexer
     * @param string|null $name
     */
    public function __construct(
        CmsPageIndexer $cmsIndexer,
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
        return 'g4nreact:reindex:cms:page';
    }

    /**
     * @return string
     */
    public function getCommandDescription(): string
    {
        return 'Reindexes CMS page';
    }

    /**
     * @return AbstractIndexer
     */
    public function getIndexer()
    {
        return $this->cmsIndexer;
    }
}
