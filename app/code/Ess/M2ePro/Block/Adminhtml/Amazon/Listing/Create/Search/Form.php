<?php

namespace Ess\M2ePro\Block\Adminhtml\Amazon\Listing\Create\Search;

use Ess\M2ePro\Block\Adminhtml\Magento\Form\AbstractForm;
use Ess\M2ePro\Model\Amazon\Listing;

class Form extends AbstractForm
{
    protected $sessionKey = 'amazon_listing_create';
    protected $useFormContainer = true;

    /** @var \Ess\M2ePro\Model\Listing */
    protected $listing;

    protected $amazonFactory;
    protected $elementFactory;

    //########################################

    public function __construct(
        \Ess\M2ePro\Model\ActiveRecord\Component\Parent\Amazon\Factory $amazonFactory,
        \Magento\Framework\Data\Form\Element\Factory $elementFactory,
        \Ess\M2ePro\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    )
    {
        $this->amazonFactory = $amazonFactory;
        $this->elementFactory = $elementFactory;
        parent::__construct($context, $registry, $formFactory, $data);
    }
    
    protected function _prepareForm()
    {
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id'      => 'edit_form',
                    'method'  => 'post',
                    'action'  => 'javascript:void(0)',
                    'enctype' => 'multipart/form-data',
                    'class' => 'admin__scope-old'
                ]
            ]
        );

        /** @var \Ess\M2ePro\Helper\Magento\Attribute $magentoAttributeHelper */
        $magentoAttributeHelper = $this->getHelper('Magento\Attribute');

        $attributesByTypes = array(
            'text' => $magentoAttributeHelper->filterByInputTypes($this->getData('general_attributes'), array('text'))
        );
        $formData = $this->getListingData();

        // Identifiers Settings
        $fieldset = $form->addFieldset(
            'identifiers_settings_fieldset',
            [
                'legend' => $this->__('Identifiers Settings'),
                'collapsable' => false
            ]
        );

        $fieldset->addField(
            'general_id_custom_attribute',
            'hidden',
            [
                'name' => 'general_id_custom_attribute',
                'value' => $formData['general_id_custom_attribute']
            ]
        );

        $preparedAttributes = [];

        $showWarning = false;
        if ($formData['general_id_mode'] == \Ess\M2ePro\Model\Amazon\Listing::GENERAL_ID_MODE_CUSTOM_ATTRIBUTE &&
            !$magentoAttributeHelper->isExistInAttributesArray(
                $formData['general_id_custom_attribute'], $attributesByTypes['text']
            ) && $formData['general_id_custom_attribute'] != '') {

            $attrs = [
                'attribute_code' => $formData['general_id_custom_attribute'],
                'selected' => 'selected'
            ];

            $preparedAttributes[] = [
                'attrs' => $attrs,
                'value' => \Ess\M2ePro\Model\Amazon\Listing::GENERAL_ID_MODE_CUSTOM_ATTRIBUTE,
                'label' => $magentoAttributeHelper->getAttributeLabel($formData['general_id_custom_attribute']),
            ];
        }

        foreach ($attributesByTypes['text'] as $attribute) {
            $attrs = ['attribute_code' => $attribute['code']];
            if (
                $formData['general_id_mode'] == \Ess\M2ePro\Model\Amazon\Listing::GENERAL_ID_MODE_CUSTOM_ATTRIBUTE
                && $attribute['code'] == $formData['general_id_custom_attribute']
            ) {
                $attrs['selected'] = 'selected';
            }
            $preparedAttributes[] = [
                'attrs' => $attrs,
                'value' => \Ess\M2ePro\Model\Amazon\Listing::GENERAL_ID_MODE_CUSTOM_ATTRIBUTE,
                'label' => $attribute['label'],
            ];
        }

        $fieldset->addField(
            'general_id_mode',
            self::SELECT,
            [
                'name' => 'general_id_mode',
                'label' => $this->__('ASIN / ISBN'),
                'class' => 'M2ePro-custom-attribute-can-be-created',
                'values' => [
                    \Ess\M2ePro\Model\Amazon\Listing::GENERAL_ID_MODE_NOT_SET => $this->__('Not Set'),
                    [
                        'label' => $this->__('Magento Attributes'),
                        'value' => $preparedAttributes,
                        'attrs' => [
                            'class' => 'M2ePro-custom-attribute-optgroup'
                        ]
                    ]
                ],
                'value' => $formData['general_id_mode'] != Listing::GENERAL_ID_MODE_CUSTOM_ATTRIBUTE
                    ? $formData['general_id_mode'] : '',
                'tooltip' => $this->__(
                    'This Setting is the source of value for ASIN/ISBN will be used at the time of
                    Automatic Search of Amazon Products.'),
                'after_element_html' => !$showWarning ?
                    '' : <<<HTML
<div class="warning-tooltip">
    <div class="m2epro-field-tooltip m2epro-field-tooltip-warning admin__field-tooltip">
        <a class="admin__field-tooltip-action" href="javascript://"></a>
        <div class="admin__field-tooltip-content">
            Magento Attribute you have chosen earlier is using not for all
            Attribute Sets or has type different from acceptable to use for this Option. <br/><br/>
            Please, select another valid Magento Attribute from the list or add selected
            Attribute to all Attributes Sets of Magento.
        </div>
    </div>
</div>
HTML
            ]
        );

        $fieldset->addField(
            'worldwide_id_custom_attribute',
            'hidden',
            [
                'name' => 'worldwide_id_custom_attribute',
                'value' => $formData['worldwide_id_custom_attribute']
            ]
        );

        $preparedAttributes = [];

        $showWarning = false;
        if ($formData['worldwide_id_mode'] == \Ess\M2ePro\Model\Amazon\Listing::WORLDWIDE_ID_MODE_CUSTOM_ATTRIBUTE &&
            !$magentoAttributeHelper->isExistInAttributesArray(
                $formData['worldwide_id_custom_attribute'], $attributesByTypes['text']
            ) && $formData['worldwide_id_custom_attribute'] != '') {

            $attrs = [
                'attribute_code' => $formData['worldwide_id_custom_attribute'],
                'selected' => 'selected'
            ];

            $preparedAttributes[] = [
                'attrs' => $attrs,
                'value' => \Ess\M2ePro\Model\Amazon\Listing::WORLDWIDE_ID_MODE_CUSTOM_ATTRIBUTE,
                'label' => $magentoAttributeHelper->getAttributeLabel($formData['worldwide_id_custom_attribute']),
            ];
        }

        foreach ($attributesByTypes['text'] as $attribute) {
            $attrs = ['attribute_code' => $attribute['code']];
            if (
                $formData['worldwide_id_mode'] == \Ess\M2ePro\Model\Amazon\Listing::WORLDWIDE_ID_MODE_CUSTOM_ATTRIBUTE
                && $attribute['code'] == $formData['worldwide_id_custom_attribute']
            ) {
                $attrs['selected'] = 'selected';
            }
            $preparedAttributes[] = [
                'attrs' => $attrs,
                'value' => \Ess\M2ePro\Model\Amazon\Listing::WORLDWIDE_ID_MODE_CUSTOM_ATTRIBUTE,
                'label' => $attribute['label'],
            ];
        }

        $fieldset->addField(
            'worldwide_id_mode',
            self::SELECT,
            [
                'name' => 'worldwide_id_mode',
                'label' => $this->__('UPC / EAN'),
                'class' => 'M2ePro-custom-attribute-can-be-created',
                'values' => [
                    \Ess\M2ePro\Model\Amazon\Listing::WORLDWIDE_ID_MODE_NOT_SET => $this->__('Not Set'),
                    [
                        'label' => $this->__('Magento Attributes'),
                        'value' => $preparedAttributes,
                        'attrs' => [
                            'class' => 'M2ePro-custom-attribute-optgroup'
                        ]
                    ]
                ],
                'value' => $formData['worldwide_id_mode'] != Listing::WORLDWIDE_ID_MODE_CUSTOM_ATTRIBUTE
                    ? $formData['worldwide_id_mode'] : '',
                'tooltip' => $this->__(
                    'This Setting is the source of value for UPC/EAN will be used at the time of
                    Automatic Search of Amazon Products.'),
                'after_element_html' => !$showWarning ?
                    '' : <<<HTML
<div class="warning-tooltip">
    <div class="m2epro-field-tooltip m2epro-field-tooltip-warning admin__field-tooltip">
        <a class="admin__field-tooltip-action" href="javascript://"></a>
        <div class="admin__field-tooltip-content">
            Magento Attribute you have chosen earlier is used not for all Attribute Sets or has type
            different from acceptable to use for this Option. <br/><br/>
            Please, select another valid Magento Attribute from the list or add selected Attribute to
            all Attributes Sets of Magento.
        </div>
    </div>
</div>
HTML
            ]
        );

        // Additional Settings
        $fieldset = $form->addFieldset(
            'additional_settings_fieldset',
            [
                'legend' => $this->__('Additional Settings'),
                'collapsable' => false
            ]
        );

        $fieldset->addField(
            'search_by_magento_title_mode',
            'select',
            [
                'name' => 'search_by_magento_title_mode',
                'label' => $this->__('Search by Product Name'),
                'class' => 'M2ePro-custom-attribute-can-be-created',
                'values' => [
                    \Ess\M2ePro\Model\Amazon\Listing::SEARCH_BY_MAGENTO_TITLE_MODE_NONE => $this->__('Disable'),
                    \Ess\M2ePro\Model\Amazon\Listing::SEARCH_BY_MAGENTO_TITLE_MODE_YES => $this->__('Enable')
                ],
                'value' => $formData['search_by_magento_title_mode'],
                'tooltip' => $this->__(
                    '<p>Enable this additional Setting if you want M2E Pro to perform the search for Amazon 
                    Products based on Magento Product Name.</p><br>
                    <p><strong>Please note</strong> that this setting is not applied to search for the available 
                    Amazon Products during the List action.</p>'
                )
            ]
        );

        $form->setUseContainer($this->useFormContainer);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    //########################################

    protected function _beforeToHtml()
    {
        // ---------------------------------------
        $data = $this->getListingData();

        $this->setData(
            'general_attributes',
            $this->getHelper('Magento\Attribute')->getGeneralFromAllAttributeSets()
        );

        foreach ($data as $key=>$value) {
            $this->setData($key, $value);
        }
        // ---------------------------------------

        return parent::_beforeToHtml();
    }

    //########################################

    protected function _toHtml()
    {

        $this->css->add(<<<CSS
.warning-tooltip {
    display: inline-block;
    width: 40px;
} 
        
.warning-tooltip .admin__field-tooltip .admin__field-tooltip-action:before {
    content: '\\e623';
    display: inline-block;
}      
CSS
        );

        $this->jsPhp->addConstants($this->getHelper('Data')->getClassConstants('\Ess\M2ePro\Model\Amazon\Listing'));

        $this->js->add(<<<JS
require([
    'M2ePro/Amazon/Listing/Create/Search'
], function(){

    window.AmazonListingCreateSearchObj = new AmazonListingCreateSearch();

    $('general_id_mode').observe('change', AmazonListingCreateSearchObj.general_id_mode_change);
    $('worldwide_id_mode').observe('change', AmazonListingCreateSearchObj.worldwide_id_mode_change);

});
JS
        );

        return parent::_toHtml();
    }

    //########################################

    public function getDefaultFieldsValues()
    {
        return array(
            'general_id_mode' => \Ess\M2ePro\Model\Amazon\Listing::GENERAL_ID_MODE_NOT_SET,
            'general_id_custom_attribute' => '',

            'worldwide_id_mode' => \Ess\M2ePro\Model\Amazon\Listing::WORLDWIDE_ID_MODE_NOT_SET,
            'worldwide_id_custom_attribute' => '',

            'search_by_magento_title_mode' => \Ess\M2ePro\Model\Amazon\Listing::SEARCH_BY_MAGENTO_TITLE_MODE_NONE
        );
    }

    //########################################

    protected function getListingData()
    {
        if (!is_null($this->getRequest()->getParam('id'))) {
            $data = array_merge($this->getListing()->getData(), $this->getListing()->getChildObject()->getData());
        } else {
            $data = $this->getHelper('Data\Session')->getValue($this->sessionKey);
            $data = array_merge($this->getDefaultFieldsValues(), $data);
        }

        return $data;
    }

    //########################################

    protected function getListing()
    {
        if (!$listingId = $this->getRequest()->getParam('id')) {
            throw new \Ess\M2ePro\Model\Exception('Listing is not defined');
        }

        if (is_null($this->listing)) {
            $this->listing = $this->amazonFactory->getCachedObjectLoaded('Listing', $listingId);
        }

        return $this->listing;
    }

    //########################################

    /**
     * @param boolean $useFormContainer
     */
    public function setUseFormContainer($useFormContainer)
    {
        $this->useFormContainer = $useFormContainer;
    }

    //########################################
}