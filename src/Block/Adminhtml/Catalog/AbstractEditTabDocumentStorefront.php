<?php

namespace G4NReact\MsCatalogMagento2\Block\Adminhtml\Catalog;

use G4NReact\MsCatalog\Client\ClientFactory;
use Magento\Framework\View\Element\Template;
use G4NReact\MsCatalogMagento2\Helper\Config;
use G4NReact\MsCatalogMagento2\Helper\Query;
use agento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class AbstractEditTabDocument
 * @package G4NReact\MsCatalogMagento2\Block\Adminhtml\Catalog
 */
abstract class AbstractEditTabDocumentStorefront extends Template
{
    /** @var string template path */
    protected $_template = 'edit/documentStorefront.phtml';
    
    /**
     * @var Config
     */
    protected $configHelper;

    /**
     * @var Query
     */
    protected $helperQuery;

    /**
     * AbstractEditTabDocument constructor.
     *
     * @param Template\Context $context
     * @param Config $config
     * @param Query $helperQuery
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Config $config,
        Query $helperQuery,
        array $data = []
    ) {
        $this->configHelper = $config;
        $this->helperQuery = $helperQuery;
        parent::__construct($context, $data);
    }

    /**
     * @return array|\G4NReact\MsCatalog\Document[]|\G4NReact\MsCatalog\ResponseInterface
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getDocument()
    {
        $client = ClientFactory::create($this->configHelper->getConfiguration());
        $query = $client->getQuery();

        $query->addFilters(
            [
                [$this->helperQuery->getFieldByAttributeCode('id', $this->getId())],
                [$this->helperQuery->getFieldByAttributeCode('object_type', $this->getObjectType())]
            ]
        );

        $result = $query->getResponse();

        $result = $result->getNumFound() ? $result->getDocumentsCollection() : [];
        return $result;
    }

    /**
     * @return int
     */
    public function getId() : int
    {
        return (int) $this->_request->getParam('id');
    }

    /**
     * @return mixed
     */
    abstract public function getObjectType();
}
