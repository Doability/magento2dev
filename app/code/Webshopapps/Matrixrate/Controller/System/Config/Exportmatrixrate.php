<?php

namespace Webshopapps\Matrixrate\Setup\Config;

use Magento\Framework\App\ResponseInterface;
use Magento\Config\Controller\Adminhtml\System\ConfigSectionChecker;
use Magento\Framework\App\Filesystem\DirectoryList;

class Exportmatrixrate extends \Magento\Config\Controller\Adminhtml\System\AbstractConfig {

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $_fileFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Config\Model\Config\Structure $configStructure
     * @param \Magento\Config\Controller\Adminhtml\System\ConfigSectionChecker $sectionChecker
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
    \Magento\Backend\App\Action\Context $context, 
            \Magento\Config\Model\Config\Structure $configStructure, 
            ConfigSectionChecker $sectionChecker, 
            \Magento\Framework\App\Response\Http\FileFactory $fileFactory, 
            \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_storeManager = $storeManager;
        $this->_fileFactory = $fileFactory;
        parent::__construct($context, $configStructure, $sectionChecker);
    }

    /**
     * Export shipping table rates in csv format
     *
     */
    public function execute() {
        $fileName = 'matrixrates.csv';
        /** @var $gridBlock Mage_Adminhtml_Block_Shipping_Carrier_Tablerate_Grid */
        $gridBlock = $this->_view->getLayout()->createBlock('Webshopapps\Matrixrate\Block\Adminhtml\Shipping\Carrier\Matrixrate\Grid');
        $website = $this->_storeManager->getWebsite($this->getRequest()->getParam('website'));
        if ($this->getRequest()->getParam('conditionName')) {
            $conditionName = $this->getRequest()->getParam('conditionName');
        } else {
            $conditionName = $website->getConfig('carriers/matrixrate/condition_name');
        }
        $gridBlock->setWebsiteId($website->getId())->setConditionName($conditionName);
        $content = $gridBlock->getCsvFile();
        $this->$this->_fileFactory->create($fileName, $content,DirectoryList::VAR_DIR);
    }

    protected function _isAllowed() {
        return true;
    }

}
