<?php

namespace G4NReact\MsCatalogMagento2\Console\Command;

use G4NReact\MsCatalogMagento2\Model\Indexer\AbstractIndexer;
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
    /**
     * @var string
     */
    const INPUT_OPTION_IDS = 'ids';

    /**
     * @var string
     */
    const INPUT_OPTION_ALL = 'all';

    /**
     * @var string
     */
    const SUCCESS_INFORMATION = 'Successfully reindex data';

    /**
     * @var string
     */
    const REQUIRED_OPTION_INFO = 'Required parameter "ids" is missing.';

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
        $ids = $input->getOption(self::INPUT_OPTION_IDS);
        $reindexAll = $input->getOption(self::INPUT_OPTION_ALL);

        if ($ids === [] && $reindexAll === false) {
            echo self::REQUIRED_OPTION_INFO . PHP_EOL;
            return;
        }

        $result = $this->getIndexer()->run($ids);

        $output->writeln(PHP_EOL);
        $collectionTotalSize = \G4NReact\MsCatalog\Profiler::getDebugInfoEntry('collection_total_size');
        $output->writeln('collection size: ' . (int)$collectionTotalSize);
        $timers = \G4NReact\MsCatalog\Profiler::getTimers();
        if ($timers && is_array($timers)) {
            foreach ($timers as $timer => $time) {
                $roundedTime = round($time, 4);
                $output->writeln("{$timer}: {$roundedTime}s");
            }
        }

        $output->writeln($result);
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
     * @return AbstractIndexer
     */
    public abstract function getIndexer();
}
