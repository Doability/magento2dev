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
namespace Oscprofessionals\Vatexempt\Model\Plugin;

use Magento\Framework\DataObject\Copy;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\Quote\Address\Item as AddressItem;
use Magento\Sales\Api\Data\OrderItemInterfaceFactory as OrderItemFactory;
use Magento\Sales\Api\Data\OrderItemInterface;
use Closure;

class InsertVatExemptInOrderItem
{
    public function beforeToHtml(\Magento\Sales\Block\Adminhtml\Order\Create\Data $originalBlock){

        $originalBlock->setTemplate('Oscprofessionals_Vatexempt::InsertVatExemptInOrderItem.phtml');

    }
}
