<?php

namespace G4NReact\MsCatalogMagento2\Model;

/**
 * Interface PullerInterface
 * @package G4NReact\MsCatalogMagento2\Model
 */
interface PullerInterface
{
    /**
     * @return int
     */
    public function getPageSize(): int;

    /**
     * @param int $pageSize
     * @return PullerInterface
     */
    public function setPageSize(int $pageSize): PullerInterface;

    /**
     * @return mixed
     */
    public function getCollection();

    /**
     * @return array
     */
    public function getIds(): array;

    /**
     * @param mixed $ids
     * @return PullerInterface
     */
    public function setIds(array $ids): PullerInterface;
}
