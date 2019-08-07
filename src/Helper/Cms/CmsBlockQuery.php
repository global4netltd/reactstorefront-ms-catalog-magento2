<?php

namespace G4NReact\MsCatalogMagento2\Helper\Cms;

use G4NReact\MsCatalog\Document\Field;
use G4NReact\MsCatalogMagento2\Helper\QueryHelperInterface;
use G4NReact\MsCatalogMagento2\Helper\Cms\CmsBlockField as HelperCmsBlockField;

/**
 * Class CmsBlockQuery
 * @package G4NReact\MsCatalogMagento2\Helper\Cms
 */
class CmsBlockQuery implements QueryHelperInterface
{
    /**
     * @var CmsBlockField
     */
    protected $helperCmsBlockField;

    /**
     * CmsBlockQuery constructor.
     *
     * @param CmsBlockField $helperCmsBlockField
     */
    public function __construct(
        HelperCmsBlockField $helperCmsBlockField
    ) {
        $this->helperCmsBlockField = $helperCmsBlockField;
    }

    /**
     * @param string $columnName
     * @param null $value
     *
     * @return Field
     */
    public function getFieldByCmsColumnName(string $columnName, $value = null): Field
    {
        return new Field(
            $columnName,
            $value,
            $this->helperCmsBlockField->getFieldTypeByCmsColumnName($columnName),
            HelperCmsBlockField::getIsIndexable($columnName),
            HelperCmsBlockField::getIsMultiValued($columnName, $value)
        );
    }
}
