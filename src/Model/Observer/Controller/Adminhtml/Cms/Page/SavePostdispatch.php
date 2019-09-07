<?php

namespace G4NReact\MsCatalogMagento2\Model\Observer\Controller\Adminhtml\Cms\Page;

use G4NReact\MsCatalogMagento2\Model\Indexer\CmsPageIndexer;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class SavePostdispatch
 * @package G4NReact\MsCatalogMagento2\Model\Observer\Controller\Adminhtml\Cms\Page
 */
class SavePostdispatch implements ObserverInterface
{
    /**
     * @var CmsPageIndexer
     */
    protected $cmsPageIndexer;

    /**
     * SavePostdispatch constructor
     *
     * @param CmsPageIndexer $cmsPageIndexer
     */
    public function __construct(CmsPageIndexer $cmsPageIndexer)
    {
        $this->cmsPageIndexer = $cmsPageIndexer;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $cmsPageModel = $observer->getDataObject();

        if (!$cmsPageModel || !$cmsPageId = $cmsPageModel->getData('page_id')) {
            return;
        }

        $this->cmsPageIndexer->run([$cmsPageId]);
    }
}
