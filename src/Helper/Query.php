<?php

namespace G4NReact\MsCatalogMagento2\Helper;

use G4NReact\MsCatalog\Document\Field;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Query
 * @package G4NReact\MsCatalogMagento2\Helper
 */
class Query extends AbstractHelper
{
    /**
     * @var array
     */
    public static $fields = [];

    /**
     * @var array
     */
    public static $multiValuedAttributeFrontendInput = [
        'select',
        'multiselect',
    ];

    /**
     * @var array
     */
    public static $mapFrontendInputToFieldType = [
        'boolean'     => 'boolean',
        'date'        => 'datetime',
        'gallery'     => 'string',
        'hidden'      => 'string',
        'image'       => 'string',
        'media_image' => 'string',
        'multiline'   => 'string',
        'multiselect' => 'int',
        'price'       => 'float',
        'select'      => 'int',
        'text'        => 'text',
        'textarea'    => 'string',
        'weight'      => 'float',
    ];

    /**
     * @var array
     */
    public static $mapAttributeCodeToFieldType = [
        'store_id' => 'int'
    ];

    /**
     * @var EavConfig
     */
    protected $eavConfig;

    /**
     * Query constructor
     *
     * @param EavConfig $eavConfig
     * @param Context $context
     */
    public function __construct(
        EavConfig $eavConfig,
        Context $context
    ) {
        $this->eavConfig = $eavConfig;
        parent::__construct($context);
    }

    /**
     * @param AbstractAttribute $attribute
     * @return string
     */
    public function getAttributeFieldType(AbstractAttribute $attribute)
    {
        $attributeType = $attribute->getBackendType();

        if (!$attributeType || $attributeType === 'static') {
            $attributeType = self::$mapAttributeCodeToFieldType[$attribute->getAttributeCode()] ?? 'static';

            if ($attributeType === 'static' && $attribute->getFlatColumns()) {
                $attributeType = $flatColumns[$attribute->getAttributeCode()]['type'] ?? 'static';
            }

            if ($attributeType === 'static' && $attribute->getFrontendInput()) {
                $attributeType = self::$mapFrontendInputToFieldType[$attribute->getFrontendInput()] ?? 'static';
            }
        }

        return $attributeType === 'static' ? 'string' : $attributeType;
    }

    /**
     * @param string $attributeCode
     * @param mixed $value
     * @param string $entityType
     * @return Field
     * @throws LocalizedException
     */
    public function getFieldByAttributeCode(string $attributeCode, $value, string $entityType = 'catalog_product'): Field
    {
        if (isset(self::$fields[$attributeCode])) {
            $field = self::$fields[$attributeCode];
            $field->setValue($value);
            return $field;
        }

        if (in_array($attributeCode, \G4NReact\MsCatalog\Helper::$coreDocumentFieldsNames)) {
            $field = new Field($attributeCode, null, 'static', true, false);
            self::$fields[$attributeCode] = $field;
            $field->setValue($value);

            return $field;
        }

        /** @var AbstractAttribute $attribute */
        $attribute = $this->eavConfig->getAttribute($entityType, $attributeCode);

        $fieldType = $this->getAttributeFieldType($attribute);
        $isFieldIndexable = $attribute->getIsFilterable() ? true : false;
        $isMultiValued = in_array($attribute->getFrontendInput(), self::$multiValuedAttributeFrontendInput);

        $field = new Field($attributeCode, null, $fieldType, $isFieldIndexable, $isMultiValued);
        self::$fields[$attributeCode] = $field;
        $field->setValue($value);

        return $field;
    }

    /**
     * @param AbstractAttribute $attribute
     * @param mixed $value
     * @return Field
     */
    public function getFieldByAttribute(AbstractAttribute $attribute, $value): Field
    {
        $attributeCode = $attribute->getAttributeCode();

        if (isset(self::$fields[$attributeCode])) {
            $field = self::$fields[$attributeCode];
            $field->setValue($value);
            return $field;
        }

        if (in_array($attributeCode, \G4NReact\MsCatalog\Helper::$coreDocumentFieldsNames)) {
            $field = new Field($attributeCode, null, 'static', true, false);
            self::$fields[$attributeCode] = $field;
            $field->setValue($value);

            return $field;
        }

        $fieldType = $this->getAttributeFieldType($attribute);
        $isFieldIndexable = $attribute->getIsFilterable() ? true : false;
        $isMultiValued = in_array($attribute->getFrontendInput(), self::$multiValuedAttributeFrontendInput);

        $field = new Field($attributeCode, null, $fieldType, $isFieldIndexable, $isMultiValued);
        self::$fields[$attributeCode] = $field;
        $field->setValue($value);

        return $field;
    }
}
