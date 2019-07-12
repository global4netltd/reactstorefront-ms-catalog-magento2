<?php

namespace G4NReact\MsCatalogMagento2\Helper;

use G4NReact\MsCatalog\Document\Field;
use G4NReact\MsCatalog\Document\FieldInterface;
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

        return $attributeType;
    }

    /**
     * @param FieldInterface $field
     * @param string $entityType
     * @return FieldInterface
     * @throws LocalizedException
     */
    public function getFieldByAttributeCode(FieldInterface $field, string $entityType = 'catalog_product'): FieldInterface
    {
        if (isset(self::$fields[$field->getName()])) {
            return self::$fields[$field->getName()];
        }

        if (in_array($field->getName(), \G4NReact\MsCatalog\Helper::$coreDocumentFieldsNames)) {
            $field->setType('static')->setIndexable(true)->setMultiValued(false);
            self::$fields[$field->getName()] = $field;

            return $field;
        }

        /** @var AbstractAttribute $attribute */
        $attribute = $this->eavConfig->getAttribute($entityType, $field->getName());

        $fieldType = $this->getAttributeFieldType($attribute);
        $isFieldIndexable = $attribute->getIsFilterable() ? true : false;
        $isMultiValued = in_array($attribute->getFrontendInput(), self::$multiValuedAttributeFrontendInput);

        $field->setType($fieldType)->setIndexable($isFieldIndexable)->setMultiValued($isMultiValued);
        self::$fields[$field->getName()] = $field;

        return $field;
    }
}
