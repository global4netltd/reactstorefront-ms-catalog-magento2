<?php

namespace G4NReact\MsCatalogMagento2\Helper;

use G4NReact\MsCatalog\Document\Field;
use G4NReact\MsCatalog\Helper;
use G4NReact\MsCatalogMagento2\Helper\Cms\Field as HelperCmsField;
use G4NReact\MsCatalogMagento2\Model\Attribute\SearchTerms;
use Magento\Catalog\Api\Data\CategoryAttributeInterface;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
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
        'multiselect',
    ];

    /**
     * @var array
     */
    public static $mapFrontendInputToFieldType = [
        'boolean'     => 'boolean',
        'date'        => 'datetime',
        'gallery'     => 'text',
        'hidden'      => 'string',
        'image'       => 'string',
        'media_image' => 'string',
        'multiline'   => 'text',
        'multiselect' => 'int',
        'price'       => 'float',
        'select'      => 'int',
        'text'        => 'string',
        'textarea'    => 'text',
        'weight'      => 'float',
    ];

    /**
     * @var array
     */
    public static $normalizeFieldType = [
        'smallint'  => 'int',
        'integer'   => 'int',
        'bool'      => 'boolean',
        'timestamp' => 'datetime',
    ];

    /**
     * @var array
     */
    public static $mapAttributeCodeToFieldType = [
        'ids'         => [
            'type'        => Field::FIELD_TYPE_STATIC,
            'indexable'   => true,
            'multivalued' => false,
            'real_code'   => 'id'
        ],
        'skus'         => [
            'type'        => Field::FIELD_TYPE_TEXT, // @todo change to string
            'indexable'   => true,
            'multivalued' => false,
            'real_code'   => 'sku'
        ],
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
            $attributeType = self::$mapAttributeCodeToFieldType[$attribute->getAttributeCode()]['type'] ?? 'static';

            if ($attributeType === 'static' && $attribute->getFlatColumns()) {
                $flatColumns = $attribute->getFlatColumns();
                $attributeType = $flatColumns[$attribute->getAttributeCode()]['type'] ?? 'static';
            }

            if ($attributeType === 'static' && $attribute->getFrontendInput()) {
                $attributeType = self::$mapFrontendInputToFieldType[$attribute->getFrontendInput()] ?? 'static';
            }
        }

        $attributeType = self::$normalizeFieldType[$attributeType] ?? $attributeType;

        return $attributeType === 'static' ? 'string' : $attributeType;
    }

    /**
     * @param string $attributeCode
     * @param mixed|null $value
     * @return Field
     * @throws LocalizedException
     */
    public function getFieldByCategoryAttributeCode(string $attributeCode, $value = null): Field
    {
        return $this->getFieldByAttributeCode($attributeCode, $value, CategoryAttributeInterface::ENTITY_TYPE_CODE);
    }

    /**
     * @param string $attributeCode
     * @param mixed|null $value
     * @return Field
     * @throws LocalizedException
     */
    public function getFieldByProductAttributeCode(string $attributeCode, $value = null): Field
    {
        return $this->getFieldByAttributeCode($attributeCode, $value, ProductAttributeInterface::ENTITY_TYPE_CODE);
    }

    /**
     * @param string $attributeCode
     * @param mixed|null $value
     * @param string $entityType
     * @return Field
     * @throws LocalizedException
     */
    public function getFieldByAttributeCode(
        string $attributeCode,
        $value = null,
        string $entityType = ProductAttributeInterface::ENTITY_TYPE_CODE
    ): Field {
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
                self::$mapAttributeCodeToFieldType[$attributeCode]['real_code'] ?? $attributeCode,
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

        if ($attribute->getData(SearchTerms::FORCE_INDEXING_IN_REACT_STORE_FRONT)) {
            $field->setIndexable(true);
        }

        return $field;
    }

    /**
     * @param AbstractAttribute $attribute
     * @param mixed $value
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

        if (in_array($attributeCode, Helper::$coreDocumentFieldsNames)) {
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
     * @return bool|Field
     */
    public function getCoreField(string $attributeCode, $value)
    {
        if (in_array($attributeCode, Helper::$coreDocumentFieldsNames)) {
            $field = new Field($attributeCode, null, 'static', true, false);
            $field->setValue($value);

            return $field;
        }

        return false;
    }
}
