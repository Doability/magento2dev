<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Model\Magento\Tax\Rule;

class Builder extends \Ess\M2ePro\Model\AbstractModel
{
    const TAX_CLASS_NAME_PRODUCT  = 'M2E Pro Product Tax Class';
    const TAX_CLASS_NAME_CUSTOMER = 'M2E Pro Customer Tax Class';

    const TAX_RATE_CODE = 'M2E Pro Tax Rate';
    const TAX_RULE_CODE = 'M2E Pro Tax Rule';

    protected $classModelFactory;
    protected $rateFactory;
    protected $ruleFactory;
    /** @var $rule \Magento\Tax\Model\Calculation\Rule */
    protected $rule = NULL;

    //########################################

    public function __construct(
        \Magento\Tax\Model\ClassModelFactory $classModelFactory,
        \Magento\Tax\Model\Calculation\RateFactory $rateFactory,
        \Magento\Tax\Model\Calculation\RuleFactory $ruleFactory,
        \Ess\M2ePro\Helper\Factory $helperFactory,
        \Ess\M2ePro\Model\Factory $modelFactory
    )
    {
        $this->classModelFactory = $classModelFactory;
        $this->rateFactory = $rateFactory;
        $this->ruleFactory = $ruleFactory;
        parent::__construct($helperFactory, $modelFactory);
    }

    //########################################

    public function getRule()
    {
        return $this->rule;
    }

    //########################################

    public function buildTaxRule($rate = 0, $countryId, $customerTaxClassId = NULL)
    {
        // Init product tax class
        // ---------------------------------------
        $productTaxClass = $this->classModelFactory->create()->getCollection()
            ->addFieldToFilter('class_name', self::TAX_CLASS_NAME_PRODUCT)
            ->addFieldToFilter('class_type', \Magento\Tax\Model\ClassModel::TAX_CLASS_TYPE_PRODUCT)
            ->getFirstItem();

        if (is_null($productTaxClass->getId())) {
            $productTaxClass->setClassName(self::TAX_CLASS_NAME_PRODUCT)
                ->setClassType(\Magento\Tax\Model\ClassModel::TAX_CLASS_TYPE_PRODUCT);
            $productTaxClass->save();
        }
        // ---------------------------------------

        // Init customer tax class
        // ---------------------------------------
        if (is_null($customerTaxClassId)) {
            $customerTaxClass = $this->classModelFactory->create()->getCollection()
                ->addFieldToFilter('class_name', self::TAX_CLASS_NAME_CUSTOMER)
                ->addFieldToFilter('class_type', \Magento\Tax\Model\ClassModel::TAX_CLASS_TYPE_CUSTOMER)
                ->getFirstItem();

            if (is_null($customerTaxClass->getId())) {
                $customerTaxClass->setClassName(self::TAX_CLASS_NAME_CUSTOMER)
                    ->setClassType(\Magento\Tax\Model\ClassModel::TAX_CLASS_TYPE_CUSTOMER);
                $customerTaxClass->save();
            }

            $customerTaxClassId = $customerTaxClass->getId();
        }
        // ---------------------------------------

        // Init tax rate
        // ---------------------------------------
        $taxCalculationRate = $this->rateFactory->create()->load(self::TAX_RATE_CODE, 'code');

        $taxCalculationRate->setCode(self::TAX_RATE_CODE)
            ->setRate((float)$rate)
            ->setTaxCountryId((string)$countryId)
            ->setTaxPostcode('*')
            ->setTaxRegionId(0);
        $taxCalculationRate->save();
        // ---------------------------------------

        // Combine tax classes and tax rate in tax rule
        // ---------------------------------------
        $this->rule = $this->ruleFactory->create()->load(self::TAX_RULE_CODE, 'code');

        $this->rule->setCode(self::TAX_RULE_CODE)
            ->setTaxCustomerClass([$customerTaxClassId])
            ->setTaxProductClass([$productTaxClass->getId()])
            ->setTaxRate([$taxCalculationRate->getId()]);
        $this->rule->save();
        // ---------------------------------------
    }

    //########################################
}