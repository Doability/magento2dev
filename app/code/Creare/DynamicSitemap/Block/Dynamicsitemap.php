<?php

namespace Creare\DynamicSitemap\Block;

class Dynamicsitemap extends \Magento\Framework\View\Element\Template {

    protected $_cmsCollectionFactory;
    protected $_storeManager;

    public function __construct(
    \Magento\Framework\View\Element\Template\Context $context, \Magento\Cms\Model\ResourceModel\Page\CollectionFactory $cmsCollectionFactory, \Magento\Store\Model\StoreManagerInterface $storeManager, array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_storeManager = $storeManager;
        $this->_cmsCollectionFactory = $cmsCollectionFactory;
    }

    public function _prepareLayout() {      
        return parent::_prepareLayout();
    }

    public function getCreareCMSPages() {
        $storeid = $this->_storeManager->getStore()->getId();
        $collection = $this->_cmsCollectionFactory->create();
        $collection->addFieldToFilter('is_active',1);
        $cms = $collection->addStoreFilter($storeid);
        $html ='';
        foreach ($cms as $cmspage):
            $page = $cmspage->getData();

            if ($page['identifier'] == "no-route" || $page['identifier'] == "enable-cookies" || $page['identifier'] == "empty") { /* do nothing or something here */
            } else {
                if ($page['identifier'] == "home") {
                    $html .= "<li><a href=\"\" title=\"" . $page['title'] . "\">" . $page['title'] . "</a></li>\n"; // this is for a nice local link to home
                } else {
                    $html .= "<li><a href=\"" .$this->getUrl($page['identifier']).  "\" title=\"" . $page['title'] . "\">" . $page['title'] . "</a></li>\n";
                }
            }
        endforeach;     
        return $html;
    }

     public function getCreareCategoryPages() {
        $storeid = $this->_storeManager->getStore()->getId();
        $collection = $this->_cmsCollectionFactory->create();
        $collection->addFieldToFilter('is_active',1);
        $cms = $collection->addStoreFilter($storeid);
        $html ='';
        foreach ($cms as $cmspage):
            $page = $cmspage->getData();

            if ($page['identifier'] == "no-route" || $page['identifier'] == "enable-cookies" || $page['identifier'] == "empty") { /* do nothing or something here */
            } else {
                if ($page['identifier'] == "home") {
                    $html .= "<li><a href=\"\" title=\"" . $page['title'] . "\">" . $page['title'] . "</a></li>\n"; // this is for a nice local link to home
                } else {
                    $html .= "<li><a href=\"" .$this->getUrl($page['identifier']).  "\" title=\"" . $page['title'] . "\">" . $page['title'] . "</a></li>\n";
                }
            }
        endforeach;     
        return $html;
    }
}
