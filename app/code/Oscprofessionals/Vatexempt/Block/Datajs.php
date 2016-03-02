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
namespace Oscprofessionals\Vatexempt\Block;

class Datajs extends \Magento\Framework\View\Element\Template
{	

	public function getVatexemptEnabled()
    {
        return $this->helper('Oscprofessionals\Vatexempt\Helper\Data')->isModuleEnabled();
    }
}