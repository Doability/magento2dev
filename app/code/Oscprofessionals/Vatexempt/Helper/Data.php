<?php
/**
 * @author      Oscprofessionals Team (support@oscprofessionals.com)
 * @copyright   Copyright (c) 2015 Oscprofessionals (http://www.oscprofessionals.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @category    Oscprofessionals
 * @package     Oscprofessionals_Vatexempt
 */

namespace Oscprofessionals\Vatexempt\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_vatExemptProductIds = array(); //all vat exempted products ids
    protected $_checkoutSession;
    protected $_product;
    protected $_taxCalculation;
    protected $_customerGroup;
    protected $_customerGroupResource;
    protected $_vatExemptedProductTaxClassId = '';
	protected $_backendConfig;
	const XML_PATH_MODULE_ENABLED = 'oscp/vatexempt/is_enabled';
	const XML_VAT_EXEMPT_CLASS = 'system/vat_exempt/vat_exempt_class';
	const XML_VAT_EXEMPT_TAX_RATE = 'system/vat_exempt/vat_exempt_tax_rate';
	const XML_VAT_EXEMPT_TAX_RULE = 'system/vat_exempt/vat_exempt_tax_rule';

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Catalog\Model\Product $product,
        \Magento\Tax\Model\Calculation $taxCalculation,
        \Magento\Customer\Model\Group $customerGroup,
        \Magento\Customer\Model\ResourceModel\Group\CollectionFactory $customerGroupResource,
        \Magento\Tax\Model\ResourceModel\TaxClass\CollectionFactory $taxClassFactory,
		\Magento\Backend\App\ConfigInterface $backendConfig
    ) {
        $this->_checkoutSession = $checkoutSession;
        $this->_product = $product;
        $this->_taxCalculation = $taxCalculation;
        $this->_customerGroup = $customerGroup;
        $this->_customerGroupResource = $customerGroupResource;
        $this->_taxClassFactory = $taxClassFactory;
		$this->_backendConfig = $backendConfig;
        parent::__construct($context);
    }

	/**
     * Check whether module enabled
     *
     * @return bool
     */
    public function isModuleEnabled()
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_PATH_MODULE_ENABLED,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

	public function getVatExemptClassName()
	{
		return $this->_backendConfig->getValue(self::XML_VAT_EXEMPT_CLASS);
	}

	public function getVatExemptTaxRate()
	{
		return $this->_backendConfig->getValue(self::XML_VAT_EXEMPT_TAX_RATE);
	}

	public function getVatExemptTaxRule()
	{
		return $this->_backendConfig->getValue(self::XML_VAT_EXEMPT_TAX_RULE);
	}

    public function getCartVatExemptProductIds()
    {
        $quote = $this->getCheckoutQuote();

        foreach ($quote->getAllItems() as $item) {
            try {
                if ($item->getVatExempt() == 1) {
                    $this->_vatExemptProductIds[$item->getProductId()] = $item->getProductId();
                }
            }
            catch (\Exception $e) {
                $this->messageManager->addError('Vat Exempt attribute is not set for any product in cart.');
                $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);

                return;
            }
        }

        return $this->_vatExemptProductIds;
    }

    protected function getCheckoutSession()
    {
        return $this->_checkoutSession;
    }

    protected function getCheckoutQuote()
    {
        $quote = $this->getCheckoutSession()->getQuote();

        return $quote;
    }

    public function getVatExemptProductTaxClassId()
    {
        $groups = $this->_taxClassFactory->create()->toOptionArray();
		$VatExemptClassName =  $this->getVatExemptClassName();		

        try {
            foreach ($groups as $ids => $group) {
                if ($group['label'] == $VatExemptClassName) {
                    return $group['value'];
                }
            }
        }
        catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);

            return;
        }
    }
}
