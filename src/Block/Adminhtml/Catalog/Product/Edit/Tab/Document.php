<?php

namespace G4NReact\MsCatalogMagento2\Block\Adminhtml\Catalog\Product\Edit\Tab;

use G4NReact\MsCatalogMagento2\Block\Adminhtml\Catalog\AbstractEditTabDocumentStorefront;

/**
 * Class Document
 * @package G4NReact\MsCatalogMagento2\Block\Adminhtml\Catalog\Product\Edit\Tab
 */
class Document extends AbstractEditTabDocumentStorefront
{
    /**
     * @return string
     */
    public function getObjectType()
    {
        return 'product';
    }
}
