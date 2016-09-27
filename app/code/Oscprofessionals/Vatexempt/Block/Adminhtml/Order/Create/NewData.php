<?php

namespace Oscprofessionals\Vatexempt\Block\Adminhtml\Order\Create;
use \Magento\Framework\View\Element\Template;

class NewData extends Template
{
    public function beforeToHtml(\Magento\Sales\Block\Adminhtml\Order\Create\Data $originalBlock){

        $originalBlock->setTemplate('Oscprofessionals_Vatexempt::newdata.phtml');

    }

}