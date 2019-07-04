<?php

namespace G4NReact\MsCatalogMagento2\Model\Config\Source;

use \Magento\Framework\Data\OptionSourceInterface;

/**
 * Class AttributeWeight
 * @package G4NReact\MsCatalogMagento2\Model\Config\Source
 */
class AttributeWeight implements OptionSourceInterface
{
    const VALUE_NOT_INCLUDE = 'Not Include';
    /**
     * @var array
     */
    protected $weights = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10'];

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $res = [];
        foreach ($this->weights as $value) {
            $res[] = ['value' => $value, 'label' => $value ? $value : __(self::VALUE_NOT_INCLUDE)];
        }
        return $res;
    }
}