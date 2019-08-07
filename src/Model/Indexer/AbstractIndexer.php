<?php

namespace G4NReact\MsCatalogMagento2\Model\Indexer;

use Exception;
use G4NReact\MsCatalog\Client\ClientFactory;
use G4NReact\MsCatalog\Document\Field;
use G4NReact\MsCatalog\PullerInterface;
use G4NReact\MsCatalog\Response;
use G4NReact\MsCatalog\ResponseInterface;
use G4NReact\MsCatalog\Indexer;
use G4NReact\MsCatalogMagento2\Helper\Config as ConfigHelper;
use Magento\Framework\App\State as AppState;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Indexer\ActionInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\App\Emulation;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class AbstractIndexer
 * @package G4NReact\MsCatalogMagento2\Model\Indexer
 */
abstract class AbstractIndexer implements ActionInterface, \Magento\Framework\Mview\ActionInterface
{
    /**
     * @var string
     */
    const SUCCESS_INFORMATION = 'Successfully reindex data';

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Emulation
     */
    protected $emulation;

    /**
     * @var AppState
     */
    protected $appState;

    /**
     * @var ConfigHelper
     */
    protected $configHelper;

    /**
     * AbstractIndexer constructor
     *
     * @param StoreManagerInterface $storeManager
     * @param Emulation $emulation
     * @param AppState $appState
     * @param ConfigHelper $configHelper
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        Emulation $emulation,
        AppState $appState,
        ConfigHelper $configHelper
    ) {
        $this->storeManager = $storeManager;
        $this->emulation = $emulation;
        $this->appState = $appState;
        $this->configHelper = $configHelper;
    }

    /**
     * @param array $ids
     * @param StoreInterface|null $store
     * @return string
     */
    public function run(array $ids = [], StoreInterface $store = null)
    {
        try {
            $start = microtime(true);
            $stores = $store ? [$store] : $this->storeManager->getStores();
            foreach ($stores as $store) {
                $this->appState->emulateAreaCode('frontend', function () use ($store, $ids) {
                    $this->reindex($store, $ids);
                });
            }
            return (round(microtime(true) - $start, 4)) . 's';
        } catch (Exception $exception) {
            return "Caught exception: " . $exception->getMessage();
        }
    }

    /**
     * @param StoreInterface $store
     * @param array $ids
     * @throws NoSuchEntityException
     * @throws Exception
     */
    public function reindex(StoreInterface $store, array $ids = [])
    {
        // start store emulation
        $this->emulation->startEnvironmentEmulation($store->getId(), 'frontend', true);
        if ($this->configHelper->isIndexerEnabled()) {
            $puller = $this->getPuller();
            $config = $this->configHelper->getConfiguration();

            if ($ids) {
                $this->prepareIds($ids);
                $puller->setIds($ids);
            }

            $indexer = new Indexer($puller, $config);
            if ($config->getPusherDeleteIndex() && !$ids) {
                $this->clearIndexByObjectType($puller->getType(), $config, $store->getId());
            }
            $indexer->reindex();
            echo self::SUCCESS_INFORMATION . PHP_EOL;
        }

        // end store emulation
        $this->emulation->stopEnvironmentEmulation();
    }

    /**
     * @param array $ids
     * @return void
     */
    public function prepareIds(array &$ids): void
    {
        $ids = explode(',', reset($ids));
        foreach ($ids as $key => $id) {
            if (!is_numeric($id)) {
                unset($ids[$key]);
            }
        }
    }

    /**
     * @param string $type
     * @param $config
     * @param $storeId
     * @return ResponseInterface
     * @throws Exception
     */
    protected function clearIndexByObjectType(string $type, $config, $storeId)
    {
        $client = ClientFactory::getInstance($config);
        if ($type) {
            return $client->deleteByFields(
                [
                    new Field('object_type', $type),
                    new Field('store_id', $storeId, Field::FIELD_TYPE_INT, true, false)
                ]
            );
        }

        return new Response();
    }

    /**
     * @return null|PullerInterface
     */
    abstract public function getPuller();

    /**
     * @param int[] $ids
     */
    public function execute($ids)
    {
        $this->run($ids);
    }

    /**
     * @param array $ids
     */
    public function executeList(array $ids)
    {
        $this->run($ids);
    }

    /**
     * @param int $id
     */
    public function executeRow($id)
    {
        $this->run([$id]);
    }

    /**
     * Reindex all
     */
    public function executeFull()
    {
        $this->run();
    }
}
