<?php

namespace Webshopapps\Matrixrate\Block\Adminhtml\System\Config\Form\Field;

class Exportmatrix extends \Magento\Framework\Data\Form\Element\AbstractElement {

    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    protected $_backendUrl;

    /**
     * @param \Magento\Framework\Data\Form\Element\Factory $factoryElement
     * @param \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Backend\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
    \Magento\Framework\Data\Form\Element\Factory $factoryElement, \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection, \Magento\Framework\Escaper $escaper, \Magento\Backend\Model\UrlInterface $backendUrl, array $data = []
    ) {
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
        $this->_backendUrl = $backendUrl;
    }

    public function getElementHtml() {
        $buttonBlock = $this->getForm()->getParent()->getLayout()->createBlock('Magento\Backend\Block\Widget\Button');
        $params = ['website' => $buttonBlock->getRequest()->getParam('website')];     
        $url = $this->_backendUrl->getUrl("*/*/exportmatrixrate", $params);
        $data = [
            'label' => __('Export CSV'),
            'onclick' => "setLocation('" .
            $url .
            "conditionName/' + $('carriers_matrixrate_condition_name').value + '/matrixrate.csv' )",
            'class' => '',
        ];

        $html = $buttonBlock->setData($data)->toHtml();
        return $html;
    }

}
