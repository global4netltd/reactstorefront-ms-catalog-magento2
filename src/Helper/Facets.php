<?php

namespace G4NReact\MsCatalogMagento2\Helper;

use Exception;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\NoSuchEntityException;

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
     * @var CategoryRepository
     */
    protected $categoryRepository;

    /**
     * Facets constructor
     *
     * @param Context $context
     * @param CategoryRepository $categoryRepository
     * @param Query $queryHelper
     */
    public function __construct(
        Context $context,
        CategoryRepository $categoryRepository,
        Query $queryHelper
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->queryHelper = $queryHelper;

        parent::__construct($context);
    }

    /**
     * @param $categoryId
     * @return array
     */
    public function getFacetFieldsByCategory(int $categoryId)
    {
        $facetFields = [];

        try {
            /** @var Category $category */
            $category = $this->categoryRepository->get($categoryId);
            $filters = json_decode($category->getData('react_storefront_filters'), true);

            if (isset($filters['facets'])) {
                foreach ($filters['facets'] as $facet) {
                    if ($field = $this->queryHelper->getFieldByAttributeCode($facet)) {
                        $facetFields[$field->getName()] = $field;
                    }
                }
            }

        } catch (NoSuchEntityException $noSuchEntityException){
            $this->_logger->error(
                'g4n-react-ms-catalog-magento-helper-facets',
                [
                    'exception' => 'Category with id: ' . $categoryId . ' doesnt exist!'
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

        return $facetFields;
    }

    /**
     * @param $categoryId
     * @return array
     */
    public function getStatsFieldsByCategory(int $categoryId)
    {
        $facetFields = [];

        try {
            /** @var Category $category */
            $category = $this->categoryRepository->get($categoryId);
            $filters = json_decode($category->getData('react_storefront_filters'), true);

            if (isset($filters['stats'])) {
                foreach ($filters['stats'] as $stat) {
                    if ($field = $this->queryHelper->getFieldByAttributeCode($stat)) {
                        $facetFields[$field->getName()] = $field;
                    }
                }
            }

        }
        catch (NoSuchEntityException $noSuchEntityException){
            $this->_logger->error(
                'g4n-react-ms-catalog-magento-helper-facets',
                [
                    'exception' => 'Category with id: ' . $categoryId . ' doesnt exist!'
                ]
            );
        }
        catch (Exception $exception) {
            $this->_logger->error(
                'g4n-react-ms-catalog-magento2',
                [
                    'exception' => $exception->getMessage()
                ]
            );
        }

        return $facetFields;
    }
}
