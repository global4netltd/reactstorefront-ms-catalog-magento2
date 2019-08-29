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
    
    /**
     * @var string force indexing in react storefront
     */
    const FORCE_INDEXING_IN_REACT_STORE_FRONT = 'force_indexing_in_react_storefront';

    /**
     * @var string search terms field name
     */
    const SEARCH_TERMS_FIELD_NAME = 'search_terms';

    /**
     * @var AttributeRepository
     */
    protected $attributeRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var array|null
     */
    public static $searchTerms = null;

    /**
     * @var array|null
     */
    public static $forceIndexingAttributes = null;

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
        // set to array -> we try to get search terms and force indexing flag only once
        if (self::$searchTerms === null) {
            self::$searchTerms = [];
        }
        if (self::$forceIndexingAttributes === null) {
            self::$forceIndexingAttributes = [];
        }

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

            if ((int)$attribute->getData(self::FORCE_INDEXING_IN_REACT_STORE_FRONT) == 1) {
                self::$forceIndexingAttributes[] = $attribute->getAttributeCode();
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
        if (self::$searchTerms === null) {
            $this->getAttributeSearchTerms();
        }

        if (isset(self::$searchTerms[$attributeCode])) {
            return (self::$searchTerms[$attributeCode] !== false)
                ? (self::SEARCH_TERMS_FIELD_NAME . '_' .self::$searchTerms[$attributeCode])
                : null;
        }

        return null;
    }

    /**
     * @ToDo: Move things like this to some more proper place than SearchTerms model
     * @return array
     * @throws InputException
     */
    public function getForceIndexingAttributes(): array
    {
        if (self::$forceIndexingAttributes === null) {
            $this->getAttributeSearchTerms();
        }

        return self::$forceIndexingAttributes ?: [];
    }
}
