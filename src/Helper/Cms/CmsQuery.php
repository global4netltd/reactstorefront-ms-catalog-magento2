<?php

namespace G4NReact\MsCatalogMagento2\Helper\Cms;

use G4NReact\MsCatalog\Document\Field;
use G4NReact\MsCatalogMagento2\Helper\Query;
use G4NReact\MsCatalogMagento2\Helper\Cms\Field as HelperCmsField;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Framework\App\Helper\Context;

/**
 * Class CmsQuery
 * @package G4NReact\MsCatalogMagento2\Helper\Cms
 */
class CmsQuery
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
    public function getFieldByCmsColumnName(string $columnName, $value = null)
    {
        $type = $this->helperCmsField->getFieldTypeByCmsColumnName($columnName);

        return new Field(
            $columnName,
            $value,
            $type,
            false,
            HelperCmsField::getIsCmsMultivalued($columnName, $value)
        );
    }
}
