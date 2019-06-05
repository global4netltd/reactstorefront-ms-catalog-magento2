<?php

namespace G4NReact\MsCatalogMagento2\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use G4NReact\MsCatalogIndexer\Indexer;

/**
 * Class ReindexProduct
 * @package G4NReact\MsCatalogMagento2\Console\Command
 */
class ReindexProduct extends Command
{
    /**
     * @var \G4NReact\MsCatalogMagento2\Model\Puller\ProductPuller
     */
    protected $productPuller;

    /**
     * ReindexProduct constructor.
     * @param \G4NReact\MsCatalogMagento2\Model\Puller\ProductPuller $productPuller
     * @param null $name
     */
    public function __construct(
        \G4NReact\MsCatalogMagento2\Model\Puller\ProductPuller $productPuller,
        $name = null
    )
    {
        $this->productPuller = $productPuller;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setName('g4nreact:reindex:product')
            ->setDescription('Reindexes products');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $puller = $this->productPuller;
            $config = $puller->getConfiguration();

            $indexer = new Indexer($puller, $config);

            $indexer->reindex();

            return true;
        } catch (\Exception $exception) {
            echo "Caught exception: " . $exception->getMessage();
            
            return false;
        }
    }
}