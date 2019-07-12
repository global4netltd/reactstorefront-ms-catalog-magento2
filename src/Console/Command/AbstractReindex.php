<?php

namespace G4NReact\MsCatalogMagento2\Console\Command;

use Exception;
use G4NReact\MsCatalog\PullerInterface;
use G4NReact\MsCatalogIndexer\Indexer;
use G4NReact\MsCatalogMagento2\Helper\Config as ConfigHelper;
use Magento\Framework\App\State as AppState;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\App\Emulation;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class AbstractReindex
 * @package G4NReact\MsCatalogMagento2\Console\Command
 */
abstract class AbstractReindex extends Command implements ReindexInterface
{
    /**#@+
     * @var string Command option name
     */
    const INPUT_OPTION_IDS = 'ids';
    const INPUT_OPTION_ALL = 'all';
    /**#@- */

    /**#@+
     * @var string Message text
     */
    const SUCCESS_INFORMATION = 'Successfully reindex data';
    const REQUIRED_OPTION_INFO = 'Required parameter "ids" is missing.';
    /**#@- */

    /**
     * @var ConfigHelper
     */
    protected $magento2ConfigHelper;

    /**
     * @var Emulation
     */
    protected $emulation;

    /**
     * @var AppState
     */
    protected $appState;

    /**
     * AbstractReindex constructor
     *
     * @param ConfigHelper $magento2ConfigHelper
     * @param Emulation $emulation
     * @param AppState $appState
     * @param string|null $name
     */
    public function __construct(
        ConfigHelper $magento2ConfigHelper,
        Emulation $emulation,
        AppState $appState,
        ?string $name = null
    ) {
        $this->magento2ConfigHelper = $magento2ConfigHelper;
        $this->emulation = $emulation;
        $this->appState = $appState;
        parent::__construct($name);
    }

    /**
     * Configure command metadata.
     */
    protected function configure()
    {
        $this->setName($this->getCommandName())
            ->setDescription($this->getCommandDescription())
            ->setDefinition($this->getInputOptions());
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $start = microtime(true);
            foreach ($this->magento2ConfigHelper->getAllStores() as $store) {
                $this->appState->emulateAreaCode('adminhtml', function() use ($input, $output, $store) {
                    $this->reindex($input, $output, $store);
                });
            }
            $output->writeln((round(microtime(true) - $start, 4)) . 's');
        } catch (Exception $exception) {
            $output->writeln("Caught exception: " . $exception->getMessage());
        }
    }

    /**
     * @ToDo: Move the indexing to the appropriate class (Model\Indexer). Here you should only call the method.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param StoreInterface $store
     * @throws NoSuchEntityException
     */
    public function reindex(InputInterface $input, OutputInterface $output, StoreInterface $store)
    {
        // start store emulation
        $this->emulation->startEnvironmentEmulation($store->getId(), 'adminhtml', true);

        if ($this->magento2ConfigHelper->isIndexerEnabled()) {
            $puller = $this->getPuller();
            $config = $this->magento2ConfigHelper->getConfiguration();

            $ids = $input->getOption(self::INPUT_OPTION_IDS);
            $reindexAll = $input->getOption(self::INPUT_OPTION_ALL);

            if ($ids === [] && $reindexAll === false) {
                echo self::REQUIRED_OPTION_INFO . PHP_EOL;
                return;
            }

            if ($ids && $reindexAll === false) {
                $this->prepareIds($ids);
                $puller->setIds($ids);
            }

            $indexer = new Indexer($puller, $config);
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
            if (!is_int($id)) {
                unset($ids[$key]);
            }
        }
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
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                self::REQUIRED_OPTION_INFO
            ),
            new InputOption(
                self::INPUT_OPTION_ALL,
                null,
                InputOption::VALUE_OPTIONAL,
                self::REQUIRED_OPTION_INFO,
                false
            ),
        ];
    }

    /**
     * @return string
     */
    public abstract function getCommandName(): string;

    /**
     * @return string
     */
    public abstract function getCommandDescription(): string;

    /**
     * @return null|PullerInterface
     */
    public abstract function getPuller();
}
