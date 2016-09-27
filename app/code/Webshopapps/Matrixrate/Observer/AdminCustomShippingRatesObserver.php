<?php

/**
 *
 * @author      Oscprofessionals Team (support@oscprofessionals.com)
 * @copyright   Copyright (c) 2015 Oscprofessionals (http://www.oscprofessionals.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @category    Oscprofessionals
 * @package     Oscprofessionals_Vatexempt
 */

namespace Webshopapps\Matrixrate\Observer;

use \Magento\Framework\Event\Observer;
use \Magento\Framework\Event\ObserverInterface;

class AdminCustomShippingRatesObserver implements ObserverInterface {

    protected $_carrierFactory;

    public function __construct(
    \Webshopapps\Matrixrate\Model\CarrierFatory $carrierFactory
    ) {
        $this->_carrierFactory = $carrierFactory;
    }

    public function execute(\Magento\Framework\Event\Observer $observer) {
       $adminCarrierRate =  $this->_carrierFactory->laod();
       
    }

}
