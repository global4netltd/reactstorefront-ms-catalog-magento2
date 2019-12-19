<?php

namespace G4NReact\MsCatalogMagento2\Helper;

use Exception;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Catalog\Model\ResourceModel\Category\Collection as CategoryCollection;

/**
 * Class Facets
 * @package G4NReact\MsCatalogMagento2\Helper
 */
class Facets extends AbstractHelper
{
    /**
     * @var Query
     */
    protected $queryHelper;

    /**
     * @var CategoryCollectionFactory
     */
    protected $categoryCollectionFactory;

    /**
     * Facets constructor
     *
     * @param Context $context
     * @param CategoryRepository $categoryRepository
     * @param Query $queryHelper
     */
    public function __construct(
        Context $context,
        Query $queryHelper,
        CategoryCollectionFactory $categoryCollectionFactory
    )
    {
        $this->queryHelper = $queryHelper;
        $this->categoryCollectionFactory = $categoryCollectionFactory;

        parent::__construct($context);
    }

    /**
     * @param int|array $categoryId
     * @return array
     */
    public function getFacetFieldsByCategory($needleCategoryId)
    {
        $facetFields = [];
        $categoryIds = is_array($needleCategoryId) ? $needleCategoryId : [$needleCategoryId];
        $categories = $this->getCategories($categoryIds);
        /** @var Category $category */
        foreach ($categories as $category) {
            try {
                $filters = json_decode($category->getData('react_storefront_filters'), true);

                if (isset($filters['facets'])) {
                    foreach ($filters['facets'] as $facet) {
                        if ($field = $this->queryHelper->getFieldByAttributeCode($facet)) {
                            $facetFields[$field->getName()] = $field;
                        }
                    }
                }

            } catch (NoSuchEntityException $noSuchEntityException) {
                $this->_logger->error(
                    'g4n-react-ms-catalog-magento-helper-facets',
                    [
                        'exception' => 'Category with id: ' . $category->getId() . ' doesnt exist!'
                    ]
                );
            } catch (Exception $exception) {
                $this->_logger->error(
                    'g4n-react-ms-catalog-magento2',
                    [
                        'exception' => $exception->getMessage()
                    ]
                );
            }
        }

        return $facetFields;
    }

    /**
     * @param array $ids
     * @return CategoryCollection
     * @throws LocalizedException
     */
    protected function getCategories(array $ids)
    {
        return $this->categoryCollectionFactory->create()
            ->addIdFilter($ids)
            ->addAttributeToSelect('react_storefront_filters');
    }

    /**
     * @param array|int $categoryId
     * @return array
     */
    public function getStatsFieldsByCategory($needleCategoryId)
    {
        $facetFields = [];
        $categoryIds = is_array($needleCategoryId) ? $needleCategoryId : [$needleCategoryId];
        $categories = $this->getCategories($categoryIds);
        /** @var Category $category */
        foreach ($categories as $category) {
            try {
                $filters = json_decode($category->getData('react_storefront_filters'), true);

                if (isset($filters['stats'])) {
                    foreach ($filters['stats'] as $stat) {
                        if ($field = $this->queryHelper->getFieldByAttributeCode($stat)) {
                            $facetFields[$field->getName()] = $field;
                        }
                    }
                }

            } catch (NoSuchEntityException $noSuchEntityException) {
                $this->_logger->error(
                    'g4n-react-ms-catalog-magento-helper-facets',
                    [
                        'exception' => 'Category with id: ' . $category->getId() . ' doesnt exist!'
                    ]
                );
            } catch (Exception $exception) {
                $this->_logger->error(
                    'g4n-react-ms-catalog-magento2',
                    [
                        'exception' => $exception->getMessage()
                    ]
                );
            }
        }

        return $facetFields;
    }
}
