<?php

namespace G4NReact\MsCatalogMagento2\Controller\Adminhtml\Catalog;

use Magento\Backend\App\Action;
use G4NReact\MsCatalogMagento2\Model\Indexer\ProductIndexer;
use G4NReact\MsCatalogMagento2\Model\Indexer\CategoryIndexer;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class ReindexDocumentStorefrontDatabase
 * @package G4NReact\MsCatalogMagento2\Controller\Adminhtml\Catalog
 */
class ReindexDocumentStorefrontDatabase extends Action
{

    /**
     * @var CategoryIndexer
     */
    protected $categoryIndexer;

    /**
     * @var ProductIndexer
     */
    protected $productIndexer;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * ReindexDocumentStorefrontDatabase constructor.
     *
     * @param Action\Context $context
     * @param CategoryIndexer $categoryIndexer
     * @param ProductIndexer $productIndexer
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Action\Context $context,
        CategoryIndexer $categoryIndexer,
        ProductIndexer $productIndexer,
        StoreManagerInterface $storeManager
    )
    {
        $this->categoryIndexer = $categoryIndexer;
        $this->productIndexer = $productIndexer;
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $params = $this->_request->getParams();
        $objectType = $this->_request->getParam('object_type');
        $id = $this->_request->getParam('id');

        if ($id && $objectType) {
            try {
                switch ($objectType) {
                    case 'product' :
                        $this->productIndexer->run([$id], $this->storeManager->getStore());
                        break;
                    case 'category' :
                        $this->productIndexer->run([$id], $this->storeManager->getStore());
                        break;
                }

                $this->messageManager->addSuccessMessage('Successfully reindex document data in storefront database');
            } catch (\Exception $exception) {
                $this->messageManager->addErrorMessage('Something went wrong');
            }
        }
    }
}
