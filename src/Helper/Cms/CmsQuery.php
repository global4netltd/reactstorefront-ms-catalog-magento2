<?php

namespace G4NReact\MsCatalogMagento2\Helper\Cms;

use G4NReact\MsCatalog\Document\Field;
use G4NReact\MsCatalogMagento2\Helper\Query;
use G4NReact\MsCatalogMagento2\Helper\Cms\Field as HelperCmsField;
use G4NReact\MsCatalogMagento2\Helper\QueryHelperInterface;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Framework\App\Helper\Context;

/**
 * Class CmsQuery
 * @package G4NReact\MsCatalogMagento2\Helper\Cms
 */
class CmsQuery implements QueryHelperInterface
{

    /**
     * @var HelperCmsField
     */
    protected $helperCmsField;

    /**
     * CmsQuery constructor.
     *
     * @param HelperCmsField $helperCmsField
     */
    public function __construct(
        HelperCmsField $helperCmsField
    )
    {
        $this->helperCmsField = $helperCmsField;
    }

    /**
     * @param string $columnName
     * @param null $value
     *
     * @return Field
     */
    public function getFieldByColumnName(string $columnName, $value = null) : Field
    {
        $type = $this->helperCmsField->getFieldTypeByColumnName($columnName);

        return new Field(
            $columnName,
            $value,
            $type,
            false,
            HelperCmsField::getIsMultiValued($columnName, $value)
        );
    }
}
