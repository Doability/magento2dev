<?php

namespace Oscprofessionals\Vatexempt\Block\Adminhtml\Order\Create;

use \Magento\Backend\Block\Template;

class Vatdeclare extends Template
{
    /**
     * @var \Oscprofessionals\Vatexempt\Model\VatdeclareModel
     *
     */
    protected $_vatdeclareModel;

    /**
     * @param Template\Context $context
     * @param \Oscprofessionals\Vatexempt\Model\VatdeclareModel $vatdeclareModel
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
       \Oscprofessionals\Vatexempt\Model\VatdeclareModel $vatdeclareModel

    ) {
       $this->_vatdeclareModel = $vatdeclareModel;
        parent::__construct($context);
    }


    /**
     * @return string
     */
    public function getCurrentPageUrl(){
        return $this->getUrl('sales/order_create/index', ['_current' => false]);
    }

    /**
     * @param $params
     * @return int
     */
    public function getVatdeclareStatus($params){
        return $this->_vatdeclareModel->getVatdeclareStatusFromModel($params);
    }
}
