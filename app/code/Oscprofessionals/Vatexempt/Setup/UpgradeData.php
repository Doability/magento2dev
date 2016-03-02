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

namespace Oscprofessionals\Vatexempt\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup;

class UpgradeData implements UpgradeDataInterface
{
	protected $_executor;
    protected $_installer;

	public function __construct(
		Setup\SampleData\Executor $executor,
		Installer $installer
	) {
		$this->_executor = $executor;
        $this->_installer = $installer;
    }

    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '2.0.1', '<')) {
			
			/**
			* install vat exempt tax class
			*/
			$data = [
				[
                    'class_name' => 'Vatexempt Class',
                    'class_type' => \Magento\Tax\Model\ClassModel::TAX_CLASS_TYPE_PRODUCT,
				]
			];
			
			foreach ($data as $row) {
				$setup->getConnection()->insertForce($setup->getTable('tax_class'), $row);
			}

            /**
			* install vat exempt tax rate
			*/
			$data = [
                [
                    'tax_country_id' => 'US',
                    'tax_region_id' => '*',
                    'tax_postcode' => '*',
                    'code' => 'Vat Exempt',
                    'rate' => '0.00'
                ]
            ];

            foreach ($data as $row) {
                $setup->getConnection()->insertForce($setup->getTable('tax_calculation_rate'), $row);
            }

			/**
			* install vat exempt tax rule
			*/
			$this->_executor->exec($this->_installer);
        }

        $setup->endSetup();
    }
}
