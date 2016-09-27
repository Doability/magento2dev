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

namespace Oscprofessionals\Vatexempt\Controller\Adminhtml;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\AccountManagementInterface;

class AdminSaveVatexempt extends \Magento\Checkout\Controller\Onepage
{
    /**
     * @var \Magento\Framework\Json\Helper\Data
     *
     */
    protected $_jsonHelper;
    protected $_vatExemptModel;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        CustomerRepositoryInterface $customerRepository,
        AccountManagementInterface $accountManagement,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Translate\InlineInterface $translateInline,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Oscprofessionals\Vatexempt\Model\Checkout\Type\Onepage $vatExemptModel
    ) {
        $this->_jsonHelper = $jsonHelper;
        $this->_vatExemptModel = $vatExemptModel;
        parent::__construct(
            $context,
            $customerSession,
            $customerRepository,
            $accountManagement,
            $coreRegistry,
            $translateInline,
            $formKeyValidator,
            $scopeConfig,
            $layoutFactory,
            $quoteRepository,
            $resultPageFactory,
            $resultLayoutFactory,
            $resultRawFactory,
            $resultJsonFactory
        );
    }

    public function execute()
    {
        if ($this->_expireAjax()) {
            return;
        }

        $data = $this->_jsonHelper->jsonDecode($this->getRequest()->getContent());

        if ((!empty($data['vatpername'])) && (!empty($data['vatcomment']))) {
            $data['vatdeclare'] = 1;
        }
        $vatExemptModel = $this->_vatExemptModel;
        $result = $vatExemptModel->saveVatexempt($data);

        return $this->resultJsonFactory->create()->setData($result);
    }
}
