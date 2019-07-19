<?php

namespace G4NReact\MsCatalogMagento2\Helper;

use G4NReact\MsCatalog\Document\Field;
use G4NReact\MsCatalog\Helper;
use G4NReact\MsCatalogMagento2\Helper\Cms\CmsQuery;
use G4NReact\MsCatalogMagento2\Model\Attribute\SearchTerms;
use Magento\Catalog\Api\Data\CategoryAttributeInterface;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Eav\Model\ResourceModel\Entity\Attribute as AttributeResource;
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
    public static $attributes = [];

    /**
     * @var array
     */
    public static $staticAttributesTypes = [];

    /**
     * @var array
     */
    public static $multiValuedAttributeFrontendInput = [
        'multiselect',
    ];

    /**
     * @var array
     */
    public static $mapBackendTypeToFieldType = [
        'text'      => Field::FIELD_TYPE_STRING,
    ];

    /**
     * @var array
     */
    public static $mapFrontendInputToFieldType = [
        'boolean'     => Field::FIELD_TYPE_BOOL,
        'date'        => Field::FIELD_TYPE_DATETIME,
        'gallery'     => Field::FIELD_TYPE_TEXT,
        'hidden'      => Field::FIELD_TYPE_STRING,
        'image'       => Field::FIELD_TYPE_STRING,
        'media_image' => Field::FIELD_TYPE_STRING,
        'multiline'   => Field::FIELD_TYPE_TEXT,
        'multiselect' => Field::FIELD_TYPE_INT,
        'price'       => Field::FIELD_TYPE_FLOAT,
        'select'      => Field::FIELD_TYPE_INT,
        'text'        => Field::FIELD_TYPE_STRING,
        'textarea'    => Field::FIELD_TYPE_TEXT,
        'weight'      => Field::FIELD_TYPE_FLOAT,
    ];

    /**
     * @var array
     */
    public static $normalizeFieldType = [
        'smallint'  => Field::FIELD_TYPE_INT,
        'integer'   => Field::FIELD_TYPE_INT,
        'bool'      => Field::FIELD_TYPE_BOOL,
        'timestamp' => Field::FIELD_TYPE_DATETIME,
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
            'type'        => Field::FIELD_TYPE_STRING,
            'indexable'   => true,
            'multivalued' => false,
            'real_code'   => 'sku'
        ],
        'store_id'    => [
            'type'        => Field::FIELD_TYPE_INT,
            'indexable'   => true,
            'multivalued' => false,
        ],
        'category_id' => [
            'type'        => Field::FIELD_TYPE_INT,
            'indexable'   => true,
            'multivalued' => true,
        ],
        'final_price' => [
            'type'        => Field::FIELD_TYPE_FLOAT,
            'indexable'   => true,
            'multivalued' => false,
        ],
    ];

    /**
     * @var EavConfig
     */
    protected $eavConfig;

    /**
     * @var CmsQuery
     */
    protected $cmsQuery;

    /**
     * @var AttributeResource;
     */
    protected $attributeResource;

    /**
     * Query constructor
     *
     * @param EavConfig $eavConfig
     * @param Context $context
     * @param CmsQuery $cmsQuery
     * @param AttributeResource $attributeResource
     */
    public function __construct(
        EavConfig $eavConfig,
        Context $context,
        CmsQuery $cmsQuery,
        AttributeResource $attributeResource
    ) {
        $this->eavConfig = $eavConfig;
        $this->cmsQuery = $cmsQuery;
        $this->attributeResource = $attributeResource;

        parent::__construct($context);
    }

    /**
     * @param AbstractAttribute $attribute
     * @return string
     * @throws LocalizedException
     */
    public function getAttributeFieldType(AbstractAttribute $attribute)
    {
        $attributeType = self::$mapBackendTypeToFieldType[$attribute->getBackendType()] ?? $attribute->getBackendType();

        if (!$attributeType || $attributeType === Field::FIELD_TYPE_STATIC) {
            $attributeType = self::$mapAttributeCodeToFieldType[$attribute->getAttributeCode()]['type'] ?? Field::FIELD_TYPE_STATIC;
            if ($attributeType === Field::FIELD_TYPE_STATIC) {
                $attributeType = $this->getStaticAttributeType($attribute);
            }

            if ($attributeType === Field::FIELD_TYPE_STATIC && $attribute->getFrontendInput()) {
                $attributeType = self::$mapFrontendInputToFieldType[$attribute->getFrontendInput()] ?? Field::FIELD_TYPE_STATIC;
            }
        }

        $attributeType = self::$normalizeFieldType[$attributeType] ?? $attributeType;

        return $attributeType === Field::FIELD_TYPE_STATIC ? Field::FIELD_TYPE_STRING : $attributeType;
    }

    /**
     * @param string $attributeCode
     * @param mixed|null $value
     * @return Field
     * @throws LocalizedException
     */
    public function getFieldByCategoryAttributeCode(string $attributeCode, $value = null): Field
    {
        return $this->getFieldByAttributeCode(
            $attributeCode,
            $value,
            CategoryAttributeInterface::ENTITY_TYPE_CODE
        );
    }

    /**
     * @param string $attributeCode
     * @param mixed|null $value
     * @return Field
     * @throws LocalizedException
     */
    public function getFieldByProductAttributeCode(string $attributeCode, $value = null): Field
    {
        return $this->getFieldByAttributeCode(
            $attributeCode,
            $value,
            ProductAttributeInterface::ENTITY_TYPE_CODE
        );
    }

    /**
     * @param string $columnName
     * @param null $value
     * @return Field
     */
    public function getFieldByCmsPageColumnName(string $columnName, $value = null) : Field
    {
        if ($coreField = $this->getCoreField($columnName, $value)) {
            return $coreField;
        }

        return $this->cmsQuery->getFieldByCmsColumnName($columnName, $value);
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
        $entityType = ProductAttributeInterface::ENTITY_TYPE_CODE
    ): Field {
        if ($field = $this->getCoreField($attributeCode, $value)) {
            return $field;
        }

        if (in_array($attributeCode, array_keys(self::$mapAttributeCodeToFieldType))) {
            $field = new Field(
                self::$mapAttributeCodeToFieldType[$attributeCode]['real_code'] ?? $attributeCode,
                $value,
                self::$mapAttributeCodeToFieldType[$attributeCode]['type'],
                self::$mapAttributeCodeToFieldType[$attributeCode]['indexable'],
                self::$mapAttributeCodeToFieldType[$attributeCode]['multivalued']
            );

            return $field;
        }

        if (isset(self::$attributes[$entityType][$attributeCode])) {
            /** @var AbstractAttribute $attribute */
            $attribute = self::$attributes[$entityType][$attributeCode];
        } else {
            /** @var AbstractAttribute $attribute */
            $attribute = $this->eavConfig->getAttribute($entityType, $attributeCode);
            self::$attributes[$entityType][$attributeCode] = $attribute;
        }

        $fieldType = $this->getAttributeFieldType($attribute);
        $isFieldIndexable = $attribute->getIsFilterable() ? true : false;
        $isMultiValued = in_array($attribute->getFrontendInput(), self::$multiValuedAttributeFrontendInput);

        $field = new Field($attributeCode, $value, $fieldType, $isFieldIndexable, $isMultiValued);
        if ($attribute->getData(SearchTerms::FORCE_INDEXING_IN_REACT_STORE_FRONT)) {
            $field->setIndexable(true);
        }

        return $field;
    }

    /**
     * @param AbstractAttribute $attribute
     * @param mixed $value
     * @return Field
     * @throws LocalizedException
     */
    public function getFieldByAttribute(AbstractAttribute $attribute, $value = null): Field
    {
        $attributeCode = $attribute->getAttributeCode();

        if (in_array($attributeCode, Helper::$coreDocumentFieldsNames)) {
            $field = new Field($attributeCode, $value, Field::FIELD_TYPE_STATIC, true, false);

            return $field;
        }

        if (in_array($attributeCode, array_keys(self::$mapAttributeCodeToFieldType))) {
            $field = new Field(
                $attributeCode,
                $value,
                self::$mapAttributeCodeToFieldType[$attributeCode]['type'],
                self::$mapAttributeCodeToFieldType[$attributeCode]['indexable'],
                self::$mapAttributeCodeToFieldType[$attributeCode]['multivalued']
            );

            return $field;
        }

        $fieldType = $this->getAttributeFieldType($attribute);
        $isFieldIndexable = $attribute->getIsFilterable() ? true : false;
        $isFieldIndexable = $attribute->getForceIndexingInReactStorefront() ? true : $isFieldIndexable;
        $isMultiValued = in_array($attribute->getFrontendInput(), self::$multiValuedAttributeFrontendInput);

        $field = new Field($attributeCode, $value, $fieldType, $isFieldIndexable, $isMultiValued);

        return $field;
    }

    /**
     * @param string $attributeCode
     * @param mixed|null $value
     * @return bool|Field
     */
    public function getCoreField(string $attributeCode, $value = null)
    {
        if (in_array($attributeCode, Helper::$coreDocumentFieldsNames)) {
            $field = new Field($attributeCode, $value, Field::FIELD_TYPE_STATIC, true, false);

            return $field;
        }

        return false;
    }

    /**
     * @param AbstractAttribute $attribute
     * @return string
     * @throws LocalizedException
     */
    public function getStaticAttributeType(AbstractAttribute $attribute): string
    {
        $attributeCode = $attribute->getAttributeCode();
        $attributeEntityType = $attribute->getEntityType()->getEntityTypeCode();

        if (isset(self::$staticAttributesTypes[$attributeEntityType][$attributeCode])) {
            return self::$staticAttributesTypes[$attributeEntityType][$attributeCode];
        }

        $attributeType = Field::FIELD_TYPE_STATIC;

        $describe = $this->attributeResource->describeTable($attribute->getBackend()->getTable());
        if (!isset($describe[$attributeCode])) {
            return $attributeType;
        }

        $prop = $describe[$attributeCode];
        $attributeType = $prop['DATA_TYPE'] ?? Field::FIELD_TYPE_STATIC;

        self::$staticAttributesTypes[$attributeEntityType][$attributeCode] = $attributeType;

        return $attributeType;
    }
}
