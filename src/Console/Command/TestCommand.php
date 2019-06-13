<?php

namespace G4NReact\MsCatalogMagento2\Console\Command;

use G4NReact\MsCatalog\Puller;
use G4NReact\MsCatalogIndexer\Indexer;
use G4NReact\MsCatalogIndexer\Config;
use G4NReact\MsCatalogSolr\Query;
use G4NReact\MsCatalogSolr\QueryBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;

use Symfony\Component\Console\Output\OutputInterface;

class TestCommand extends Command
{
    /**
     * @var \G4NReact\MsCatalogMagento2\Model\Puller
     */
    protected $productPuller;

    protected $categoryPuller;

    protected $cmsPuller;

    protected $deploymentConfig;

    /**
     * TestCommand constructor
     * @param \G4NReact\MsCatalogMagento2\Model\Puller\ProductPuller $productPuller
     * @param \G4NReact\MsCatalogMagento2\Model\Puller\CategoryPuller $categoryPuller
     * @param \G4NReact\MsCatalogMagento2\Model\Puller\CmsPuller $cmsPuller
     * @param null $name
     */
    public function __construct(
        \G4NReact\MsCatalogMagento2\Model\Puller\ProductPuller $productPuller,
        \G4NReact\MsCatalogMagento2\Model\Puller\CategoryPuller $categoryPuller,
        \G4NReact\MsCatalogMagento2\Model\Puller\CmsPuller $cmsPuller,
        \Magento\Framework\App\DeploymentConfig $deploymentConfig,
        $name = null
    ) {
        $this->productPuller = $productPuller;
        $this->categoryPuller = $categoryPuller;
        $this->cmsPuller = $cmsPuller;
        $this->deploymentConfig = $deploymentConfig;
        parent::__construct($name);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('g4nreact:ms-catalog:test')->setDescription('Test console command');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $host = '10.0.0.21';
        $port = 9090;
        $path = '/solr/';
        $collection = '';
        $core = 'qa';



        $config = new Config(Config::ENGINE_SOLR, $host, $port, $path, $collection, $core, 10, false);
        $config->setEngine($config::ENGINE_SOLR);

        $puller = new Puller($config);

        $queryBuilder = $puller->getQueryBuilder();

        $storeFilterQuery = [
        'key' => 'store_id',
        'query' => 'store_id:1'
        ];

        $visibleFilterQuery = [
        'key' => 'is_visible_in_search_i',
        'query' => 'is_visible_in_search_i:1'
        ];

        $tagFilterQuery = [
        'key' => 'extendedsolr_tag_facet',
        'query' => 'extendedsolr_tag_facet:249'
        ];

        /** @var QueryBuilder $queryBuilder
        $query = $queryBuilder
        ->addFilterQuery($storeFilterQuery)
        ->addFilterQuery($visibleFilterQuery)
        ->addFilterQuery($tagFilterQuery)
        ->buildQuery();

        $result = $puller->pull($query);

        $documents = $result->getDocumentsCollection();
        $facets = $result->getFacets();
         */
        // bierzemy puller z ms-catalog-magento2
        $puller = $this->productPuller;
        $puller = $this->categoryPuller;
        $puller = $this->cmsPuller;
        // bierzemy config z magento2
        $config = $puller->getConfiguration();

        //$output->writeln("engine: " . $config->getEngine());
        //$output->writeln("host: " . $config->getHost());
        //$output->writeln("port: " . $config->getPort());
        //$output->writeln("path: " . $config->getPath());
        //$output->writeln("core: " . $config->getCore());

        foreach ($puller as $document) {
            //    $output->writeln("object_id: " . $document->getObjectId());
            //    $output->writeln("object_type: " . $document->getObjectType());
            //    $output->writeln("data: " . var_dump($document->getData()));

            break;
        }

        // $output->writeln("count: " . count($puller));

        // tworzymy nowy ms-catalog-indexer przekazując mu Documents i Config w konstruktorze
        $indexer = new Indexer($puller, $config);

        // $indexer->getPusher->Push() ? $indexer->reindex()
        $indexer->reindex();

        // indexer tworzy pusher na podstawie konfiguracji (solr) przekazując mu konfigurację w konstruktorze i foreach Documents odpala query?
1
        // $output->writeln("Found: " . $response->getNumFound());
    }
}
