<?php

namespace Ess\M2ePro\Block\Adminhtml\Amazon\Template\Synchronization\Edit\Tabs;

use Ess\M2ePro\Block\Adminhtml\Magento\Form\AbstractForm;
use Ess\M2ePro\Model\Amazon\Template\Synchronization;

class StopRules extends AbstractForm
{
    protected function _prepareForm()
    {
        $template = $this->getHelper('Data\GlobalData')->getValue('tmp_template');
        $formData = !is_null($template)
            ? array_merge($template->getData(), $template->getChildObject()->getData()) : [];

        $defaults = array(
            'stop_status_disabled' => Synchronization::STOP_STATUS_DISABLED_YES,
            'stop_out_off_stock' => Synchronization::STOP_OUT_OFF_STOCK_YES,

            'stop_qty_magento'           => Synchronization::STOP_QTY_NONE,
            'stop_qty_magento_value'     => '0',
            'stop_qty_magento_value_max' => '10',

            'stop_qty_calculated'           => Synchronization::STOP_QTY_NONE,
            'stop_qty_calculated_value'     => '0',
            'stop_qty_calculated_value_max' => '10'
        );
        $formData = array_merge($defaults, $formData);

        $isEdit = !!$this->getRequest()->getParam('id');
        
        $form = $this->_formFactory->create();

        $form->addField(
            'amazon_template_synchronization_stop',
            self::HELP_BLOCK,
            [
                'content' => $this->__('Stop Rules define the Conditions when Amazon Items Listing must be
    inactivated, depending on Magento Product state.<br/><br/>
    <b>Note:</b> If all Stop Conditions are set to <i>No</i> or <i>No Action</i>,
    then the Stop Option for Amazon Items is disabled.<br/>
    If all Stop Conditions are enabled, then an Item will be inactivated if at least one of the
    Stop Conditions is met.<br/><br/>
    More detailed information about ability to work with this Page you can find
    <a href="%url%" target="_blank">here</a>.',
                    $this->getHelper('Module\Support')->getDocumentationUrl(NULL, NULL, 'x/HIMVAQ')
                )
            ]
        );

        $fieldset = $form->addFieldset(
            'magento_block_ebay_template_synchronization_form_data_stop_rules',
            [
                'legend' => $this->__('Stop Conditions'),
                'collapsable' => false,
            ]
        );

        $fieldset->addField(
            'stop_status_disabled',
            'select',
            [
                'name' => 'stop_status_disabled',
                'label' => $this->__('Stop When Status Disabled'),
                'value' => $formData['stop_status_disabled'],
                'values' => [
                    Synchronization::STOP_STATUS_DISABLED_NONE => $this->__('No'),
                    Synchronization::STOP_STATUS_DISABLED_YES => $this->__('Yes'),
                ],
                'tooltip' => $this->__(
                    'Automatically stops Item(s) if its status has been changed to \'Disabled\' in Magento.'
                )
            ]
        );

        $fieldset->addField(
            'stop_out_off_stock',
            'select',
            [
                'name' => 'stop_out_off_stock',
                'label' => $this->__('Stop When Out Of Stock'),
                'value' => $formData['stop_out_off_stock'],
                'values' => [
                    Synchronization::STOP_OUT_OFF_STOCK_NONE => $this->__('No'),
                    Synchronization::STOP_OUT_OFF_STOCK_YES => $this->__('Yes'),
                ],
                'tooltip' => $this->__(
                    'Automatically stops Item(s) if its Stock availability has been changed to \'Out of Stock\'
                    in Magento.'
                )
            ]
        );

        $fieldset->addField(
            'stop_qty_magento',
            'select',
            [
                'name' => 'stop_qty_magento',
                'label' => $this->__('Stop When Magento Quantity Is'),
                'value' => $formData['stop_qty_magento'],
                'values' => [
                    Synchronization::STOP_QTY_NONE => $this->__('No Action'),
                    Synchronization::STOP_QTY_LESS => $this->__('Less or Equal'),
                    Synchronization::STOP_QTY_BETWEEN => $this->__('Between'),
                ],
                'tooltip' => $this->__(
                    'Automatically stops Item(s) if Magento Quantity has been changed and meets the Conditions.'
                )
            ]
        )->addCustomAttribute('qty_type', 'magento');

        $fieldset->addField(
            'stop_qty_magento_value',
            'text',
            [
                'container_id' => 'stop_qty_magento_value_container',
                'name' => 'stop_qty_magento_value',
                'label' => $this->__('Quantity'),
                'value' => $formData['stop_qty_magento_value'],
                'class' => 'validate-digits',
                'required' => true
            ]
        );

        $fieldset->addField(
            'stop_qty_magento_value_max',
            'text',
            [
                'container_id' => 'stop_qty_magento_value_max_container',
                'name' => 'stop_qty_magento_value_max',
                'label' => $this->__('Max Quantity'),
                'value' => $formData['stop_qty_magento_value_max'],
                'class' => 'validate-digits M2ePro-validate-conditions-between',
                'required' => true
            ]
        );

        $fieldset->addField(
            'stop_qty_calculated',
            'select',
            [
                'name' => 'stop_qty_calculated',
                'label' => $this->__('Stop When Calculated Quantity Is'),
                'value' => $formData['stop_qty_calculated'],
                'values' => [
                    Synchronization::STOP_QTY_NONE => $this->__('No Action'),
                    Synchronization::STOP_QTY_LESS => $this->__('Less or Equal'),
                    Synchronization::STOP_QTY_BETWEEN => $this->__('Between'),
                ],
                'tooltip' => $this->__(
                    'Automatically stops Item(s) if Calculated Quantity according to the Selling Format
                    Policy has been changed and meets the Conditions.'
                )
            ]
        )->addCustomAttribute('qty_type', 'calculated');

        $fieldset->addField(
            'stop_qty_calculated_value',
            'text',
            [
                'container_id' => 'stop_qty_calculated_value_container',
                'name' => 'stop_qty_calculated_value',
                'label' => $this->__('Quantity'),
                'value' => $formData['stop_qty_calculated_value'],
                'class' => 'validate-digits',
                'required' => true
            ]
        );

        $fieldset->addField(
            'stop_qty_calculated_value_max',
            'text',
            [
                'container_id' => 'stop_qty_calculated_value_max_container',
                'name' => 'stop_qty_calculated_value_max',
                'label' => $this->__('Max Quantity'),
                'value' => $formData['stop_qty_calculated_value_max'],
                'class' => 'validate-digits M2ePro-validate-conditions-between',
                'required' => true
            ]
        );

        $jsFormData = [
            'stop_status_disabled',
            'stop_out_off_stock',

            'stop_qty_magento',
            'stop_qty_magento_value',
            'stop_qty_magento_value_max',

            'stop_qty_calculated',
            'stop_qty_calculated_value',
            'stop_qty_calculated_value_max',
        ];

        foreach ($jsFormData as $item) {
            $this->js->add("M2ePro.formData.$item = '{$this->getHelper('Data')->escapeJs($formData[$item])}';");
        }

        $this->setForm($form);

        return parent::_prepareForm();
    }
}