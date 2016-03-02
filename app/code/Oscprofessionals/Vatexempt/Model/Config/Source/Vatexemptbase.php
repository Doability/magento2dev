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
namespace Oscprofessionals\Vatexempt\Model\Config\Source;

class Vatexemptbase implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => 0, 'label' => __('--Please Select--')],
            ['value' => 1, 'label' => __('Product Base')]
        ];
    }
}
