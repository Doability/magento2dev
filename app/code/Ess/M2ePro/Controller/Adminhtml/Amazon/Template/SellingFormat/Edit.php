<?php

namespace Ess\M2ePro\Controller\Adminhtml\Amazon\Template\SellingFormat;

use Ess\M2ePro\Controller\Adminhtml\Amazon\Template;

class Edit extends Template
{
    public function execute()
    {
        $template = null;
        if ($id = $this->getRequest()->getParam('id')) {
            $template = $this->amazonFactory->getObjectLoaded('Template\SellingFormat', $id);
        }

        if (is_null($template) && $id) {
            $this->messageManager->addError($this->__('Policy does not exist'));
            return $this->_redirect('*/amazon_template/index');
        }

        $this->getHelper('Data\GlobalData')->setValue('tmp_template', $template);

        $headerTextEdit = $this->__("Edit Selling Format Policy");
        $headerTextAdd = $this->__("Add Selling Format Policy");

        if (!is_null($template)
            && $template->getId()
        ) {
            $headerText = $headerTextEdit;
            $headerText .= ' "'.$this->getHelper('Data')->escapeHtml($template->getTitle()).'"';
        } else {
            $headerText = $headerTextAdd;
        }

        $this->getResultPage()->getConfig()->getTitle()->prepend($this->__('Policies'));
        $this->getResultPage()->getConfig()->getTitle()->prepend($this->__('Selling Format Policies'));
        $this->getResultPage()->getConfig()->getTitle()->prepend($headerText);

        $this->addContent($this->createBlock('Amazon\Template\SellingFormat\Edit'));

        $this->setComponentPageHelpLink('Selling+Format');

        return $this->getResultPage();
    }
}