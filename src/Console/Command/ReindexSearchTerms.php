<?php

namespace G4NReact\MsCatalogMagento2\Console\Command;


use G4NReact\MsCatalogMagento2\Model\Indexer\SearchTermsIndexer;
use Symfony\Component\Console\Input\InputOption;
use G4NReact\MsCatalogMagento2\Model\Indexer\AbstractIndexer;

/**
 * Class ReindexSearchTerms
 * @package G4NReact\MsCatalogMagento2\Console\Command
 */
class ReindexSearchTerms extends AbstractReindex
{
    /**
     * @var SearchTermsIndexer
     */
    protected $searchTermsIndexer;

    /**
     * ReindexSearchTerms constructor.
     *
     * @param SearchTermsIndexer $searchTermsIndexer
     * @param string|null $name
     */
    public function __construct(
        SearchTermsIndexer $searchTermsIndexer,
        string $name = null
    ) {
        $this->searchTermsIndexer = $searchTermsIndexer;
        parent::__construct($name);
    }

    /**
     * @return string
     */
    public function getCommandName(): string
    {
        return 'g4nreact:reindex:search:terms';
    }

    /**
     * @return string
     */
    public function getCommandDescription(): string
    {
        return 'Reindexes search terms';
    }

    /**
     * @return AbstractIndexer
     */
    public function getIndexer()
    {
        return $this->searchTermsIndexer;
    }
}
