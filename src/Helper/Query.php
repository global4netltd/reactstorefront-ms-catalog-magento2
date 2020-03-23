<?php

namespace G4NReact\MsCatalogMagento2\Helper;

use G4NReact\MsCatalog\Client\ClientFactory;
use G4NReact\MsCatalog\Document\Field;
use G4NReact\MsCatalog\FieldHelper;
use G4NReact\MsCatalog\Helper;
use G4NReact\MsCatalogMagento2\Helper\Cms\CmsBlockQuery;
use G4NReact\MsCatalogMagento2\Helper\Cms\CmsQuery;
use G4NReact\MsCatalogMagento2\Helper\Config as ConfigHelper;
use G4NReact\MsCatalogMagento2\Model\Attribute\SearchTerms;
use Magento\Catalog\Api\Data\CategoryAttributeInterface;
use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Eav\Model\ResourceModel\Entity\Attribute as AttributeResource;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Event\Manager as EventManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

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
        'text' => Field::FIELD_TYPE_STRING,
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
        ProductAttributeInterface::ENTITY_TYPE_CODE  => [
            'ids'           => [
                'type'        => Field::FIELD_TYPE_STATIC,
                'indexable'   => true,
                'multivalued' => false,
                'real_code'   => 'id'
            ],
            'entity_id'     => [
                'type'        => Field::FIELD_TYPE_INT,
                'indexable'   => true,
                'multivalued' => false,
            ],
            'skus'          => [
                'type'        => Field::FIELD_TYPE_STRING,
                'indexable'   => true,
                'multivalued' => false,
                'real_code'   => 'sku'
            ],
            'sku'          => [
                'type'        => Field::FIELD_TYPE_STRING,
                'indexable'   => true,
                'multivalued' => false,
            ],
            'store_id'      => [
                'type'        => Field::FIELD_TYPE_INT,
                'indexable'   => true,
                'multivalued' => false,
            ],
            'category_id'   => [
                'type'        => Field::FIELD_TYPE_INT,
                'indexable'   => true,
                'multivalued' => true,
            ],
            'request_path'  => [
                'type'        => Field::FIELD_TYPE_STRING,
                'indexable'   => true,
                'multivalued' => false,
            ],
            'final_price'   => [
                'type'        => Field::FIELD_TYPE_FLOAT,
                'indexable'   => true,
                'multivalued' => false,
            ],
            'media_gallery' => [
                'type'        => Field::FIELD_TYPE_TEXT,
                'indexable'   => false,
                'multivalued' => false,
            ],
            'url_key'       => [ // @ToDo: temporarily - upgrade attribute
                'type'        => Field::FIELD_TYPE_STRING,
                'indexable'   => true,
                'multivalued' => false,
            ],
            'url' =>[
                'type' => Field::FIELD_TYPE_STRING,
                'indexable' => true,
                'multivalued' => false
            ],
            'visibility'    => [
                'type' => Field::FIELD_TYPE_INT,
                'indexable' => true,
                'multivalued' => false
            ],
            'status'        => [
                'type' => Field::FIELD_TYPE_INT,
                'indexable' => true,
                'multivalued' => false
            ]
        ],
        CategoryAttributeInterface::ENTITY_TYPE_CODE => [
            'entity_id' => [
                'type'        => Field::FIELD_TYPE_INT,
                'indexable'   => true,
                'multivalued' => false,
            ],
            'store_id'  => [
                'type'        => Field::FIELD_TYPE_INT,
                'indexable'   => true,
                'multivalued' => false,
            ],
            'parent_id' => [
                'type'        => Field::FIELD_TYPE_INT,
                'indexable'   => true,
                'multivalued' => false,
            ],
            'url_key'   => [ // @ToDo: temporarily - upgrade attribute
                'type'        => Field::FIELD_TYPE_STRING,
                'indexable'   => true,
                'multivalued' => false,
            ],
            'url' =>[
                'type' => Field::FIELD_TYPE_STRING,
                'indexable' => true,
                'multivalued' => false
            ],
            'product_count'   => [
                'type'        => Field::FIELD_TYPE_INT,
                'indexable'   => true,
                'multivalued' => false,
            ],

        ]
    ];

    /**
     * @var array
     */
    public static $additionalMapping = [];

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
     * @var ConfigHelper
     */
    protected $configHelper;

    /**
     * @var CmsBlockQuery
     */
    protected $helperCmsBlockQuery;

    /**
     * @var EventManager
     */
    protected $eventManager;

    /**
     * Query constructor.
     *
     * @param EavConfig $eavConfig
     * @param Context $context
     * @param CmsQuery $cmsQuery
     * @param AttributeResource $attributeResource
     * @param Config $configHelper
     * @param CmsBlockQuery $helperCmsBlockQuery
     * @param EventManager $eventManager
     */
    public function __construct(
        EavConfig $eavConfig,
        Context $context,
        CmsQuery $cmsQuery,
        AttributeResource $attributeResource,
        ConfigHelper $configHelper,
        CmsBlockQuery $helperCmsBlockQuery,
        EventManager $eventManager
    ) {
        $this->configHelper = $configHelper;
        $this->eavConfig = $eavConfig;
        $this->cmsQuery = $cmsQuery;
        $this->attributeResource = $attributeResource;
        $this->configHelper = $configHelper;
        $this->helperCmsBlockQuery = $helperCmsBlockQuery;
        $this->eventManager = $eventManager;

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
            $attributeCodeToFieldTypeMap = $this->getAttributeCodeToFieldTypeMap($attribute->getEntityType()->getEntityTypeCode());
            $attributeType = $attributeCodeToFieldTypeMap[$attribute->getAttributeCode()]['type'] ?? Field::FIELD_TYPE_STATIC;
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
    public function getFieldByCmsPageColumnName(string $columnName, $value = null): Field
    {
        if ($coreField = $this->getCoreField($columnName, $value)) {
            return $coreField;
        }

        return $this->cmsQuery->getFieldByColumnName($columnName, $value);
    }

    /**
     * @param string $columnName
     * @param null $value
     *
     * @return Field
     */
    public function getFieldByCmsBlockColumnName(string $columnName, $value = null) : Field
    {
        if ($coreField = $this->getCoreField($columnName, $value)) {
            return $coreField;
        }

        return $this->helperCmsBlockQuery->getFieldByColumnName($columnName, $value);
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
        $start = microtime(true);
        $field = null;

        if ($coreField = $this->getCoreField($attributeCode, $value)) {
            $field = $coreField;
        }

        if (!$field) {
            $attributeCodeToFieldTypeMap = $this->getAttributeCodeToFieldTypeMap($entityType);
            if (in_array($attributeCode, array_keys($attributeCodeToFieldTypeMap))) {
                $newField = new Field(
                    $attributeCodeToFieldTypeMap[$attributeCode]['real_code'] ?? $attributeCode,
                    FieldHelper::shouldHandleValue($value, $attributeCodeToFieldTypeMap[$attributeCode]['type'])
                        ? FieldHelper::handleValue($value)
                        : $value,
                    $attributeCodeToFieldTypeMap[$attributeCode]['type'],
                    $attributeCodeToFieldTypeMap[$attributeCode]['indexable'],
                    $attributeCodeToFieldTypeMap[$attributeCode]['multivalued']
                );

                $field = $newField;
            }
        }

        if (!$field) {
            if (isset(self::$attributes[$entityType][$attributeCode])) {
                /** @var AbstractAttribute $attribute */
                $attribute = self::$attributes[$entityType][$attributeCode];
            } else {
                /** @var AbstractAttribute $attribute */
                $attribute = $this->eavConfig->getAttribute($entityType, $attributeCode);
                self::$attributes[$entityType][$attributeCode] = $attribute;
            }

            $isMultiValued = in_array($attribute->getFrontendInput(), self::$multiValuedAttributeFrontendInput);
            $fieldType = $isMultiValued ? Field::FIELD_TYPE_INT : $this->getAttributeFieldType($attribute);
            $isFieldIndexable = $attribute->getIsFilterable() || $attribute->getUsedForSortBy();

            $value = FieldHelper::shouldHandleValue($value, $fieldType) ? FieldHelper::handleValue($value) : $value;

            $field = new Field($attributeCode, $value, $fieldType, $isFieldIndexable, $isMultiValued);
            if ($attribute->getData(SearchTerms::FORCE_INDEXING_IN_REACT_STORE_FRONT)) {
                $field->setIndexable(true);
            }
        }

        $this->eventManager->dispatch('prepare_field_by_attribute_code_after', [
            'field' => $field, 'attribute_code' => $attributeCode, 'value' => $value, 'entity_type' => $entityType]);

        \G4NReact\MsCatalog\Profiler::increaseTimer(' ========> getFieldByAttributeCode', (microtime(true) - $start));

        return $field;
    }

    /**
     * @param array $attributeCodes
     * @return array
     * @throws LocalizedException
     */
    public function getFieldsByAttributeCodes(array $attributeCodes)
    {
        $fields = [];
        foreach ($attributeCodes as $attributeCode) {
            $fields[] = $this->getFieldByProductAttributeCode($attributeCode);
        }

        return $fields;
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
        $entityType = $attribute->getEntityType()->getEntityTypeCode();
        if($attribute->getFrontendInput() === 'multiselect'){
            $value = explode(',', $value);
        }

        return $this->getFieldByAttributeCode($attributeCode, $value, $entityType);
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

    /**
     * @param array $additionalMapping
     */
    public function setAdditionalMapping(array $additionalMapping)
    {
        self::$additionalMapping = $additionalMapping;
    }

    /**
     * @param string $entityType
     * @return array
     */
    public function getAttributeCodeToFieldTypeMap(string $entityType = ProductAttributeInterface::ENTITY_TYPE_CODE): array
    {
        $baseMapping = self::$mapAttributeCodeToFieldType[$entityType] ?? [];
        $additionalMapping = self::$additionalMapping[$entityType] ?? [];

        return array_merge($baseMapping, $additionalMapping);
    }

    /**
     * @param $storeId
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getCategoriesProductsCount($storeId)
    {
        $searchEngineConfig = $this->configHelper->getConfiguration();
        $searchEngineClient = ClientFactory::create($searchEngineConfig);
        $query = $searchEngineClient->getQuery();
        $query->addFilters([
            [$this->getFieldByAttributeCode(
                'store_id',
                $storeId
            )],
            [$this->getFieldByAttributeCode(
                'object_type',
                'product'
            )],
            [$this->getFieldByAttributeCode(
                'visibility',
                Visibility::VISIBILITY_BOTH
            )],
        ]);

        $query->addFacet(
            $this->getFieldByAttributeCode(
                'category_id',
                null,
                'catalog_product'
            )->setLimit(10000) // @todo change it to better value like maybe category count or sth
        );

        $query->setPageSize(0);

        if (isset($query->getResponse()->getFacets()['category_id'])) {
            return $query->getResponse()->getFacets()['category_id']->getValues() ?? [];
        }

        return [];
    }
}
