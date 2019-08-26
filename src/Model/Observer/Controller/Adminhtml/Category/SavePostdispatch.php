<?php

namespace G4NReact\MsCatalogMagento2\Model\Observer\Controller\Adminhtml\Category;

use G4NReact\MsCatalogMagento2\Model\Indexer\CategoryIndexer;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class SavePostdispatch
 * @package G4NReact\MsCatalogMagento2\Model\Observer\Controller\Adminhtml\Category
 */
class SavePostdispatch implements ObserverInterface
{
    /**
     * @var CategoryIndexer
     */
    protected $categoryIndexer;

    /**
     * SavePostdispatch constructor
     *
     * @param CategoryIndexer $categoryIndexer
     */
    public function __construct(CategoryIndexer $categoryIndexer)
    {
        $this->categoryIndexer = $categoryIndexer;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var RequestInterface $request */
        $request = $observer->getEvent()->getRequest();

        $categoryId = $request->getParam('entity_id', 0);
//        $storeId = $request->getParam('store_id', 0);

        if ($categoryId) {
            $this->categoryIndexer->run([$categoryId]);
        }
    }
}
