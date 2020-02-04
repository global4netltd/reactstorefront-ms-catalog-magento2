<?php

namespace G4NReact\MsCatalogMagento2\Console\Command;


use G4NReact\MsCatalogMagento2\Model\Indexer\SearchTermsIndexer;
use Magento\Store\Model\StoreManagerInterface;
use Symfony\Component\Console\Input\InputOption;
use G4NReact\MsCatalogMagento2\Model\Indexer\AbstractIndexer;

/**
 * Class ReindexAllSearchTerms
 * @package G4NReact\MsCatalogMagento2\Console\Command
 */
class ReindexAllSearchTerms extends AbstractReindex
{
    /**
     * @var SearchTermsIndexer
     */
    protected $searchTermsIndexer;

    /**
     * ReindexAllSearchTerms constructor.
     *
     * @param StoreManagerInterface $storeManager
     * @param SearchTermsIndexer $searchTermsIndexer
     * @param string|null $name
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        SearchTermsIndexer $searchTermsIndexer,
        string $name = null
    ) {
        $this->searchTermsIndexer = $searchTermsIndexer;
        parent::__construct($storeManager, $name);
    }

    /**
     * @return string
     */
    public function getCommandName(): string
    {
        return 'g4nreact:reindexall:search:terms';
    }

    /**
     * @return string
     */
    public function getCommandDescription(): string
    {
        return 'Reindexes all search terms';
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
        return $this->searchTermsIndexer;
    }
}
