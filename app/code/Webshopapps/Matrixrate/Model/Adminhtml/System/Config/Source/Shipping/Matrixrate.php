<?php

namespace Webshopapps\Matrixrate\Model\Adminhtml\System\Config\Source\Shipping;

class Matrixrate implements \Magento\Framework\Option\ArrayInterface {

    /**
     * @var \Magento\OfflineShipping\Model\Carrier\Tablerate
     */
    protected $_carrierTablerate;

    /**
     * @param \Magento\OfflineShipping\Model\Carrier\Tablerate $carrierTablerate
     */
    public function __construct(\Webshopapps\Matrixrate\Model\Carrier\Matrixrate $carrierTablerate) {
        $this->_carrierTablerate = $carrierTablerate;
    }

    public function toOptionArray() {
        $arr = [];
        foreach ($this->_carrierTablerate->getCode('condition_name') as $k => $v) {
            $arr[] = array('value' => $k, 'label' => $v);
        }
        return $arr;
    }

}
