<?php

namespace G4NReact\MsCatalogMagento2\Config\Framework\Indexer;

use Magento\Framework\Module\Dir;
use Magento\Framework\Indexer\Config\SchemaLocator as IndexerSchemaLocator;
use Magento\Framework\Config\SchemaLocatorInterface;
use Magento\Framework\Config\Dom\UrnResolver;

/**
 * Class SchemaLocator
 * @package G4NReact\MsCatalogMagento2\Config\Framework\Indexer
 */
class SchemaLocator extends IndexerSchemaLocator implements SchemaLocatorInterface
{
    /**
     * @var string
     */
    protected $schema;

    /**
     * @var string
     */
    protected $perFileSchema;

    /**
     * SchemaLocator constructor.
     *
     * @param \Magento\Framework\Config\Dom\UrnResolver $urnResolver
     * @param Dir\Reader $moduleReader
     */
    public function __construct(
        UrnResolver $urnResolver,
        Dir\Reader $moduleReader
    )
    {
        $this->perFileSchema = $moduleReader->getModuleDir(Dir::MODULE_ETC_DIR, 'G4NReact_MsCatalogMagento2') . '/indexer.xsd';
        $this->schema = $moduleReader->getModuleDir(Dir::MODULE_ETC_DIR, 'G4NReact_MsCatalogMagento2') . '/indexer_merged.xsd';
        parent::__construct($urnResolver);
    }

    /**
     * {@inheritdoc}
     */
    public function getSchema()
    {
        return $this->schema;
    }

    /**
     * {@inheritdoc}
     */
    public function getPerFileSchema()
    {
        return $this->perFileSchema;
    }
}
