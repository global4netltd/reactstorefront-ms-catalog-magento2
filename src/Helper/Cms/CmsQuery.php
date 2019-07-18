<?php

namespace G4NReact\MsCatalogMagento2\Helper\Cms;

use G4NReact\MsCatalog\Document\Field;
use G4NReact\MsCatalogMagento2\Helper\BaseQuery;
use G4NReact\MsCatalogMagento2\Helper\Cms\Field as HelperCmsField;

/**
 * Class CmsQuery
 * @package G4NReact\MsCatalogMagento2\Helper\Cms
 */
class CmsQuery extends BaseQuery
{
    /**
     * @param string $attributeCode
     * @param null $value
     * @param string $entityType
     *
     * @return Field
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getFieldByAttributeCode(string $attributeCode, $value = null, string $entityType = 'catalog_product'): Field
    {
        if($field = $this->getCoreField($attributeCode, $value)){
            return $field;
        }

        /** @var AbstractAttribute $attribute */
        $attribute = $this->eavConfig->getAttribute($entityType, $attributeCode);
        if(isset(HelperCmsField::$fieldTypeMap[$attributeCode])){
            $fieldType = HelperCmsField::$fieldTypeMap[$attributeCode];
        }else {
            $fieldType = $this->getAttributeFieldType($attribute);
        }
        $isFieldIndexable = $attribute->getIsFilterable() ? true : false;
        $isMultiValued = in_array($attribute->getFrontendInput(), self::$multiValuedAttributeFrontendInput);

        $field = new Field($attributeCode, null, $fieldType, $isFieldIndexable, $isMultiValued);
        $field->setValue($value);

        return $field;
    }
}
