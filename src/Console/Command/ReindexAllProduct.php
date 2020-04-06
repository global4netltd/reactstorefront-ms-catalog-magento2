<?php

namespace G4NReact\MsCatalogMagento2\Console\Command;

use G4NReact\MsCatalogMagento2\Model\Indexer\AbstractIndexer;
use G4NReact\MsCatalogMagento2\Model\Indexer\ProductIndexer;
use Magento\Store\Model\StoreManagerInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class ReindexAllProduct
 * @package G4NReact\MsCatalogMagento2\Console\Command
 */
class ReindexAllProduct extends AbstractReindex
{
    /**
     * @var ProductIndexer
     */
    protected $productIndexer;

    /**
     * ReindexAllProduct constructor
     *
     * @param StoreManagerInterface $storeManager
     * @param ProductIndexer $productIndexer
     * @param string|null $name
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ProductIndexer $productIndexer,
        string $name = null
    ) {
        $this->productIndexer = $productIndexer;
        parent::__construct($storeManager, $name);
    }

    /**
     * @return string
     */
    public function getCommandName(): string
    {
        return 'g4nreact:reindexall:product';
    }

    /**
     * @return string
     */
    public function getCommandDescription(): string
    {
        return 'Reindexes all products';
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
            new InputOption(
                self::INPUT_OPTION_STORE_ID,
                null,
                InputOption::VALUE_OPTIONAL,
                self::REQUIRED_OPTION_INFO,
                false
            )
        ];
    }

    /**
     * @return AbstractIndexer
     */
    public function getIndexer()
    {
        return $this->productIndexer;
    }
}
