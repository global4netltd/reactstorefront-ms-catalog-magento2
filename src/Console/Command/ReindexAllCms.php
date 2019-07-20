<?php

namespace G4NReact\MsCatalogMagento2\Console\Command;

use G4NReact\MsCatalogMagento2\Model\Indexer\AbstractIndexer;
use G4NReact\MsCatalogMagento2\Model\Indexer\CmsIndexer;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class ReindexAllCms
 * @package G4NReact\MsCatalogMagento2\Console\Command
 */
class ReindexAllCms extends AbstractReindex
{
    /**
     * @var CmsIndexer
     */
    protected $cmsIndexer;

    /**
     * ReindexAllCms constructor
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
        return 'g4nreact:reindexall:cms';
    }

    /**
     * @return string
     */
    public function getCommandDescription(): string
    {
        return 'Reindexes all cms';
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
        return $this->cmsIndexer;
    }
}
