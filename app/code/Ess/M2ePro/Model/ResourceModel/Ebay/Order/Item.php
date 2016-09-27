<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Model\ResourceModel\Ebay\Order;

class Item extends \Ess\M2ePro\Model\ResourceModel\ActiveRecord\Component\Child\AbstractDb
{
    protected $_isPkAutoIncrement = false;

    //########################################

    public function _construct()
    {
        $this->_init('m2epro_ebay_order_item', 'order_item_id');
        $this->_isPkAutoIncrement = false;
    }

    //########################################
}