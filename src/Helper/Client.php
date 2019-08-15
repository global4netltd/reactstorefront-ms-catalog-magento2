<?php

namespace G4NReact\MsCatalogMagento2\Helper;

use G4NReact\MsCatalog\Client\ClientFactory;
use G4NReact\MsCatalog\Document\Field;
use G4NReact\MsCatalogMagento2\Helper\Config as ConfigHelper;
use G4NReact\MsCatalogMagento2\Helper\Query as QueryHelper;

/**
 * Class Client
 * @package G4NReact\MsCatalogMagento2\Helper
 */
class Client
{
    /**
     * @var Config
     */
    protected $configHelper;

    /**
     * @var Query
     */
    protected $helperQuery;

    /**
     * AbstractIndexer constructor
     *
     * @param ConfigHelper $configHelper
     */
    public function __construct(
        ConfigHelper $configHelper,
        QueryHelper $helperQuery
    ) {
        $this->configHelper = $configHelper;
        $this->helperQuery = $helperQuery;
    }

    /**
     * @param $sku
     */
    public function deleteProductBySku($sku)
    {
        if ($sku) {
            return $this->getClient()->deleteByFields(
                [
                    new Field('object_type', 'product'),
                    $this->helperQuery->getFieldByAttributeCode('sku', $sku)
                ]
            );
        }

        return false;
    }

    /**
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getClient()
    {
        $config = $this->configHelper->getConfiguration();
        $client = ClientFactory::getInstance($config);

        return $client;
    }
}
