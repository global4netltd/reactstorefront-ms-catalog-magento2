<?php

namespace G4NReact\MsCatalogMagento2\Console\Command;

use Exception;
use G4NReact\MsCatalogIndexer\Indexer;
use G4NReact\MsCatalogMagento2\Model\Puller\ProductPuller;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ReindexProduct
 * @package G4NReact\MsCatalogMagento2\Console\Command
 */
class ReindexProduct extends Command
{
    /**
     * @var ProductPuller
     */
    protected $productPuller;

    /**
     * ReindexProduct constructor.
     * @param ProductPuller $productPuller
     * @param null $name
     */
    public function __construct(
        ProductPuller $productPuller,
        $name = null
    ) {
        $this->productPuller = $productPuller;
        parent::__construct($name);
    }

    /**
     * Configure command metadata.
     */
    protected function configure()
    {
        $this->setName('g4nreact:reindex:product')
            ->setDescription('Pull products from Magento 2 database and push it to database search engine');
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

            echo "Successfully reindex data" . PHP_EOL;
        } catch (Exception $exception) {
            echo "Caught exception: " . $exception->getMessage();
        }
    }
}
