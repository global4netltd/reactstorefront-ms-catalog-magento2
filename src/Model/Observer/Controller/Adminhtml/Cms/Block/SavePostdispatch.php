<?php

namespace G4NReact\MsCatalogMagento2\Model\Observer\Controller\Adminhtml\Cms\Block;

use G4NReact\MsCatalogMagento2\Model\Indexer\CmsBlockIndexer;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class SavePostdispatch
 * @package G4NReact\MsCatalogMagento2\Model\Observer\Controller\Adminhtml\Cms\Block
 */
class SavePostdispatch implements ObserverInterface
{
    /**
     * @var CmsBlockIndexer
     */
    protected $cmsBlockIndexer;

    /**
     * SavePostdispatch constructor
     *
     * @param CmsBlockIndexer $cmsBlockIndexer
     */
    public function __construct(CmsBlockIndexer $cmsBlockIndexer)
    {
        $this->cmsBlockIndexer = $cmsBlockIndexer;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var RequestInterface $request */
        $request = $observer->getEvent()->getRequest();

        $cmsBlockId = $request->getParam('block_id', 0);

        if ($cmsBlockId) {
            $this->cmsBlockIndexer->run([$cmsBlockId]);
        }
    }
}
