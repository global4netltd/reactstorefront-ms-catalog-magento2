<?php

namespace G4NReact\MsCatalogMagento2\Model\Attribute;

use Magento\Eav\Model\AttributeRepository;
use Magento\Framework\Api\SearchCriteriaBuilder;

/**
 * Class SearchTerms
 * @package G4NReact\MsCatalogMagento2\Model\Attribute
 */
class SearchTerms
{
    /** @var string use in react store front */
    const USE_IN_REACT_STORE_FRONT = 'use_in_react_store_front';
    
    /** @var string attribute weight in react store front */
    const WEIGHT_REACT_STORE_FRONT = 'weight_react_store_front';
    /**
     * @var \Magento\Eav\Model\AttributeRepository
     */
    protected $attributeRepository;
    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;
    /**
     * @var
     */
    protected $searchTerms;

    /**
     * SearchTerms constructor.
     *
     * @param \Magento\Eav\Model\AttributeRepository $attributeRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        AttributeRepository $attributeRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    )
    {
        $this->attributeRepository = $attributeRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\InputException
     */
    public function getAttributeSearchTerms()
    {
        if(!$this->searchTerms) {
            $attributes = $this->attributeRepository->getList(
                \Magento\Catalog\Api\Data\ProductAttributeInterface::ENTITY_TYPE_CODE,
                $this->searchCriteriaBuilder->create());

            $attr = [];
            /** @var \Magento\Eav\Model\Entity\Attribute $attribute */
            foreach ($attributes->getItems() as $attribute) {
                if ($weight = (int) $attribute->getData(self::WEIGHT_REACT_STORE_FRONT) > 0) {
                    $attr[$attribute->getAttributeCode()] = (int) $attribute->getData(self::WEIGHT_REACT_STORE_FRONT);
                }
            }

            $this->searchTerms = $attr;
        }
        return $this->searchTerms;
    }

    /**
     * @param $attributeCode
     *
     * @return bool|mixed
     */
    protected function checkIfAttributeCodeInSearchTerms($attributeCode)
    {
        if(isset($this->getAttributeSearchTerms()[$attributeCode])){
            return $this->getAttributeSearchTerms()[$attributeCode];
        }
        
        return false;
    }

    /**
     * @param $attributeCode
     *
     * @return bool|string
     */
    public function prepareSearchTermField($attributeCode)
    {
        if($attributeSearchTerm = $this->checkIfAttributeCodeInSearchTerms($attributeCode)){
            return 'search_terms_' . $attributeSearchTerm;
        }
        
        return false;
    }
}

