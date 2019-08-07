<?php

namespace G4NReact\MsCatalogMagento2\Helper;

use G4NReact\MsCatalog\Document\Field;

/**
 * Interface QueryHelperInterface
 * @package G4NReact\MsCatalogMagento2\Helper
 */
interface QueryHelperInterface
{

    /**
     * @param string $columnName
     * @param null $value
     *
     * @return Field
     */
    public function getFieldByCmsColumnName(string $columnName, $value = null) : Field;
}
