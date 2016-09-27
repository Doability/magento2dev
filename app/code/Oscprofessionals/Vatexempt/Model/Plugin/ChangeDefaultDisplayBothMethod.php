<?php
namespace Oscprofessionals\Vatexempt\Model\Plugin;

use Magento\Tax\Model\Config as MagentoTaxModelConfig;
use Magento\Tax\Helper\Data as MagentoTaxHelperData;
use \Oscprofessionals\Vatexempt\Helper\Data as OscVatHelperData;

class ChangeDefaultDisplayBothMethod {
    /**
     * @var \Oscprofessionals\Vatexempt\Helper\Data
     */

    protected $_helper;

    /**
     * @param \Oscprofessionals\Vatexempt\Helper\Data $helperData
     * @param MagentoTaxHelperData $magentoTaxHelperData
     * @param MagentoTaxModelConfig $magentoTaxModelConfig
     */
    public function __construct(
        OscVatHelperData $helperData,
        MagentoTaxHelperData $magentoTaxHelperData,
        MagentoTaxModelConfig $magentoTaxModelConfig
    ){
       $this->_helper = $helperData;
        $this->_magentoTaxHelperData = $magentoTaxHelperData;
        $this->_magentoTaxModelConfig = $magentoTaxModelConfig;
    }

    /**
     * @return bool
     */

    public function aroundDisplayPriceExcludingTax()
    {
        if ($this->_helper->isCatalogDisplayIncExcTaxEnabled()) {
            return false;
        } else {
            return $this->_magentoTaxHelperData->getPriceDisplayType() == MagentoTaxModelConfig::DISPLAY_TYPE_EXCLUDING_TAX;
        }
    }

    public function aroundDisplayPriceIncludingTax(){

        if($this->_helper->isCatalogDisplayIncExcTaxEnabled()) {
            return false;
        }else{
            return $this->_magentoTaxHelperData->getPriceDisplayType() == MagentoTaxModelConfig::DISPLAY_TYPE_INCLUDING_TAX;
        }
    }

    public function aroundDisplayBothPrices(){

        if($this->_helper->isCatalogDisplayIncExcTaxEnabled()) {
            return true;
        }else{
            return $this->_magentoTaxHelperData->getPriceDisplayType($store=null) == MagentoTaxModelConfig::DISPLAY_TYPE_BOTH;
        }
    }


    public function aroundDisplaySalesPriceInclTax(){

        if($this->_helper->isSalesDisplayIncExcTaxEnabled()) {
            return false;
        }else{
            $this->_magentoTaxModelConfig->displaySalesPricesInclTax($store=null);
        }
    }

    public function aroundDisplaySalesPricesExclTax(){

        if($this->_helper->isSalesDisplayIncExcTaxEnabled()) {
            return false;
        }else{
            return $this->_magentoTaxModelConfig->displaySalesPricesExclTax($store=null);
        }
    }

    public function aroundDisplaySalesBothPrices(){

        if($this->_helper->isSalesDisplayIncExcTaxEnabled()) {
            return true;
        }else{
            return $this->_magentoTaxModelConfig->displaySalesPricesBoth($store=null);
        }
    }
}