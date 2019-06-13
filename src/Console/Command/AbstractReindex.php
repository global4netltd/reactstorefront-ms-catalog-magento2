<?php

namespace G4NReact\MsCatalogMagento2\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class AbstractReindex
 * @package G4NReact\MsCatalogMagento2\Console\Command
 */
abstract class AbstractReindex extends Command
{
    const INPUT_OPTION_IDS = 'ids';
    const INPUT_OPTION_ALL = 'all';
    const SUCCESS_INFORMATION = 'Successfully reindex data';
    const REQUIRED_OPTION_INFO = 'Required ids or all option';

    /**
     * @param $ids
     */
    protected function prepareIds(&$ids)
    {
        $ids = explode(',', reset($ids));
        foreach ($ids as $key => $id) {
            if (!is_int($id)) {
                unset($ids[$key]);
            }
        }
    }
}