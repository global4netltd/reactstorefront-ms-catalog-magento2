<?php

namespace G4NReact\MsCatalogMagento2\Console\Command;

use G4NReact\MsCatalogMagento2\Model\Indexer\AbstractIndexer;
use G4NReact\MsCatalogMagento2\Model\Indexer\CmsBlockIndexer;
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
