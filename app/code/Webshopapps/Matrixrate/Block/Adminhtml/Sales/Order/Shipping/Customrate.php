<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Customrate
 *
 * @author akshay.jindal
 */

namespace Webshopapps\Matrixrate\Block\Adminhtml\Sales\Order\Shipping;

use \Magento\Backend\Block\Template;

class Customrate extends Template
{

    /**
     * @param Template\Context $context
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context
    ) {

        parent::__construct($context);
    }


    /**
     * @return string
     */
    public function getCurrentPageUrl(){
        return $this->getUrl('sales/order_create/index', ['_current' => false]);
    }

}
