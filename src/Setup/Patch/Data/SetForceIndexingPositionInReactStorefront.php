<?php

namespace G4NReact\MsCatalogMagento2\Setup\Patch\Data;

use Magento\Catalog\Api\Data\CategoryAttributeInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Class SetForceIndexingInReactStorefront
 * @package G4NReact\MsCatalogMagento2\Setup\Patch\Data
 */
class SetForceIndexingPositionInReactStorefront implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    protected $moduleDataSetup;

    /**
     * @var EavSetupFactory
     */
    protected $eavSetupFactory;

    /**
     * SetForceIndexingInReactStorefront constructor
     *
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory
    )
    {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
    }​

    /**
     * @return DataPatchInterface|void
     */
    public function apply()
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $eavSetup->updateAttribute(
            CategoryAttributeInterface::ENTITY_TYPE_CODE,
            'position',
            'force_indexing_in_react_storefront',
            true
        );
    }​

    /**
     * @return array|string[]
     */
    public static function getDependencies()
    {
        return [];
    }​

    /**
     * @return array|string[]
     */
    public function getAliases()
    {
        return [];
    }
}