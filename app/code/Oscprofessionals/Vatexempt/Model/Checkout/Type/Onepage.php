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

namespace Oscprofessionals\Vatexempt\Model\Checkout\Type;

class Onepage extends \Magento\Checkout\Model\Type\Onepage
{
    public function saveVatexempt($data)
    {
        if (isset($data['vatdeclare']) && $data['vatdeclare'] !='') {
            $this->getQuote()->setVatpername($data['vatpername']);
            $this->getQuote()->setVatcomment($data['vatcomment']);
            $this->getQuote()->setVatdeclare($data['vatdeclare']);
            $this->getQuote()->collectTotals();

            $this->getQuote()->save();
        } else {
            $this->getQuote()->setVatpername('');
            $this->getQuote()->setVatcomment('');
            $this->getQuote()->setVatdeclare('');
            $this->getQuote()->collectTotals();

            $this->getQuote()->save();
        }

        $this->getCheckout()->setStepData('vatexempt', 'allow', true)->setStepData('vatexempt', 'complete', true)->setStepData('payment', 'allow', true);

        return array();
    }
}
