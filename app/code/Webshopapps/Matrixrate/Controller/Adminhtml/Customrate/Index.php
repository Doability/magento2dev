<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Webshopapps\Matrixrate\Controller\Adminhtml\Customrate;

class Index extends \Magento\Backend\App\Action {

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;
    protected $_matrixRate;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(
    \Magento\Backend\App\Action\Context $context, \Magento\Framework\Registry $coreRegistry, \Webshopapps\Matrixrate\Model\ResourceModel\Carrier\Matrixrate $matrixRate, \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_matrixRate = $matrixRate;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    public function execute() {
        $postData = $this->getRequest()->getParams();
        $data = [[1, '', '', '', '', '', 'admin', 0.0000, 1000.0000, $postData['price'], 0.0000, $postData['title']]];
        /** @var \Magento\Framework\Controller\Result\Forward $resultForward */
        $result = $this->resultJsonFactory->create();
        try {
            $this->_matrixRate->adminCustomRate($data);
            return $result->setData(['success' => true]);
        } catch (\Exception $e) {
            return $result->setData(['error' => true, 'message' => $e->getMessage() . print_r($data, true)]);
        }
    }

}
