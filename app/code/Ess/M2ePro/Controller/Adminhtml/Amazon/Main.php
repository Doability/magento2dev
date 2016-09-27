<?php

namespace Ess\M2ePro\Controller\Adminhtml\Amazon;

use Ess\M2ePro\Controller\Adminhtml\Context;

abstract class Main extends \Ess\M2ePro\Controller\Adminhtml\Main
{
    protected $amazonFactory;

    //########################################

    public function __construct(
        \Ess\M2ePro\Model\ActiveRecord\Component\Parent\Amazon\Factory $amazonFactory,
        Context $context
    )
    {
        $this->amazonFactory = $amazonFactory;

        parent::__construct($context);
    }

    //########################################

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Ess_M2ePro::amazon');
    }

    //########################################

    protected function getCustomViewNick()
    {
        return \Ess\M2ePro\Helper\Component\Amazon::NICK;
    }

    protected function initResultPage()
    {
        if (!is_null($this->resultPage)) {
            return;
        }

        parent::initResultPage();

        $this->getResultPage()->getConfig()->getTitle()->prepend(
            $this->getHelper('Component\Amazon')->getTitle()
        );

        $this->getResultPage()->setActiveMenu('Ess_M2ePro::amazon');
    }

    //########################################

    protected function setComponentPageHelpLink($view = NULL, $component = NULL)
    {
        if (!is_null($component)) {
            $this->setPageHelpLink($component, $view);
            return;
        }

        $this->setPageHelpLink(\Ess\M2ePro\Helper\Component\Amazon::NICK, $view);
    }

    //########################################
}