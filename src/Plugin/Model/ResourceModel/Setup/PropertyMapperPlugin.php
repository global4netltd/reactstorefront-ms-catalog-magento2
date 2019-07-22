<?php

namespace G4NReact\MsCatalogMagento2\Plugin\Model\ResourceModel\Setup;

use G4NReact\MsCatalogMagento2\Model\Attribute\SearchTerms;
use Magento\Catalog\Model\ResourceModel\Setup\PropertyMapper;
use Magento\Eav\Model\Entity\Setup\PropertyMapperAbstract;

/**
 * Class PropertyMapperPlugin
 * @package G4NReact\MsCatalogMagento2\Plugin\Model\ResourceModel\Setup
 */
class PropertyMapperPlugin extends PropertyMapper
{
    /**
     * @param PropertyMapper $subject
     * @param callable $proceed
     * @param array $input
     * @param $entityTypeId
     *
     * @return array
     */
    public function aroundMap(\Magento\Catalog\Model\ResourceModel\Setup\PropertyMapper $subject, callable $proceed, array $input, $entityTypeId)
    {
        $result = $proceed($input, $entityTypeId);
        $result = array_merge($result, $this->prepareReactStorefrontMap($input));
        return $result;
    }

    /**
     * @param array $input
     *
     * @return array
     */
    protected function prepareReactStorefrontMap(array $input): array
    {
        $map = [];
        $map[SearchTerms::FORCE_INDEXING_IN_REACT_STORE_FRONT] = $this->_getValue($input, SearchTerms::FORCE_INDEXING_IN_REACT_STORE_FRONT, 0);
        $map[SearchTerms::USE_IN_REACT_STORE_FRONT] = $this->_getValue($input, SearchTerms::USE_IN_REACT_STORE_FRONT, 0);

        return $map;
    }
}
