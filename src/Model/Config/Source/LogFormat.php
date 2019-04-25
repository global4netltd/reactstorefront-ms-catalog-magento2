<?php

namespace G4NReact\MsCatalogMagento2\Model\Config\Source;

/**
 * Class LogFormat
 * @package G4NReact\MsCatalogMagento2\Model\Config\Source
 */
class LogFormat implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [['value' => 0, 'label' => __('Text')], ['value' => 1, 'label' => __('JSON')]];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [0 => __('Text'), 1 => __('JSON')];
    }
}
