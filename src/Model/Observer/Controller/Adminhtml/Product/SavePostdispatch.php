<?php

namespace G4NReact\MsCatalogMagento2\Model\Observer\Controller\Adminhtml\Product;

use G4NReact\MsCatalogMagento2\Model\Indexer\ProductIndexer;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class SavePostdispatch
 * @package G4NReact\MsCatalogMagento2\Model\Observer\Controller\Adminhtml\Product
 */
class SavePostdispatch implements ObserverInterface
{
    /**
     * @var ProductIndexer
     */
    protected $productIndexer;

    /**
     * SavePostdispatch constructor
     *
     * @param ProductIndexer $productIndexer
     */
    public function __construct(ProductIndexer $productIndexer)
    {
        $this->productIndexer = $productIndexer;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var RequestInterface $request */
        $request = $observer->getEvent()->getRequest();

        $productId = $request->getParam('id', 0);
//        $storeId = $request->getParam('store', 0);

        if ($productId) {
            $this->productIndexer->run([$productId]);
        }
    }
}
