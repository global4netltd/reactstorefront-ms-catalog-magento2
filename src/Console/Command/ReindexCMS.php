<?php

namespace G4NReact\MsCatalogMagento2\Console\Command;

use Exception;
use G4NReact\MsCatalogIndexer\Indexer;
use G4NReact\MsCatalogMagento2\Model\Puller\CmsPuller;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ReindexCMS
 * @package G4NReact\MsCatalogMagento2\Console\Command
 */
class ReindexCMS extends AbstractReindex
{
    /**
     * @var CmsPuller
     */
    protected $cmsPuller;

    /**
     * ReindexCMS constructor.
     * @param CmsPuller $cmsPuller
     * @param string|null $name
     */
    public function __construct(
        CmsPuller $cmsPuller,
        ?string $name = null
    ) {
        $this->cmsPuller = $cmsPuller;
        parent::__construct($name);
    }

    /**
     * Configure command metadata.
     */
    protected function configure()
    {
        $this->setName('g4nreact:reindex:cms')
            ->setDescription('Reindexes CMS')
            ->setDefinition([
                new InputOption(self::INPUT_OPTION_IDS, null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, self::REQUIRED_OPTION_INFO),
            ]);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $puller = $this->cmsPuller;
            $config = $puller->getConfiguration();
            $ids = $input->getOption(self::INPUT_OPTION_IDS);
            if ($ids) {
                if (reset($ids) !== self::INPUT_OPTION_ALL) {
                    $this->prepareIds($ids);
                    $puller->setIds($ids);
                }
                $indexer = new Indexer($puller, $config);
                $indexer->reindex();
                echo self::SUCCESS_INFORMATION . PHP_EOL;
            } else {
                echo self::REQUIRED_OPTION_INFO . PHP_EOL;
            }
        } catch (Exception $exception) {
            echo "Caught exception: " . $exception->getMessage() . PHP_EOL;
        }
    }
}
