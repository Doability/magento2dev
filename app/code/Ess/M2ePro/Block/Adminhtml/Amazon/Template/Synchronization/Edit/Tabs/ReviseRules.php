<?php

namespace Ess\M2ePro\Block\Adminhtml\Amazon\Template\Synchronization\Edit\Tabs;

use Ess\M2ePro\Block\Adminhtml\Magento\Form\AbstractForm;
use Ess\M2ePro\Model\Amazon\Template\Synchronization;

class ReviseRules extends AbstractForm
{
    protected function _prepareForm()
    {
        $template = $this->getHelper('Data\GlobalData')->getValue('tmp_template');
        $formData = !is_null($template)
            ? array_merge($template->getData(), $template->getChildObject()->getData()) : [];

        $defaults = array(
            'revise_update_qty'                              => Synchronization::REVISE_UPDATE_QTY_YES,
            'revise_update_qty_max_applied_value_mode'       => Synchronization::REVISE_MAX_AFFECTED_QTY_MODE_ON,
            'revise_update_qty_max_applied_value'
                    => Synchronization::REVISE_UPDATE_QTY_MAX_APPLIED_VALUE_DEFAULT,
            'revise_update_price'                            => Synchronization::REVISE_UPDATE_PRICE_YES,
            'revise_update_price_max_allowed_deviation_mode'
                    => Synchronization::REVISE_MAX_ALLOWED_PRICE_DEVIATION_MODE_ON,
            'revise_update_price_max_allowed_deviation'
                    => Synchronization::REVISE_UPDATE_PRICE_MAX_ALLOWED_DEVIATION_DEFAULT,
            'revise_update_details'                          => Synchronization::REVISE_UPDATE_DETAILS_NONE,
            'revise_update_images'                           => Synchronization::REVISE_UPDATE_IMAGES_NONE,
            'revise_change_selling_format_template'
                    => \Ess\M2ePro\Model\Template\Synchronization::REVISE_CHANGE_SELLING_FORMAT_TEMPLATE_YES,
            'revise_change_description_template'
                    => Synchronization::REVISE_CHANGE_DESCRIPTION_TEMPLATE_NONE,
            'revise_change_shipping_override_template'
                    => Synchronization::REVISE_CHANGE_SHIPPING_OVERRIDE_TEMPLATE_YES,
            'revise_change_listing'
                    => \Ess\M2ePro\Model\Template\Synchronization::REVISE_CHANGE_LISTING_YES
        );

        $formData = array_merge($defaults, $formData);
        $isEdit = !!$this->getRequest()->getParam('id');
        
        $form = $this->_formFactory->create();

        $form->addField(
            'amazon_template_synchronization_revise',
            self::HELP_BLOCK,
            [
                'content' => $this->__(
                    <<<HTML
                    <p>Revise Rules are the Conditions in accordance with which full and partial 
                    automatic Revise of Listings is performed.</p><br>
                    <p>If a certain Condition is enabled (set to Yes) and satisfied, then Amazon Listings will 
                    be automatically fully or partially updated providing that the current Synchronization Policy
                    is assigned to the Products in M2E Pro Listing.</p>
                    <ul>
                    <li><p><strong>Partial Revise</strong> - if any changes are made to the indicated parameters of
                    Magento Products, automatic Revise will update only the changed details for the Items listed 
                    on Amazon</p></li>
                    <li><p><strong>Full Revise</strong> - contains groups of M2E Pro Settings. If any data in
                    these groups (<i>Selling Format Policy, Listing Settings, Description Policy, etc</i>) is changed,
                    then Listings, which use these Policies in their Settings, will be automatically 
                    fully Revised.</p></li>
                    </ul><br>
                    <p>More detailed information about how to work with this Page you can find
                    <a href="%url%" target="_blank">here</a>.</p>
HTML
                    ,
                    $this->getHelper('Module\Support')->getDocumentationUrl(NULL, NULL, 'x/RwItAQ')
                )
            ]
        );

        $fieldset = $form->addFieldset(
            'magento_block_ebay_template_synchronization_form_data_revise_products',
            [
                'legend' => $this->__('Partial Revise'),
                'collapsable' => true
            ]
        );

        $fieldset->addField(
            'revise_update_qty',
            'select',
            [
                'name' => 'revise_update_qty',
                'label' => $this->__('Quantity'),
                'value' => $formData['revise_update_qty'],
                'values' => [
                    Synchronization::REVISE_UPDATE_QTY_NONE => $this->__('No'),
                    Synchronization::REVISE_UPDATE_QTY_YES => $this->__('Yes'),
                ],
                'tooltip' => $this->__(
                    'Automatically revises Quantity on Amazon Listing(s) when the Quantity or
                    Attribute for Quantity of a Product is changed in Magento.'
                )
            ]
        );

        $fieldset->addField(
            'revise_update_qty_max_applied_value_mode',
            'select',
            [
                'container_id' => 'revise_update_qty_max_applied_value_mode_tr',
                'name' => 'revise_update_qty_max_applied_value_mode',
                'label' => $this->__('Conditional Revise'),
                'value' => $formData['revise_update_qty_max_applied_value_mode'],
                'values' => [
                    Synchronization::REVISE_MAX_AFFECTED_QTY_MODE_OFF => $this->__('No'),
                    Synchronization::REVISE_MAX_AFFECTED_QTY_MODE_ON => $this->__('Yes'),
                ],
                'tooltip' => $this->__(
                    'Updates Amazon QTY only when the Condition you set below is met.
                    <br/><br/><b>Note:</b> By using this Option you can significantly increase Synchronization
                    performance.'
                )
            ]
        );

        $fieldset->addField(
            'revise_update_qty_max_applied_value_',
            'text',
            [
                'container_id' => 'revise_update_qty_max_applied_value_tr',
                'name' => 'revise_update_qty_max_applied_value',
                'label' => $this->__('Revise When Less or Equal to'),
                'value' => $formData['revise_update_qty_max_applied_value'],
                'class' => 'M2ePro-validate-qty',
                'required' => true,
                'tooltip' => $this->__(
                    'The value should not be too high (i.e. 100). Recommended value is in range 10 - 20.'
                )
            ]
        );

        $fieldset->addField(
            'revise_update_qty_max_applied_value_line_tr',
            self::SEPARATOR,
            []
        );

        $fieldset->addField(
            'revise_update_price',
            'select',
            [
                'name' => 'revise_update_price',
                'label' => $this->__('Price'),
                'value' => $formData['revise_update_price'],
                'values' => [
                    Synchronization::REVISE_UPDATE_PRICE_NONE => $this->__('No'),
                    Synchronization::REVISE_UPDATE_PRICE_YES => $this->__('Yes'),
                ],
                'tooltip' => $this->__(
                    'Automatically revises Price on Amazon Listing(s) when the Price or
                    Attribute for Price of a Product is changed in Magento.'
                )
            ]
        );

        $fieldset->addField(
            'revise_update_price_max_allowed_deviation_mode',
            'select',
            [
                'container_id' => 'revise_update_price_max_allowed_deviation_mode_tr',
                'name' => 'revise_update_price_max_allowed_deviation_mode',
                'label' => $this->__('Conditional Revise'),
                'value' => $formData['revise_update_price_max_allowed_deviation_mode'],
                'values' => [
                    Synchronization::REVISE_MAX_ALLOWED_PRICE_DEVIATION_MODE_OFF => $this->__('No'),
                    Synchronization::REVISE_MAX_ALLOWED_PRICE_DEVIATION_MODE_ON => $this->__('Yes'),
                ],
                'tooltip' => $this->__('Updates Amazon Price only when the Condition you set below is met.')
            ]
        );

        $preparedValues = [];
        $percentageStep = 0.5;
        for ($priceDeviationValue = 0.5; $priceDeviationValue <= 20; $priceDeviationValue += $percentageStep) {
            $preparedValues[] = [
                'label' => $priceDeviationValue . ' %',
                'value' => $priceDeviationValue
            ];
            $priceDeviationValue >= 5 && $percentageStep = 1;
        }

        $fieldset->addField(
            'revise_update_price_max_allowed_deviation',
            'select',
            [
                'container_id' => 'revise_update_price_max_allowed_deviation_tr',
                'name' => 'revise_update_price_max_allowed_deviation',
                'label' => $this->__('Revise When Deviation More or Equal than'),
                'value' => $formData['revise_update_price_max_allowed_deviation'],
                'values' => $preparedValues,
                'tooltip' => $this->__('
                    It is a Percent Value of Maximum possible Deviation between Magento Price
                    (Selling Format Policy settings) and Amazon Item Price, that can be ignored.<br/><br/>
                    <strong>For example</strong>, your Magento Price is 23.25$. According to
                    Selling Format Policy Settings Item Price is equal to Magento Price.
                    The "Revise When Deviation More or Equal than" Option is specified to 1%.<br/>
                    1) If Magento Price was changed to 23.26$, possible Deviation Value (0.23$) is
                    <strong>more</strong> than Price change (0.1$), so the Price <strong>will not be Revised</strong>
                    on Amazon.<br/>
                    2) If Magento Price was changed to 23.5$, possible Deviation Value (0.23$) is
                    <strong>less</strong> than Price change (0.25$), so the Price
                    <strong>will be Revised</strong> on Amazon.<br/><br/>
                    After Successful Revise new Magento Price (in this case is 23.5$)
                    will be used for further Deviation count.
                ')
            ]
        );

        $fieldset->addField(
            'revise_update_price_line',
            self::SEPARATOR,
            []
        );

        $fieldset->addField(
            'revise_update_details',
            'select',
            [
                'name' => 'revise_update_details',
                'label' => $this->__('Details'),
                'value' => $formData['revise_update_details'],
                'values' => [
                    Synchronization::REVISE_UPDATE_DETAILS_NONE => $this->__('No'),
                    Synchronization::REVISE_UPDATE_DETAILS_YES => $this->__('Yes'),
                ],
                'tooltip' => $this->__(
                    'Automatically revises data on Amazon Listing(s)
                    if there are changes made to the Magento Attributes used for Description or
                    Condition Note of Listing Settings.'
                )
            ]
        );

        $fieldset->addField(
            'revise_update_images',
            'select',
            [
                'name' => 'revise_update_images',
                'label' => $this->__('Images'),
                'value' => $formData['revise_update_images'],
                'values' => [
                    Synchronization::REVISE_UPDATE_IMAGES_NONE => $this->__('No'),
                    Synchronization::REVISE_UPDATE_IMAGES_YES => $this->__('Yes'),
                ],
                'tooltip' => $this->__(
                    'Automatically revises Images on Amazon Listing(s) if the Image or Attribute for
                    Image of the Item is changed in Magento.'
                )
            ]
        );

        $fieldset = $form->addFieldset(
            'magento_block_amazon_template_synchronization_revise_templates',
            [
                'legend' => $this->__('Full Revise'),
                'collapsable' => true
            ]
        );

        $fieldset->addField(
            'revise_change_listing',
            'select',
            [
                'name' => 'revise_change_listing',
                'label' => $this->__('Listing Settings'),
                'value' => $formData['revise_change_listing'],
                'values' => [
                    \Ess\M2ePro\Model\Template\Synchronization::REVISE_CHANGE_LISTING_NONE => $this->__('No'),
                    \Ess\M2ePro\Model\Template\Synchronization::REVISE_CHANGE_LISTING_YES => $this->__('Yes'),
                ],
                'tooltip' => $this->__('Automatically revises Amazon Listing(s) if its Listing Settings were changed.')
            ]
        );

        $fieldset->addField(
            'revise_change_selling_format_template',
            'select',
            [
                'name' => 'revise_change_selling_format_template',
                'label' => $this->__('Selling Format Policy'),
                'value' => $formData['revise_change_selling_format_template'],
                'values' => [
                    \Ess\M2ePro\Model\Template\Synchronization::REVISE_CHANGE_SELLING_FORMAT_TEMPLATE_NONE
                                => $this->__('No'),
                    \Ess\M2ePro\Model\Template\Synchronization::REVISE_CHANGE_SELLING_FORMAT_TEMPLATE_YES
                                => $this->__('Yes'),
                ],
                'tooltip' => $this->__(
                    'Automatically revises Amazon Listing(s) if its Selling Format Policy was changed.'
                )
            ]
        );

        $fieldset->addField(
            'revise_change_description_template',
            'select',
            [
                'name' => 'revise_change_description_template',
                'label' => $this->__('Description Policy'),
                'value' => $formData['revise_change_description_template'],
                'values' => [
                    Synchronization::REVISE_CHANGE_DESCRIPTION_TEMPLATE_NONE => $this->__('No'),
                    Synchronization::REVISE_CHANGE_DESCRIPTION_TEMPLATE_YES => $this->__('Yes'),
                ],
                'tooltip' => $this->__('Automatically revises Amazon Listing(s) if its Description Policy was changed.')
            ]
        );

        // TODO NOT SUPPORTED FEATURES
//        $fieldset->addField(
//            'revise_change_shipping_override_template',
//            'select',
//            [
//                'name' => 'revise_change_shipping_override_template',
//                'label' => $this->__('Shipping Override Policy'),
//                'value' => $formData['revise_change_shipping_override_template'],
//                'values' => [
//                    Synchronization::REVISE_CHANGE_SHIPPING_OVERRIDE_TEMPLATE_NONE => $this->__('No'),
//                    Synchronization::REVISE_CHANGE_SHIPPING_OVERRIDE_TEMPLATE_YES => $this->__('Yes'),
//                ],
//       'tooltip' => $this->__('Automatically revises Amazon Listing(s) if its Shipping Override Policy was changed.')
//            ]
//        );

        $form->addField(
            'revise_qty_max_applied_value_confirmation_popup_template',
            self::CUSTOM_CONTAINER,
            [
                'text' => $this->__('
                    <br/>It is necessary to understand that Disabling this Option can <strong>negatively</strong>
                    influence on <strong>M2E Pro Performance</strong>.<br/><br/>
                    In case this Option is <strong>Disabled</strong>, M2E Pro will Revise the smallest changes
                    for High Quantity Values (e.g. from 515 to 514), that most likely has no practical effect.
                    It can be time-consuming and more important changes (e.g. from 1 to 0)
                    for another Product can be <strong>stayed in queue</strong> instead of immediate update.
                    Also it can cause increase of Order Import passivity up to 12 hours.<br/>
                    If you <strong>Enable</strong> "Conditional Revise" Option and "Revise When Less or
                    Equal to" Option is set to 5, M2E Pro will Revise your Products in realtime
                    format only when Magento Quantity will be less or equal 5.
                    Revise will not be run until the Quantity Value is more than 5.<br/><br/>
                    So M2E Pro <strong>does not recommend</strong> to Disable this Option and suggests
                    to specify for "Revise When Less or Equal to" Option Value 5 (The less Value = less Unuseful
                    Calls + more Performance of M2E Pro).<br/>
                    You can always change this Option Value according to your needs.<br/><br/>
                    <strong>Note:</strong> For Sellers who synchronize Magento
                    Inventory with Suppliers Inventory by any Automatic Software this Option is
                    <strong>critically required</strong>, as usually Supplier\'s Quantity has
                    Big Values and it is changed very often.
                '),
                'style' => 'display: none;'
            ]
        );

        $form->addField(
            'revise_price_max_max_allowed_deviation_confirmation_popup_template',
            self::CUSTOM_CONTAINER,
            [
                'text' => $this->__('
                    <br/>It is necessary to understand that Disabling this Option can <strong>negatively</strong>
                    nfluence on <strong>M2E Pro Performance</strong>.<br/><br/>
                    In case this Option is <strong>Disabled</strong>, M2E Pro will Revise the smallest changes
                    for Price Values (e.g. from 25.25$ to 25.26$), that most likely has no practical effect.
                    It can be time-consuming and more important changes (e.g. from 1$ to 50$) for another
                    Product can be <strong>stayed in queue</strong> instead of immediate update.
                    Also it can cause increase of Order Import passivity up to 12 hours.<br/>
                    If you <strong>Enable</strong> "Conditional Revise" Option and "Revise When Deviation More or
                    Equal than" set to 3%, M2E Pro will Revise your Products in realtime format only when Price
                    change will be more than 3% from Starting Price.<br/><br/>
                    So M2E Pro <strong>does not recommend</strong> to Disable this Option (The more value =
                    less Unusefull Calls + more Performance of M2E Pro).<br/>
                    You can always change this Option Value according to your needs.<br/><br/>
                    <strong>Note:</strong> For Sellers who synchronize Magento Inventory with Suppliers
                    Inventory by any Automatic Software this Option is <strong>critically required</strong>,
                    as Supplier\s Price Values are changed very often.
                '),
                'style' => 'display: none;'
            ]
        );

        $this->jsTranslator->add('Wrong value. Only integer numbers.', $this->__('Wrong value. Only integer numbers.'));

        $jsFormData = [
            'revise_update_qty',
            'revise_update_price',
            'revise_change_selling_format_template',
            'revise_change_listing',
            'revise_update_qty_max_applied_value',
        ];

        foreach ($jsFormData as $item) {
            $this->js->add("M2ePro.formData.$item = '{$this->getHelper('Data')->escapeJs($formData[$item])}';");
        }
        
        $this->setForm($form);

        return parent::_prepareForm();
    }
}