<?php

namespace G4NReact\MsCatalogMagento2\Console\Command;

use G4NReact\MsCatalogMagento2\Model\Indexer\AbstractIndexer;
use G4NReact\MsCatalogMagento2\Model\Indexer\CmsBlockIndexer;

/**
 * Class ReindexCmsBlock
 * @package G4NReact\MsCatalogMagento2\Console\Command
 */
class ReindexCmsBlock extends AbstractReindex
{
    /** @var  */
    protected $cmsBlockIndexer;

    /**
     * ReindexCmsBlock constructor.
     *
     * @param CmsBlockIndexer $cmsBlockIndexer
     * @param string|null $name
     */
    public function __construct(
        CmsBlockIndexer $cmsBlockIndexer,
        string $name = null
    ) {
        $this->cmsBlockIndexer = $cmsBlockIndexer;
        parent::__construct($name);
    }

    /**
     * @return string
     */
    public function getCommandName(): string
    {
        return 'g4nreact:reindex:cms:block';
    }

    /**
     * @return string
     */
    public function getCommandDescription(): string
    {
        return 'Reindexes CMS Block';
    }

    /**
     * @return AbstractIndexer
     */
    public function getIndexer()
    {
        return $this->cmsBlockIndexer;
    }
}
