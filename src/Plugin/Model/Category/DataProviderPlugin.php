<?php

namespace G4NReact\MsCatalogMagento2\Plugin\Model\Category;

use G4NReact\MsCatalogMagento2\Model\Attribute\ReactStoreFrontFilters;
use G4NReact\MsCatalogMagento2\Model\Config\Source\AttributesReactFilter;
use Magento\Catalog\Model\Category\DataProvider;
use Magento\Framework\App\RequestInterface;

/**
 * Class DataProviderPlugin
 * @package G4NReact\MsCatalogMagento2\Plugin\Model\Category
 */
class DataProviderPlugin
{
    /** @var string react storefront filters suffix */
    const REACT_STOREFRONT_FILTERS_SUFFIX = '_react_storefront_filters';

    /**
     * @var ReactStoreFrontFilters
     */
    protected $reactStoreFrontFilters;

    /**
     * @var AttributesReactFilter
     */
    protected $attributesReactFilter;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * DataProviderPlugin constructor.
     *
     * @param ReactStoreFrontFilters $reactStoreFrontFilters
     * @param AttributesReactFilter $attributesReactFilter
     */
    public function __construct(
        ReactStoreFrontFilters $reactStoreFrontFilters,
        AttributesReactFilter $attributesReactFilter,
        RequestInterface $request
    )
    {
        $this->reactStoreFrontFilters = $reactStoreFrontFilters;
        $this->attributesReactFilter = $attributesReactFilter;
        $this->request = $request;
    }

    /**
     * @param DataProvider $subject
     * @param $result
     *
     * @return mixed
     */
    public function afterPrepareMeta(DataProvider $subject, $result)
    {
        $result['react_storefront_filters'] = [
            'children' => $this->prepareAttributesFields()
        ];

        return $result;
    }

    /**
     * @param DataProvider $subject
     * @param $result
     *
     * @return mixed
     */
    public function afterGetData(DataProvider $subject, $result)
    {
        $categoryId = $this->request->getParam('id');
        $filters = $this->reactStoreFrontFilters->getReactStoreFrontFiltersByCategoryId($categoryId);
        foreach ($filters[AttributesReactFilter::FACETS] as $facets){
            $result[$categoryId][$facets . self::REACT_STOREFRONT_FILTERS_SUFFIX][0] = AttributesReactFilter::FACETS;
        }
        foreach ($filters[AttributesReactFilter::STATS] as $stats){
            if(!isset($result[$categoryId][$stats . self::REACT_STOREFRONT_FILTERS_SUFFIX])){
                $result[$categoryId][$stats . self::REACT_STOREFRONT_FILTERS_SUFFIX][0] = null;
            }
            $result[$categoryId][$stats . self::REACT_STOREFRONT_FILTERS_SUFFIX][1] = AttributesReactFilter::STATS;
        }
        return $result;
    }

    /**
     * Prepare by category attributes
     *
     * @return array
     */
    protected function prepareAttributesFields()
    {
        $res = [];
        foreach ($this->reactStoreFrontFilters->getCategoryAttributes() as $attribute) {
            $res[$attribute->getAttributeCode() . self::REACT_STOREFRONT_FILTERS_SUFFIX] = [
                'arguments' => [
                    'data' => [
                        'name' => $attribute->getAttributeCode() . self::REACT_STOREFRONT_FILTERS_SUFFIX,
                        'config' => [
                            'formElement' => 'checkboxset',
                            'visible' => true,
                            'required' => false,
                            'label' => $attribute->getAttributeCode(),
                            'componentType' => 'field',
                            'multiple' => true,
                            'source' => 'module',
                            'options' => $this->attributesReactFilter->toOptionArray(),
                        ]
                    ]
                ]
            ];
        }

        return $res;
    }
}
