<?php

namespace G4NReact\MsCatalogMagento2\Model;

use G4NReact\MsCatalog\Document;
use G4NReact\MsCatalog\PullerInterface;
use G4NReact\MsCatalogMagento2\Helper\MsCatalog as MsCatalogHelper;
use Iterator;

/**
 * Class AbstractPuller
 * @package G4NReact\MsCatalogMagento2\Model
 */
abstract class AbstractPuller implements Iterator, PullerInterface
{
    /**
     * @var int
     */
    const PAGE_SIZE_DEFAULT = 10;

    /**
     * @var int
     */
    public $totalSize = 1000;

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
     * @var MsCatalogHelper
     */
    protected $msCatalogHelper;

    /**
     * Puller constructor
     * @param MsCatalogHelper $msCatalogHelper
     */
    public function __construct(
        MsCatalogHelper $msCatalogHelper
    ) {
        $this->msCatalogHelper = $msCatalogHelper;
        $this->position = 0;
        $this->totalSize = $this->getCollection()->getSize();
        $this->curPage = 0;

        $this->pageSize = $msCatalogHelper->getConfigByPath('ms_catalog_indexer/indexer_settings/pagesize') ?: self::PAGE_SIZE_DEFAULT;
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
    
    public function getCollection()
    {
    }

    /**
     * @return array
     */
    public function getIds(): array
    {
        return $this->ids;
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
        if ($this->totalPosition == $this->totalSize) {
            return false;
        }

        if ($this->position == $this->pageSize) {
            $this->position = 0;
        }

        if ($this->position == 0) {
            $collection = $this->getCollection();
            $this->curPage++;

            $this->pageArray = array();
            foreach ($collection as $item) {
                $this->pageArray[] = $item;
            }
        }

        return isset($this->pageArray[$this->position]);
    }
}
