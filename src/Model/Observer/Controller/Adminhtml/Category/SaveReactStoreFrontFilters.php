<?php

namespace G4NReact\MsCatalogMagento2\Model\Observer\Controller\Adminhtml\Category;

use G4NReact\MsCatalogMagento2\Model\Attribute\ReactStoreFrontFilters;
use G4NReact\MsCatalogMagento2\Plugin\Model\Category\DataProviderPlugin;
use Magento\Catalog\Model\Category;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class SaveReactStoreFrontFilters
 * @package G4NReact\MsCatalogMagento2\Model\Observer\Controller\Adminhtml\Category
 */
class SaveReactStoreFrontFilters implements ObserverInterface
{
    /**
     * @var ReactStoreFrontFilters
     */
    protected $reactStoreFrontFilters;

    /**
     * SaveReactStoreFrontFilters constructor.
     *
     * @param ReactStoreFrontFilters $reactStoreFrontFilters
     */
    public function __construct(
        ReactStoreFrontFilters $reactStoreFrontFilters
    ) {
        $this->reactStoreFrontFilters = $reactStoreFrontFilters;
    }

    /**
     * @param Observer $observer
     *
     * @return Category|void
     */
    public function execute(Observer $observer)
    {
        /** @var Category $category */
        $category = $observer->getCategory();

        /** @var RequestInterface $request */
        $request = $observer->getRequest();
        $post = $request->getPostValue();

        $filters = $this->getReactStoreFrontFiltersFromPost($post);

        if($filters){
            $category = $this->reactStoreFrontFilters->saveReactStoreFrontFiltersInCategory($category, $filters);
        }

        return $category;
    }

    /**
     * @param $post
     *
     * @return array
     */
    protected function getReactStoreFrontFiltersFromPost($post)
    {
        $filters = [];
        foreach ($post as $key => $field){
            if(strpos($key, DataProviderPlugin::REACT_STOREFRONT_FILTERS_SUFFIX) !== false){
                $filters[str_replace(DataProviderPlugin::REACT_STOREFRONT_FILTERS_SUFFIX, '', $key)] = $field;
            }
        }

        return $filters;
    }
}
