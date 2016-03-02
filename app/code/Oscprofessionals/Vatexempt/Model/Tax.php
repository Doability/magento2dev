<?php

namespace Oscprofessionals\Vatexempt\Model;

class Tax
{
    protected $_taxRuleRepository;
    protected $_ruleFactory;
    protected $_taxRateFactory;

    public function __construct(
        \Magento\Tax\Api\TaxRuleRepositoryInterface $taxRuleRepository,
        \Magento\Tax\Api\Data\TaxRuleInterfaceFactory $ruleFactory,
        \Magento\Tax\Model\Calculation\RateFactory $taxRateFactory
    ) {
        $this->_taxRuleRepository = $taxRuleRepository;
        $this->_ruleFactory = $ruleFactory;
        $this->_taxRateFactory = $taxRateFactory;
    }

    public function install()
    {
		$vatExemptTaxRate = $this->_taxRateFactory->create()->loadByCode('Vat Exempt');
		$taxRule = $this->_ruleFactory->create();

		$taxRule->setCode('Vat Exempt')
			->setTaxRateIds([$vatExemptTaxRate->getId()])
			->setCustomerTaxClassIds(['3'])
			->setProductTaxClassIds(['2'])
			->setPriority(0)
			->setCalculateSubtotal('')
			->setPosition(0);
		$this->_taxRuleRepository->save($taxRule);
    }
}
