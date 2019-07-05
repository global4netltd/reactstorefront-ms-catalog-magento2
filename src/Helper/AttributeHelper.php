<?php

namespace G4NReact\MsCatalogMagento2\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class MapAttributeType
 * @package G4NReact\MsCatalogMagento2\Helper
 */
class AttributeHelper
{
    /** @var string url fieldname */
    const URL_FIELDNAME = 'url';

    /** @var string path to product url suffix */
    const SEO_SUFIX_URL_PRODUCT_PATH = 'catalog/seo/product_url_suffix';

    /** @var string path to category url suffix */
    const SEO_SUFIX_URL_CATEGORY_PATH = 'catalog/seo/category_url_suffix';

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * AttributeHelper constructor.
     *
     * @param StoreManagerInterface
     * @param ScopeConfigInterface
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig
    )
    {
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @var array
     */
    protected static $mapBackendTypeByAttributeCode = [
        'level' => 'int',
        'entity_id' => 'int',
        'children_count' => 'int',
        'position' => 'int',
        'attribute_set_id' => 'int'
    ];

    /**
     * @param \Magento\Eav\Model\Entity\Attribute $attribute
     *
     * @return mixed
     */
    public function getAttributeBackendType($attribute)
    {
        if (isset(self::$mapBackendTypeByAttributeCode[$attribute->getAttributeCode()])) {
            return self::$mapBackendTypeByAttributeCode[$attribute->getAttributeCode()];
        } else {
            return $attribute->getBackendType();
        }
    }

    /**
     * @param $url
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function prepareFullUrlPath($url, $type)
    {
        $sufix = '';
        if (strpos($url, $this->getSufixUrlByType($type)) == false && $url) {
            $sufix = $this->getSufixUrlByType($type);
        }
        return $this->storeManager->getStore()->getBaseUrl() . $url . $sufix;
    }

    /**
     * @param \G4NReact\MsCatalog\Document $document
     * @param $url
     */
    public function addUrlField($document, $url)
    {
        if ($document && $url && !$document->getField(self::URL_FIELDNAME)) {
            $document->setField(
                self::URL_FIELDNAME,
                $this->prepareFullUrlPath($url, $document->getObjectType()),
                'string',
                true,
                false
            );
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSufixUrlProduct()
    {
        return $this->scopeConfig->getValue(
            self::SEO_SUFIX_URL_PRODUCT_PATH,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return mixed
     */
    public function getSufixUrlCategory()
    {
        return $this->scopeConfig->getValue(
            self::SEO_SUFIX_URL_CATEGORY_PATH,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param $type
     *
     * @return mixed|string
     */
    public function getSufixUrlByType($type)
    {
        switch ($type) {
            case 'product':
                return $this->getSufixUrlProduct();
            case 'category':
                return $this->getSufixUrlCategory();
            default:
                return '';
        }
    }

    /**
     * @param $urlPath
     *
     * @return string
     */
    public function prepareCategoryUrlPath($urlPath)
    {
        return '/' . $urlPath . $this->getSufixUrlCategory();
    }
}
