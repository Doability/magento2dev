<?php

namespace Webshopapps\Matrixrate\Model\Carrier;

use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote\Address\RateRequest;

class Matrixrate extends \Magento\Shipping\Model\Carrier\AbstractCarrier implements \Magento\Shipping\Model\Carrier\CarrierInterface {

    protected $_code = 'matrixrate';
    protected $_default_condition_name = 'package_weight';
    protected $_conditionNames = array();

    public function __construct(
    \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, 
            \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory, 
            \Psr\Log\LoggerInterface $logger,
            \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory, 
            \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $resultMethodFactory, 
            \Webshopapps\Matrixrate\Model\ResourceModel\Carrier\MatrixrateFactory $matrixrateFactory, 
              \Magento\Framework\App\State $areaState,
            array $data = []
    ) {
        $this->_rateResultFactory = $rateResultFactory;
        $this->areaState = $areaState;
        $this->_resultMethodFactory = $resultMethodFactory;
        $this->_matrixrateFactory = $matrixrateFactory;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
        foreach ($this->getCode('condition_name') as $k => $v) {
            $this->_conditionNames[] = $k;
        }
    }

    /**
     * Enter description here...
     *
     * @param Mage_Shipping_Model_Rate_Request $data
     * @return Mage_Shipping_Model_Rate_Result
     */
    public function collectRates(RateRequest $request) {
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        // exclude Virtual products price from Package value if pre-configured
        if (!$this->getConfigFlag('include_virtual_price') && $request->getAllItems()) {
            foreach ($request->getAllItems() as $item) {
                if ($item->getParentItem()) {
                    continue;
                }
                if ($item->getHasChildren() && $item->isShipSeparately()) {
                    foreach ($item->getChildren() as $child) {
                        if ($child->getProduct()->isVirtual() || $item->getProductType() == 'downloadable') {
                            $request->setPackageValue($request->getPackageValue() - $child->getBaseRowTotal());
                        }
                    }
                } elseif ($item->getProduct()->isVirtual() || $item->getProductType() == 'downloadable') {
                    $request->setPackageValue($request->getPackageValue() - $item->getBaseRowTotal());
                }
            }
        }

        // Free shipping by qty
        $freeQty = 0;
        if ($request->getAllItems()) {
            foreach ($request->getAllItems() as $item) {
                if ($item->getProduct()->isVirtual() || $item->getParentItem()) {
                    continue;
                }

                if ($item->getHasChildren() && $item->isShipSeparately()) {
                    foreach ($item->getChildren() as $child) {
                        if ($child->getFreeShipping() && !$child->getProduct()->isVirtual()) {
                            $freeQty += $item->getQty() * ($child->getQty() - (is_numeric($child->getFreeShipping()) ? $child->getFreeShipping() : 0));
                        }
                    }
                } elseif ($item->getFreeShipping()) {
                    $freeQty += ($item->getQty() - (is_numeric($item->getFreeShipping()) ? $item->getFreeShipping() : 0));
                }
            }
        }
        
        if (!$request->getMRConditionName()) {

            if ($this->areaState->getAreaCode() == 'adminhtml') {
                $request->setMRConditionName(['notempty','admin',$this->getConfigData('condition_name') ? $this->getConfigData('condition_name') : $this->_default_condition_name]);
            } else
                $request->setMRConditionName($this->getConfigData('condition_name') ? $this->getConfigData('condition_name') : $this->_default_condition_name);
        }

        // Package weight and qty free shipping
        $oldWeight = $request->getPackageWeight();
        $oldQty = $request->getPackageQty();

        if ($this->getConfigData('allow_free_shipping_promotions') && !$this->getConfigData('include_free_ship_items')) {
            $request->setPackageWeight($request->getFreeMethodWeight());
            $request->setPackageQty($oldQty - $freeQty);
        }

        $result = $this->_rateResultFactory->create();
        $ratearray = $this->getRate($request);

        $request->setPackageWeight($oldWeight);
        $request->setPackageQty($oldQty);

        $freeShipping = false;

        if (is_numeric($this->getConfigData('free_shipping_threshold')) &&
                $this->getConfigData('free_shipping_threshold') > 0 &&
                $request->getPackageValue() > $this->getConfigData('free_shipping_threshold')) {
            $freeShipping = true;
        }
        if ($this->getConfigData('allow_free_shipping_promotions') &&
                ($request->getFreeShipping() === true ||
                $request->getPackageQty() == $this->getFreeBoxes())) {
            $freeShipping = true;
        }
        if ($freeShipping) {
            $method = $this->_resultMethodFactory->create();
            $method->setCarrier('matrixrate');
            $method->setCarrierTitle($this->getConfigData('title'));
            $method->setMethod('matrixrate_free');
            $method->setPrice('0.00');
            $method->setMethodTitle($this->getConfigData('free_method_text'));
            $result->append($method);

            if ($this->getConfigData('show_only_free')) {
                return $result;
            }
        }

        foreach ($ratearray as $rate) {
            if (!empty($rate) && $rate['price'] >= 0) {
                $method = $this->_resultMethodFactory->create();

                $method->setCarrier('matrixrate');
                $method->setCarrierTitle($this->getConfigData('title'));

                $method->setMethod('matrixrate_' . $rate['pk']);

                $method->setMethodTitle(__($rate['delivery_type']));

                $shippingPrice = $this->getFinalPriceWithHandlingFee($rate['price']);
                $method->setCost($rate['cost']);
                $method->setDeliveryType($rate['delivery_type']);

                $method->setPrice($shippingPrice);

                $result->append($method);
            }
        }

        return $result;
    }

    public function getRate(\Magento\Quote\Model\Quote\Address\RateRequest $request) {
        return $this->_matrixrateFactory->create()->getNewRate($request, $this->getConfigFlag('zip_range'));
    }

    /**
     * Get allowed shipping methods
     *
     * @return array
     */
    public function getAllowedMethods() {
        return ['matrixrate' => $this->getConfigData('name')];
    }

    public function getCode($type, $code = '') {
        $codes = array(
            'condition_name' => array(
                'package_weight' => __('Weight vs. Destination'),
                'package_value' => __('Price vs. Destination'),
                'package_qty' => __('# of Items vs. Destination'),
            ),
            'condition_name_short' => array(
                'package_weight' => __('Weight'),
                'package_value' => __('Order Subtotal'),
                'package_qty' => __('# of Items'),
            ),
        );

        if (!isset($codes[$type])) {
            throw new LocalizedException(__('Invalid Matrix Rate code type: %s', $type));
        }

        if ('' === $code) {
            return $codes[$type];
        }

        if (!isset($codes[$type][$code])) {
            throw new LocalizedException(__('Invalid Matrix Rate code for type %s: %s', $type, $code));
        }

        return $codes[$type][$code];
    }

}
