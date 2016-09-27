<?php

namespace Emizentech\ShopByBrand\Controller;

class Router implements \Magento\Framework\App\RouterInterface {

    /**
     * @var \Magento\Framework\App\ActionFactory
     */
    protected $actionFactory;

    /**
     * Response
     *
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $_response;
    protected $_brandFactory;

    /**
     * @param \Magento\Framework\App\ActionFactory $actionFactory
     * @param \Magento\Framework\App\ResponseInterface $response
     */
    public function __construct(
    \Magento\Framework\App\ActionFactory $actionFactory, \Magento\Framework\App\ResponseInterface $response, \Emizentech\ShopByBrand\Model\BrandFactory $brandFactory
    ) {
        $this->actionFactory = $actionFactory;
        $this->_response = $response;
        $this->_brandFactory = $brandFactory;
    }

    /**
     * Validate and Match
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return bool
     */
    public function match(\Magento\Framework\App\RequestInterface $request) {
        /*
         * We will search “examplerouter” and “exampletocms” words and make forward depend on word
         * -examplerouter will forward to base router to match inchootest front name, test controller path and test controller class
         * -exampletocms will set front name to cms, controller path to page and action to view
         */
        $identifier = explode('/', trim($request->getPathInfo(), '/'));

        if (strpos($identifier[0], 'brands') !== false && isset($identifier[1])) {

            /*
             * We must set module, controller path and action name + we will set page id 5 witch is about us page on
             * default magento 2 installation with sample data.
             */
            $id = $this->_brandFactory->create()->getCollection()->addFieldToSelect('id')
                  ->addFieldToFilter('url_key', ['eq' => $identifier[1]])
                  ->addFieldToFilter('is_active', \Emizentech\ShopByBrand\Model\Status::STATUS_ENABLED)->getFirstItem()->getId();

            if ($id)
                $request->setModuleName('brand')->setControllerName('view')->setActionName('index')->setParam('id', $id);
            else return;
        } else if (strpos($identifier[0], 'brands') !== false) {
            /*
             * We must set module, controller path and action name for our controller class(Controller/Test/Test.php)
             */
            $request->setModuleName('brand')->setControllerName('index')->setActionName('index');
        } else {
            //There is no match
            return;
        }

        /*
         * We have match and now we will forward action
         */
        return $this->actionFactory->create(
                        'Magento\Framework\App\Action\Forward', ['request' => $request]
        );
    }

}
