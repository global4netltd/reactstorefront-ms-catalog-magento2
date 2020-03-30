<?php

namespace G4NReact\MsCatalogMagento2\Plugin\Model\Category;

use G4NReact\MsCatalogMagento2\Model\Attribute\ReactStoreFrontFilters;
use G4NReact\MsCatalogMagento2\Model\Config\Source\AttributesReactFilter;
use Magento\Catalog\Model\Category\DataProvider;
use Magento\Framework\Event\Manager as EventManager;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\SerializerInterface;
use Zend\Mail\Header\Subject;

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
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var EventManager
     */
    protected $eventManager;

    /**
     * DataProviderPlugin constructor.
     *
     * @param ReactStoreFrontFilters $reactStoreFrontFilters
     * @param AttributesReactFilter $attributesReactFilter
     * @param SerializerInterface $serializer
     * @param EventManager $eventManager
     */
    public function __construct(
        ReactStoreFrontFilters $reactStoreFrontFilters,
        AttributesReactFilter $attributesReactFilter,
        SerializerInterface $serializer,
        EventManager $eventManager
    ) {
        $this->reactStoreFrontFilters = $reactStoreFrontFilters;
        $this->attributesReactFilter = $attributesReactFilter;
        $this->serializer = $serializer;
        $this->eventManager = $eventManager;
    }

    /**
     * @param DataProvider $subject
     * @param $result
     *
     * @return mixed
     * @throws InputException
     */
    public function afterPrepareMeta(DataProvider $subject, $result)
    {
        $result['react_storefront_filters'] = [
            'children' => $this->prepareAttributesFields($subject)
        ];

        return $result;
    }

    /**
     * @param DataProvider $subject
     * @param $result
     *
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function afterGetData(DataProvider $subject, $result)
    {
        $category = $subject->getCurrentCategory();
        $categoryId = $category->getId();
        $filters = $category->getReactStorefrontFilters();

        if ($filters) {
            $filters = $this->serializer->unserialize($filters);
            foreach ($filters[AttributesReactFilter::FACETS] as $facets) {
                $result[$categoryId][$facets . self::REACT_STOREFRONT_FILTERS_SUFFIX][0] = AttributesReactFilter::FACETS;
            }
            foreach ($filters[AttributesReactFilter::STATS] as $stats) {
                if (!isset($result[$categoryId][$stats . self::REACT_STOREFRONT_FILTERS_SUFFIX])) {
                    $result[$categoryId][$stats . self::REACT_STOREFRONT_FILTERS_SUFFIX][0] = null;
                }
                $result[$categoryId][$stats . self::REACT_STOREFRONT_FILTERS_SUFFIX][1] = AttributesReactFilter::STATS;
            }
        }
        return $result;
    }

    /**
     * Prepare by category attributes
     *
     * @param Subject $subject
     * @return array
     * @throws InputException
     */
    protected function prepareAttributesFields(DataProvider $subject)
    {
        $res = [];

        $attributesList = new \stdClass();
        $attributesList->items = $this->reactStoreFrontFilters->getProductAttributes();

        $this->eventManager->dispatch(
            'mscatalog_magento2_adminhtml_plugin_category_attributes_prepare_attributes',
            ['attributes_list' => $attributesList, 'subject' => $subject]
        );

        foreach ($attributesList->items as $attribute) {
            $object = new \stdClass();
            $object->data = [
                'arguments' => [
                    'data' => [
                        'name' => $attribute->getAttributeCode() . self::REACT_STOREFRONT_FILTERS_SUFFIX,
                        'config' => [
                            'formElement' => 'checkboxset',
                            'visible' => true,
                            'required' => false,
                            'label' => $attribute->getAttributeCode() . ' (' . $attribute->getDefaultFrontendLabel() . ')',
                            'componentType' => 'field',
                            'multiple' => true,
                            'source' => 'module',
                            'options' => $this->attributesReactFilter->toOptionArray(),
                        ]
                    ]
                ]
            ];

            $this->eventManager->dispatch('mscatalog_magento2_adminhtml_plugin_category_attributes_prepare_before', ['resource' => $object, 'subject' => $subject]);

            if ($object->data) {
                $res[$attribute->getAttributeCode() . self::REACT_STOREFRONT_FILTERS_SUFFIX] = $object->data;
            }
        }

        return $res;
    }
}
