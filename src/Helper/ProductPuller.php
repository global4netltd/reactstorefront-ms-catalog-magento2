<?php

namespace G4NReact\MsCatalogMagento2\Helper;

/**
 * Class ProductPuller
 * @package G4NReact\MsCatalogMagento2\Helper
 */
class ProductPuller
{
    /**
     * @param string $sourceCode
     *
     * @return string
     */
    public static function prepareFieldNameBySourceCode(string $sourceCode) : string
    {
        return 'stock_' . $sourceCode . '_qty';
    }
}
