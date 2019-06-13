<?php

namespace G4NReact\MsCatalogMagento2\Console\Command;

use Exception;
use G4NReact\MsCatalogIndexer\Indexer;
use G4NReact\MsCatalogMagento2\Model\Puller\CategoryPuller;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ReindexCategory
 * @package G4NReact\MsCatalogMagento2\Console\Command
 */
class ReindexCategory extends Command
{
    /**
     * @var CategoryPuller
     */
    protected $categoryPuller;

    /**
     * ReindexCategory constructor.
     * @param CategoryPuller $categoryPuller
     * @param null $name
     */
    public function __construct(
        CategoryPuller $categoryPuller,
        $name = null
    ) {
        $this->categoryPuller = $categoryPuller;
        parent::__construct($name);
    }

    /**
     * Configure command metadata.
     */
    protected function configure()
    {
        $this->setName('g4nreact:reindex:category')
            ->setDescription('Pull categories from Magento 2 database and push it to database search engine');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $puller = $this->categoryPuller;
            $config = $puller->getConfiguration();

            $indexer = new Indexer($puller, $config);
            $indexer->reindex();

            echo "Successfully reindex data" . PHP_EOL;
        } catch (Exception $exception) {
            echo "Caught exception: " . $exception->getMessage() . PHP_EOL;
        }
    }
}
