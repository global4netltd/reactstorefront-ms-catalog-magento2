<?php

namespace G4NReact\MsCatalogMagento2\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class AttributesReactFilter
 * @package G4NReact\MsCatalogMagento2\Model\Config\Source
 */
class AttributesReactFilter implements OptionSourceInterface
{
    /** @var string stats */
    const STATS = 'stats';

    /** @var string facets */
    const FACETS = 'facets';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => 'facets', 'label' => __('Facets')
            ],
            [
                'value' => 'stats', 'label' => __('Stats')
            ],
        ];
    }
}
