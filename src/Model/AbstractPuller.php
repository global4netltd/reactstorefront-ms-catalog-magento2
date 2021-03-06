<?php

namespace G4NReact\MsCatalogMagento2\Model;

use G4NReact\MsCatalog\Document;
use G4NReact\MsCatalog\PullerInterface;
use G4NReact\MsCatalogMagento2\Helper\Config as ConfigHelper;
use Iterator;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class AbstractPuller
 * @package G4NReact\MsCatalogMagento2\Model
 */
abstract class AbstractPuller implements Iterator, PullerInterface
{
    /**
     * @var int
     */
    const PAGE_SIZE_DEFAULT = 100;

    /** @var int default current page */
    const CUR_PAGE_DEFAULT = 0;

    /**
     * @var int
     */
    public $totalSize = null;

    /**
     * @var int
     */
    public $curPage;

    /**
     * @var int
     */
    public $pageSize = 10;

    /**
     * @var int
     */
    public $position = 0;

    /**
     * @var int
     */
    public $totalPosition = 0;

    /**
     * @var array
     */
    public $pageArray;

    /**
     * @var array
     */
    public $ids;

    /**
     * @var string
     */
    public $type;

    /**
     * @var ConfigHelper
     */
    protected $magento2ConfigHelper;

    /**
     * Puller constructor
     * @param ConfigHelper $magento2ConfigHelper
     * @throws NoSuchEntityException
     */
    public function __construct(
        ConfigHelper $magento2ConfigHelper
    ) {
        $this->magento2ConfigHelper = $magento2ConfigHelper;
        $this->position = 0;
        $this->curPage = self::CUR_PAGE_DEFAULT;

        $this->pageSize = $magento2ConfigHelper->getConfiguration()->getPullerPageSize() ?: self::PAGE_SIZE_DEFAULT;
    }

    /**
     * @return int
     */
    public function getPageSize(): int
    {
        return $this->pageSize;
    }

    /**
     * @param int $pageSize
     * @return PullerInterface
     */
    public function setPageSize(int $pageSize): PullerInterface
    {
        $this->pageSize = $pageSize;

        return $this;
    }

    /**
     * @param int $curPage
     *
     * @return PullerInterface
     */
    public function setCurPage(int $curPage): PullerInterface
    {
        $this->curPage = $curPage;

        return $this;
    }

    /**
     * @return int
     */
    public function getCurPage() : int
    {
        return $this->curPage;
    }

    /**
     * @return mixed
     */
    abstract public function getCollection();

    /**
     * @return array
     */
    public function getIds(): array
    {
        return is_array($this->ids) ? $this->ids : [];
    }

    /**
     * @param array $ids
     * @return PullerInterface
     */
    public function setIds(array $ids): PullerInterface
    {
        $this->ids = $ids;

        return $this;
    }

    /**
     * @param string $type
     *
     * @return $this
     */
    public function setType(string $type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    abstract public function getType(): string;

    /**
     * @inheritdoc
     */
    public function rewind()
    {
        $this->position = 0;
        $this->curPage = 0;
    }

    /**
     * @return Document
     */
    public function current(): Document
    {
        $document = new Document();

        return $document;
    }

    /**
     * @inheritdoc
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * @inheritdoc
     */
    public function next()
    {
        ++$this->position;
        ++$this->totalPosition;
    }

    /**
     * @inheritdoc
     */
    public function valid()
    {
        $collection = null;

        if (is_null($this->totalSize)) {
            $collection = $this->getCollection();
            $this->totalSize = $collection->getSize();
        }

        if ($this->totalPosition == $this->totalSize) {
            return false;
        }

        if ($this->position == $this->pageSize) {
            $this->position = 0;
        }

        if ($this->position == 0) {
            $this->curPage++;
            $collection = is_null($collection) ? $this->getCollection() : $collection;

            $this->pageArray = [];
            foreach ($collection as $item) {
                $this->pageArray[] = $item;
            }
        }
        return isset($this->pageArray[$this->position]);
    }
}
