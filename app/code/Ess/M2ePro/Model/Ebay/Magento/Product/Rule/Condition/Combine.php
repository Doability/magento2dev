<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Model\Ebay\Magento\Product\Rule\Condition;

class Combine extends \Ess\M2ePro\Model\Magento\Product\Rule\Condition\Combine
{
    //########################################

    public function __construct(
        \Ess\M2ePro\Model\Factory $modelFactory,
        \Ess\M2ePro\Helper\Factory $helperFactory,
        \Magento\Rule\Model\Condition\Context $context,
        array $data = []
    )
    {

        $this->setType('Ebay\Magento\Product\Rule\Condition\Combine');
        parent::__construct($modelFactory, $helperFactory, $context, $data);
    }

    //########################################

    /**
     * @return string
     */
    protected function getConditionCombine()
    {
        return $this->getType() . '|ebay|';
    }

    /**
     * @return string
     */
    protected function getCustomLabel()
    {
        return $this->helperFactory->getObject('Module\Translation')->__('eBay Values');
    }

    /**
     * @return array
     */
    protected function getCustomOptions()
    {
        $attributes = $this->getCustomOptionsAttributes();
        return !empty($attributes) ?
            $this->getOptions('Ebay\Magento\Product\Rule\Condition\Product', $attributes, array('ebay'))
            : array();
    }

    /**
     * @return array
     */
    protected function getCustomOptionsAttributes()
    {
        $helper = $this->helperFactory->getObject('Module\Translation');
        return array(
            'ebay_item_id' => $helper->__('Item ID'),
            'ebay_online_title' => $helper->__('Title'),
            'ebay_online_sku' => $helper->__('SKU'),
            'ebay_online_category_id' => $helper->__('Category ID'),
            'ebay_online_category_path' => $helper->__('Category Path'),
            'ebay_available_qty' => $helper->__('Available QTY'),
            'ebay_sold_qty' => $helper->__('Sold QTY'),
            'ebay_online_current_price' => $helper->__('Price'),
            'ebay_online_start_price' => $helper->__('Start Price'),
            'ebay_online_reserve_price' => $helper->__('Reserve Price'),
            'ebay_online_buyitnow_price' => $helper->__('"Buy It Now" Price'),
            'ebay_status' => $helper->__('Status'),
            'ebay_start_date' => $helper->__('Start Date'),
            'ebay_end_date' => $helper->__('End Date'),
        );
    }

    //########################################
}