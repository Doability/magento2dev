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

use Magento\Framework\Setup;

class Installer implements Setup\SampleData\InstallerInterface
{
    protected $_vatexemptTaxModel;

    public function __construct(\Oscprofessionals\Vatexempt\Model\Tax $vatexemptTaxModel)
	{
        $this->_vatexemptTaxModel = $vatexemptTaxModel;
    }

    /**
     * {@inheritdoc}
     */
    public function install()
    {
        $this->_vatexemptTaxModel->install();
    }
}
