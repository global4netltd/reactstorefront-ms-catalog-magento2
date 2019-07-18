<?php

namespace G4NReact\MsCatalogMagento2\Helper;

use G4NReact\MsCatalog\Document\Field;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use \G4NReact\MsCatalogMagento2\Helper\Cms\Field as HelperCmsField;

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
        'store_id'    => [
            'type'        => 'int',
            'indexable'   => true,
            'multivalued' => false,
        ],
        'category_id' => [
            'type'        => 'int',
            'indexable'   => true,
            'multivalued' => true,
        ],
        'final_price' => [
            'type'        => 'float',
            'indexable'   => true,
            'multivalued' => false,
        ],
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
    )
    {
        $this->eavConfig = $eavConfig;
        parent::__construct($context);
    }

    /**
     * @param AbstractAttribute $attribute
     *
     * @return string
     */
    public function getAttributeFieldType(AbstractAttribute $attribute)
    {
        $attributeType = $attribute->getBackendType();

        if (!$attributeType || $attributeType === 'static') {
            $attributeType = self::$mapAttributeCodeToFieldType[$attribute->getAttributeCode()]['type'] ?? 'static';

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
     * @param null $value
     * @param string $entityType
     *
     * @return Field
     * @throws LocalizedException
     */
    public function getFieldByAttributeCode(string $attributeCode, $value = null, string $entityType = 'catalog_product'): Field
    {
//        if (isset(self::$fields[$entityType][$attributeCode])) {
//            /** @var Field $field */
//            $field = self::$fields[$entityType][$attributeCode];
//            $field->setValue($value);
//
//            return $field;
//        } @todo cache attributes

        if ($field = $this->getCoreField($attributeCode, $value)) {
            return $field;
        }

        if (in_array($attributeCode, array_keys(self::$mapAttributeCodeToFieldType))) {
            $field = new Field(
                $attributeCode,
                null,
                self::$mapAttributeCodeToFieldType[$attributeCode]['type'],
                self::$mapAttributeCodeToFieldType[$attributeCode]['indexable'],
                self::$mapAttributeCodeToFieldType[$attributeCode]['multivalued']
            );
            $field->setValue($value);

            return $field;
        }

        /** @var AbstractAttribute $attribute */
        $attribute = $this->eavConfig->getAttribute($entityType, $attributeCode);
        if ($entityType == HelperCmsField::OBJECT_TYPE && isset(HelperCmsField::$fieldTypeMap[$attributeCode])) {
            $fieldType = HelperCmsField::$fieldTypeMap[$attributeCode];
        } else {
            $fieldType = $this->getAttributeFieldType($attribute);
        }
        $isFieldIndexable = $attribute->getIsFilterable() ? true : false;
        $isMultiValued = in_array($attribute->getFrontendInput(), self::$multiValuedAttributeFrontendInput);

        $field = new Field($attributeCode, null, $fieldType, $isFieldIndexable, $isMultiValued);
        $field->setValue($value);

        return $field;
    }

    /**
     * @param AbstractAttribute $attribute
     * @param mixed $value
     *
     * @return Field
     */
    public function getFieldByAttribute(AbstractAttribute $attribute, $value = null): Field
    {
        $attributeCode = $attribute->getAttributeCode();
        $entityType = $attribute->getEntityType()->getEntityTypeCode();

        if (isset(self::$fields[$entityType][$attributeCode])) {
            /** @var Field $field */
            $field = self::$fields[$entityType][$attributeCode];
            $field->setValue($value);

            return $field;
        }

        if (in_array($attributeCode, \G4NReact\MsCatalog\Helper::$coreDocumentFieldsNames)) {
            $field = new Field($attributeCode, null, 'static', true, false);
            self::$fields[$entityType][$attributeCode] = $field;
            $field->setValue($value);

            return $field;
        }

        if (in_array($attributeCode, array_keys(self::$mapAttributeCodeToFieldType))) {
            $field = new Field(
                $attributeCode,
                null,
                self::$mapAttributeCodeToFieldType[$attributeCode]['type'],
                self::$mapAttributeCodeToFieldType[$attributeCode]['indexable'],
                self::$mapAttributeCodeToFieldType[$attributeCode]['multivalued']
            );
            self::$fields[$entityType][$attributeCode] = $field;
            $field->setValue($value);

            return $field;
        }

        $fieldType = $this->getAttributeFieldType($attribute);
        $isFieldIndexable = $attribute->getIsFilterable() ? true : false;
        $isFieldIndexable = $attribute->getForceIndexingInReactStorefront() ? true : $isFieldIndexable;
        $isMultiValued = in_array($attribute->getFrontendInput(), self::$multiValuedAttributeFrontendInput);

        $field = new Field($attributeCode, null, $fieldType, $isFieldIndexable, $isMultiValued);
        self::$fields[$entityType][$attributeCode] = $field;
        $field->setValue($value);

        return $field;
    }

    /**
     * @param string $attributeCode
     *
     * @return bool|Field
     */
    public function getCoreField(string $attributeCode, $value)
    {
        if (in_array($attributeCode, \G4NReact\MsCatalog\Helper::$coreDocumentFieldsNames)) {
            $field = new Field($attributeCode, null, 'static', true, false);
            $field->setValue($value);

            return $field;
        }

        return false;
    }
}
