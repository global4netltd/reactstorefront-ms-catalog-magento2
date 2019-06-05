<?php

namespace G4NReact\MsCatalogMagento2\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use G4NReact\MsCatalogIndexer\Indexer;

/**
 * Class ReindexCategory
 * @package G4NReact\MsCatalogMagento2\Console\Command
 */
class ReindexCategory extends Command
{
    /**
     * @var \G4NReact\MsCatalogMagento2\Model\Puller\CategoryPuller
     */
    protected $categoryPuller;

    /**
     * ReindexCategory constructor.
     * @param \G4NReact\MsCatalogMagento2\Model\Puller\CategoryPuller $categoryPuller
     * @param null $name
     */
    public function __construct(
        \G4NReact\MsCatalogMagento2\Model\Puller\CategoryPuller $categoryPuller,
        $name = null
    )
    {
        $this->categoryPuller = $categoryPuller;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setName('g4nreact:reindex:category')
            ->setDescription('Reindexes categories');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return bool|int|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $puller = $this->categoryPuller;
            $config = $puller->getConfiguration();

            $indexer = new Indexer($puller, $config);

            $indexer->reindex();
            return true;
        } catch (\Exception $exception) {
            echo "Caught exception: " . $exception->getMessage() . PHP_EOL;
        }
    }
}