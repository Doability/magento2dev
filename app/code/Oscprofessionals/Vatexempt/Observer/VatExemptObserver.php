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

use \Magento\Framework\Event\Observer;
use \Magento\Framework\Event\ObserverInterface;

class VatExemptObserver implements ObserverInterface
{
    protected $_helper;
    protected $_taxCalculationModel;

    public function __construct(
        \Oscprofessionals\Vatexempt\Helper\Data $helper,
        \Magento\Tax\Model\Calculation $taxCalculationModel,
        \Magento\Tax\Helper\Data $taxHelper,
        \Magento\Catalog\Helper\Data $catalogHelper
    ) {
        $this->_helper = $helper;
        $this->_taxCalculationModel = $taxCalculationModel;
        $this->taxHelper = $taxHelper;
        $this->catalogHelper = $catalogHelper;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $quote = $observer->getQuote();
        $this->setCartProductTaxAmount($quote);
    }

    public function setCartProductTaxAmount($quote)
    {
        $customer = $quote->getCustomer();
        $storeId = $customer->getStoreId();
        $taxCalculationModel = $this->_taxCalculationModel;
        $request = $taxCalculationModel->getRateRequest($quote->getShippingAddress(), $quote->getBillingAddress(), null, $storeId);
        $quoteVatExemptDeclare = $quote->getVatdeclare();

        switch($quoteVatExemptDeclare)
        {
            case 1:
                foreach ($quote->getAllItems() as $item) {
                    if ($item->getVatExempt() == 1) {

                        //Catalog Prices Tax Calculation base on "Including Tax with Custom Option"
                        $taxCatalogPrice = $this->taxHelper->priceIncludesTax();

                        if($taxCatalogPrice==true){

                           $finalPrice = $item->getProduct()->getFinalPrice();

                           $tierPrice = $this->getTierPriceForVatexempt($item->getQty(),$item->getProduct());


                           if (!empty($tierPrice) && ($tierPrice < $finalPrice)) {
                               $finalPrice = $tierPrice;
                             }

                            $finalPriceExcludingTax = $this->catalogHelper->getTaxPrice($item->getProduct(),$finalPrice,false);

                           $item->getProduct()->setPrice($finalPriceExcludingTax);
                           $item->getProduct()->setBasePrice($finalPriceExcludingTax);
                        }

                        $vatExemptClassId = $this->_helper->getVatExemptProductTaxClassId();
                        $item->getProduct()->setTaxClassId($vatExemptClassId);
                        $rate = $taxCalculationModel->getRate($request->setProductClassId($item->getProduct()->getTaxClassId()));
                        $item->setTaxPercent($rate);
                        $item->setTaxAmount(0);
                        $item->setBaseTaxAmount(0);
                    }
                }
                break;
            default:
                foreach ($quote->getAllItems() as $item) {
                    $rate = $taxCalculationModel->getRate($request->setProductClassId($item->getProduct()->getTaxClassId()));
                    $item->setTaxPercent($rate);
                    $item->setTaxAmount();
                    $item->setBaseTaxAmount();
                }
                break;
        }
    }


    /**
    * collect Tier price
    * @param quote product collection
    * @return  float
    */
    public function getTierPriceForVatexempt($qty = null, $product){
        
        $tierPrice ='';
        if ($qty) {
            //get product Tier price
            $prices = $product->getTierPrice();

            foreach($prices as $price){
                if($qty >= $price['price_qty']){
                   $tierPrice  = $price['website_price'];
                }
            }
           return $tierPrice;
        }
        return $tierPrice;
    }
}

