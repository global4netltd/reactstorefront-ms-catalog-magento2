<?php

namespace G4NReact\MsCatalogMagento2\Console\Command;

use G4NReact\MsCatalogMagento2\Model\Indexer\AbstractIndexer;
use G4NReact\MsCatalogMagento2\Model\Indexer\CmsBlockIndexer;
use Magento\Store\Model\StoreManagerInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class ReindexAllCmsBlock
 * @package G4NReact\MsCatalogMagento2\Console\Command
 */
class ReindexAllCmsBlock extends AbstractReindex
{
    /**
     * @var CmsBlockIndexer
     */
    protected $cmsBlockIndexer;

    /**
     * ReindexAllCmsBlock constructor.
     *
     * @param StoreManagerInterface $storeManager
     * @param CmsBlockIndexer $cmsBlockIndexer
     * @param string|null $name
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        CmsBlockIndexer $cmsBlockIndexer,
        string $name = null
    ) {
        $this->cmsBlockIndexer = $cmsBlockIndexer;
        parent::__construct($storeManager, $name);
    }

    /**
     * @return string
     */
    public function getCommandName(): string
    {
        return 'g4nreact:reindexall:cms:block';
    }

    /**
     * @return string
     */
    public function getCommandDescription(): string
    {
        return 'Reindexes all cms blocks';
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
        return $this->cmsBlockIndexer;
    }
}
