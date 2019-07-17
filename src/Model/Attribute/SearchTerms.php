<?php

namespace G4NReact\MsCatalogMagento2\Model\Attribute;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Eav\Model\AttributeRepository;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\InputException;

/**
 * Class SearchTerms
 * @package G4NReact\MsCatalogMagento2\Model\Attribute
 */
class SearchTerms
{
    /**
     * @var string use in react store front
     */
    const USE_IN_REACT_STORE_FRONT = 'use_in_react_storefront';

    /**
     * @var string attribute weight in react store front
     */
    const WEIGHT_REACT_STORE_FRONT = 'weight_react_store_front';
    
    /** @var string force indexing in react storefront */
    const FORCE_INDEXING_IN_REACT_STORE_FRONT = 'force_indexing_in_react_storefront';

    /**
     * @var AttributeRepository
     */
    protected $attributeRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var array
     */
    public static $searchTerms = [];

    /**
     * SearchTerms constructor
     *
     * @param AttributeRepository $attributeRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        AttributeRepository $attributeRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @return array
     * @throws InputException
     */
    public function getAttributeSearchTerms()
    {
        $getAttributeListStart = microtime(true);
        $attributes = $this->attributeRepository->getList(
            ProductAttributeInterface::ENTITY_TYPE_CODE,
            $this->searchCriteriaBuilder->create()
        );
        echo '$getAttributeList: ' . (round(microtime(true) - $getAttributeListStart, 4)) . 's' . PHP_EOL;

        $attributeWeights = [];
        /** @var Attribute $attribute */
        foreach ($attributes->getItems() as $attribute) {
            if ((int)$attribute->getData(self::WEIGHT_REACT_STORE_FRONT) > 0) {
                $attributeWeights[$attribute->getAttributeCode()] = (int)$attribute->getData(self::WEIGHT_REACT_STORE_FRONT);
            } else {
                $attributeWeights[$attribute->getAttributeCode()] = false;
            }
        }

        self::$searchTerms = $attributeWeights;

        return self::$searchTerms;
    }

    /**
     * @param $attributeCode
     * @return string|null
     * @throws InputException
     */
    public function prepareSearchTermField($attributeCode)
    {
        if (!self::$searchTerms) {
            $this->getAttributeSearchTerms();
        }

        if (isset(self::$searchTerms[$attributeCode])) {
            return (self::$searchTerms[$attributeCode] !== false)
                ? ('search_terms_' . self::$searchTerms[$attributeCode])
                : null;
        }

        return null;
    }
}
