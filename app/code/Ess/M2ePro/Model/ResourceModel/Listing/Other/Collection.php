<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Model\ResourceModel\Listing\Other;

class Collection extends \Ess\M2ePro\Model\ResourceModel\ActiveRecord\Collection\Component\Parent\AbstractCollection
{
    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init(
            'Ess\M2ePro\Model\Listing\Other',
            'Ess\M2ePro\Model\ResourceModel\Listing\Other'
        );
    }

    //########################################
}