<?php

namespace G4NReact\MsCatalogMagento2\Console\Command;

use G4NReact\MsCatalogMagento2\Model\Indexer\AbstractIndexer;
use G4NReact\MsCatalogMagento2\Model\Indexer\CmsPageIndexer;
use Magento\Store\Model\StoreManagerInterface;

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
     * @param StoreManagerInterface $storeManager
     * @param CmsPageIndexer $cmsIndexer
     * @param string|null $name
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        CmsPageIndexer $cmsIndexer,
        string $name = null
    ) {
        $this->cmsIndexer = $cmsIndexer;
        parent::__construct($storeManager, $name);
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
