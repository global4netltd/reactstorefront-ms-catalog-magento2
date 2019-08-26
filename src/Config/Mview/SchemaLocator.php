<?php

namespace G4NReact\MsCatalogMagento2\Config\Mview;

use Magento\Framework\Module\Dir;
use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\Config\Dom\UrnResolver;

/**
 * Class SchemaLocator
 * @package G4NReact\MsCatalogMagento2\Config\Mview
 */
class SchemaLocator extends \Magento\Framework\Mview\Config\SchemaLocator
{
    /**
     * @var string
     */
    protected $schema;

    /**
     * SchemaLocator constructor.
     *
     * @param UrnResolver $urnResolver
     * @param Reader $reader
     */
    public function __construct(
        UrnResolver $urnResolver,
        Reader $reader
    )
    {
        $this->schema = $reader->getModuleDir(Dir::MODULE_ETC_DIR, 'G4NReact_MsCatalogMagento2') . '/mview.xsd';
        parent::__construct($urnResolver);
    }

    /**
     * @return null|string
     */
    public function getSchema()
    {
        if ($this->schema) {
            return $this->schema;
        }

        return parent::getSchema();
    }

    /**
     * @return null|string
     */
    public function getPerFileSchema()
    {
        if ($this->schema) {
            return $this->schema;
        }

        return parent::getPerFileSchema();
    }
}
