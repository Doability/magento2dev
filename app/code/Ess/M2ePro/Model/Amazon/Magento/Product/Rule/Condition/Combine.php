<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Model\Amazon\Magento\Product\Rule\Condition;

class Combine extends \Ess\M2ePro\Model\Magento\Product\Rule\Condition\Combine
{
    //########################################

    public function __construct(
        \Ess\M2ePro\Model\Factory $modelFactory,
        \Ess\M2ePro\Helper\Factory $helperFactory,
        \Magento\Rule\Model\Condition\Context $context,
        array $data = [])
    {
        $this->setType('Amazon\Magento\Product\Rule\Condition\Combine');
        parent::__construct($modelFactory, $helperFactory, $context, $data);
    }

    //########################################

    protected function getConditionCombine()
    {
        return $this->getType() . '|amazon|';
    }

    // ---------------------------------------

    protected function getCustomLabel()
    {
        return $this->helperFactory->getObject('Module\Translation')->__('Amazon Values');
    }

    protected function getCustomOptions()
    {
        $attributes = $this->getCustomOptionsAttributes();
        return !empty($attributes) ?
               $this->getOptions('Amazon\Magento\Product\Rule\Condition\Product', $attributes, array('amazon'))
               : array();
    }

    protected function getCustomOptionsAttributes()
    {
        $helper = $this->helperFactory->getObject('Module\Translation');
        return array(
            'amazon_sku' => $helper->__('SKU'),
            'amazon_general_id' => $helper->__('ASIN/ISBN Value'),
            'amazon_general_id_state' => $helper->__('ASIN/ISBN Status'),
            'amazon_online_qty' => $helper->__('QTY'),
            'amazon_online_price' => $helper->__('Price'),
            'amazon_online_sale_price' => $helper->__('Sale Price'),
            'amazon_is_afn_chanel' => $helper->__('Fulfillment'),
            'amazon_status' => $helper->__('Status')
        );
    }

    //########################################
}