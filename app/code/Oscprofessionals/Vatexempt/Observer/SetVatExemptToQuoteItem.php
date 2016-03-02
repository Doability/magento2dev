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
namespace Oscprofessionals\Vatexempt\Observer;

use Magento\Framework\Event\ObserverInterface;

class SetVatExemptToQuoteItem implements ObserverInterface
{
    /*
     * To set Vat_exempt value of product into quote_item
    */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->setToQuoteItem($observer);
    }

    public function setToQuoteItem($observer){

        $vatExempt = $observer->getEvent()->getProduct()->getData('vat_exempt');
        $quoteItem= $observer->getEvent()->getQuoteItem();
        $quoteItem->setData('vat_exempt',$vatExempt);

    }
}
