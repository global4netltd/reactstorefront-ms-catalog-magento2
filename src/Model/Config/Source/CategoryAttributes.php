<?php

namespace G4NReact\MsCatalogMagento2\Model\Config\Source;

use G4NReact\MsCatalogMagento2\Model\Attribute\ReactStoreFrontFilters;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Exception\InputException;

/**
 * Class CategoryAttributes
 * @package G4NReact\MsCatalogMagento2\Model\Config\Source
 */
class CategoryAttributes implements OptionSourceInterface
{
    /**
     * @var ReactStoreFrontFilters
     */
    protected $reactStoreFrontFilters;

    /**
     * CategoryAttributes constructor.
     *
     * @param ReactStoreFrontFilters $reactStoreFrontFilters
     */
    public function __construct(
        ReactStoreFrontFilters $reactStoreFrontFilters
    )
    {
        $this->reactStoreFrontFilters = $reactStoreFrontFilters;
    }

    /**
     * @return array
     * @throws InputException
     */
    public function toOptionArray()
    {
        $res = [];
        foreach ($this->reactStoreFrontFilters->getProductAttributes() as $categoryAttribute){
            $res[] = [
                'value' => $categoryAttribute->getAttributeCode(),
                'label' => $categoryAttribute->getDefaultFrontendLabel()
            ];
        }   
        
        return $res;
    }
}
