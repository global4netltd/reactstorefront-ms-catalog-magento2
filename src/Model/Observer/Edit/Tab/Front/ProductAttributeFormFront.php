<?php

namespace G4NReact\MsCatalogMagento2\Model\Observer\Edit\Tab\Front;

use G4NReact\MsCatalogMagento2\Model\Attribute\SearchTerms;
use G4NReact\MsCatalogMagento2\Model\Config\Source\AttributeWeight;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Config\Model\Config\Source\Yesno;

/**
 * Class ProductAttributeFormFront
 * @package G4NReact\MsCatalogMagento2\Model\Observer\Edit\Tab\Front
 */
class ProductAttributeFormFront implements ObserverInterface
{
    /**
     * @var \Magento\Config\Model\Config\Source\Yesno
     */
    protected $configSourceYesNo;

    /**
     * @var \G4NReact\MsCatalogMagento2\Model\Config\Source\AttributeWeight
     */
    protected $configAttributeWeight;

    /**
     * ProductAttributeFormFront constructor.
     *
     * @param \Magento\Config\Model\Config\Source\Yesno $configSourceYesNo
     * @param \G4NReact\MsCatalogMagento2\Model\Config\Source\AttributeWeight $configAttributeWeight
     */
    public function __construct(
        Yesno $configSourceYesNo,
        AttributeWeight $configAttributeWeight
    )
    {
        $this->configSourceYesNo = $configSourceYesNo;
        $this->configAttributeWeight = $configAttributeWeight;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $form = $observer->getForm();

        $fieldset = $form->getElement('front_fieldset');

        $fieldset->addField(
            SearchTerms::USE_IN_REACT_STORE_FRONT,
            'select',
            [
                'name' => SearchTerms::USE_IN_REACT_STORE_FRONT,
                'label' => __('Use in React Store Front'),
                'title' => __('Use in React Store Front'),
                'values' => $this->configSourceYesNo->toOptionArray(),
                'note' => __('Add attribute as field to React Storefront search engine')
            ]
        );

        $fieldset->addField(
            SearchTerms::WEIGHT_REACT_STORE_FRONT,
            'select',
            [
                'name' => SearchTerms::WEIGHT_REACT_STORE_FRONT,
                'label' => __('Attribute weight React Store Front'),
                'title' => __('Attribute weight React Store Front'),
                'values' => $this->configAttributeWeight->toOptionArray(),
            ]
        );

        $fieldset->addField(
            SearchTerms::FORCE_INDEXING_IN_REACT_STORE_FRONT,
            'select',
            [
                'name' => SearchTerms::FORCE_INDEXING_IN_REACT_STORE_FRONT,
                'label' => __('Force indexing in React Storefront'),
                'title' => __('Force indexing in React Storefront'),
                'values' => $this->configSourceYesNo->toOptionArray(),
                'note' => __('Set field as indexable in React Storefront search engine')
            ]
        );
    }
}

