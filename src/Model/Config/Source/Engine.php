<?php

namespace G4NReact\MsCatalogMagento2\Model\Config\Source;

use G4NReact\MsCatalog\Helper;
use Magento\Framework\Option\ArrayInterface;

/**
 * Class Engine
 * @package G4NReact\MsCatalogMagento2\Model\Config\Source
 */
class Engine implements ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        $enginesOptionArray = [];

        foreach (Helper::$engines as $engineValue => $engine) {
            $enginesOptionArray[] = [
                'value' => $engineValue,
                'label' => __($engine['label']),
            ];
        }

        return $enginesOptionArray;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray(): array
    {
        $enginesOptionArray = [];

        foreach (Helper::$engines as $engineValue => $engine) {
            $enginesOptionArray[$engineValue] = __($engine['label']);
        }

        return $enginesOptionArray;
    }
}
