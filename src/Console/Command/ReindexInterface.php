<?php

namespace G4NReact\MsCatalogMagento2\Console\Command;

/**
 * Interface ReindexInterface
 * @package G4NReact\MsCatalogMagento2\Console\Command
 */
interface ReindexInterface
{
    /**
     * @param array $ids
     * @return void
     */
    public function prepareIds(array &$ids): void;

    /**
     * @return string
     */
    public function getCommandName(): string;

    /**
     * @return string
     */
    public function getCommandDescription(): string;
}
