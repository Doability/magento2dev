<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

/**
 * @method \Ess\M2ePro\Model\Order\Item[] getItems()
 */
namespace Ess\M2ePro\Model\ResourceModel\Order\Item;

class Collection extends \Ess\M2ePro\Model\ResourceModel\ActiveRecord\Collection\Component\Parent\AbstractCollection
{
    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init(
            'Ess\M2ePro\Model\Order\Item',
            'Ess\M2ePro\Model\ResourceModel\Order\Item'
        );
    }

    //########################################
}