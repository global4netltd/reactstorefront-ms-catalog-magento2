<?php

namespace G4NReact\MsCatalogMagento2\Model\Config\Source;

/**
 * Class LogFormat
 * @package G4NReact\MsCatalogMagento2\Model\Config\Source
 */
class Engine implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [['value' => \G4NReact\MsCatalogIndexer\Indexer::ENGINE_SOLR, 'label' => __('Solr')]];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [\G4NReact\MsCatalogIndexer\Indexer::ENGINE_SOLR => __('SOLR')];
    }
}
