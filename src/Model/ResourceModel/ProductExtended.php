<?php

namespace G4NReact\MsCatalogMagento2\Model\ResourceModel;

use Magento\Catalog\Model\Factory;
use Magento\Catalog\Model\Indexer\Category\Product\TableMaintainer;
use Magento\Catalog\Model\Product\Attribute\DefaultAttributes;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Category;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product as MagentoProduct;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Eav\Model\Entity\Attribute\SetFactory;
use Magento\Eav\Model\Entity\Attribute\UniqueValidationInterface;
use Magento\Eav\Model\Entity\Context;
use Magento\Eav\Model\Entity\TypeFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DB\Select;
use Magento\Framework\DB\Sql\UnionExpression;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Product entity resource model
 */
class ProductExtended extends MagentoProduct
{
    /**
     * @var TableMaintainer
     */
    private $tableMaintainer;

    /**
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param Factory $modelFactory
     * @param CollectionFactory $categoryCollectionFactory
     * @param Category $catalogCategory
     * @param ManagerInterface $eventManager
     * @param SetFactory $setFactory
     * @param TypeFactory $typeFactory
     * @param DefaultAttributes $defaultAttributes
     * @param array $data
     * @param TableMaintainer|null $tableMaintainer
     * @param UniqueValidationInterface|null $uniqueValidator
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        Factory $modelFactory,
        CollectionFactory $categoryCollectionFactory,
        Category $catalogCategory,
        ManagerInterface $eventManager,
        SetFactory $setFactory,
        TypeFactory $typeFactory,
        DefaultAttributes $defaultAttributes,
        $data = [],
        TableMaintainer $tableMaintainer = null,
        UniqueValidationInterface $uniqueValidator = null
    ) {
        $this->tableMaintainer = $tableMaintainer ?: ObjectManager::getInstance()->get(TableMaintainer::class);

        parent::__construct(
            $context,
            $storeManager,
            $modelFactory,
            $categoryCollectionFactory,
            $catalogCategory,
            $eventManager,
            $setFactory,
            $typeFactory,
            $defaultAttributes,
            $data,
            $tableMaintainer,
            $uniqueValidator
        );
    }

    /**
     * Retrieve category ids where product is available
     *
     * @param $entityIds
     * @param ProductCollection $productCollection
     * @throws NoSuchEntityException
     */
    public function eagerLoadCategoriesWithParents($entityIds, $productCollection)
    {
        $unionTables[] = $this->getEagerLoadCategoriesWithParentsSelect(
            $entityIds,
            $this->tableMaintainer->getMainTable($this->_storeManager->getStore()->getId())
        );

        $unionSelect = new UnionExpression(
            $unionTables,
            Select::SQL_UNION_ALL
        );

        $preparedCategories = [];
        foreach ($this->getConnection()->fetchAll($unionSelect) as $data) {
            $preparedCategories[$data['product_id']][] = $data['category_id'];
        }

        foreach ($productCollection as $product) {
            $product->setCategoryIds($preparedCategories[$product->getId()] ?? []);
        }
    }

    /**
     * Returns DB select for available categories.
     *
     * @param int $entityIds
     * @param string $tableName
     * @return Select
     */
    private function getEagerLoadCategoriesWithParentsSelect($entityIds, $tableName)
    {
        return $this->getConnection()->select()->distinct()->from(
            $tableName,
            ['category_id', 'product_id']
        )->where(
            'product_id IN (?)',
            $entityIds
        )->where(
            'visibility != ?',
            Visibility::VISIBILITY_NOT_VISIBLE
        );
    }
}
