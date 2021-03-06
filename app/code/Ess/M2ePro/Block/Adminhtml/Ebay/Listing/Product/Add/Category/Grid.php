<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Block\Adminhtml\Ebay\Listing\Product\Add\Category;

class Grid extends \Ess\M2ePro\Block\Adminhtml\Ebay\Listing\Product\Add\Grid
{
    private $selectedIds = array();

    private $currentCategoryId = NULL;

    //########################################

    private function getCollectionIds()
    {
        if (!is_null($ids = $this->getData('collection_ids'))) {
            return $ids;
        }

        $ids = $this->getHelper('Magento\Category')->getProductsFromCategories(
            array($this->getCurrentCategoryId()), $this->_getStore()->getId()
        );

        $this->setData('collection_ids',$ids);
        return $ids;
    }

    //########################################

    protected function _prepareMassaction()
    {
        $this->getMassactionBlock()->setFormFieldName('ids');

        $ids = $this->getRequest()->getPost($this->getMassactionBlock()->getFormFieldNameInternal());

        if ($this->getRequest()->isXmlHttpRequest() && !$this->getRequest()->getParam('category_change')) {
            return parent::_prepareMassaction();
        }

        $ids = array_filter(explode(',',$ids));
        $ids = array_merge($ids,$this->getSelectedIds());
        $ids = array_intersect($ids,$this->getCollectionIds());
        $ids = array_values(array_unique($ids));

        $this->getRequest()->setPostValue($this->getMassactionBlock()->getFormFieldNameInternal(), implode(',',$ids));

        return parent::_prepareMassaction();
    }

    //########################################

    public function setSelectedIds(array $ids)
    {
        $this->selectedIds = $ids;
        return $this;
    }

    public function getSelectedIds()
    {
        return $this->selectedIds;
    }

    // ---------------------------------------

    public function setCurrentCategoryId($currentCategoryId)
    {
        $this->currentCategoryId = $currentCategoryId;
        return $this;
    }

    public function getCurrentCategoryId()
    {
        return $this->currentCategoryId;
    }

    //########################################

    /**
     * @inheritdoc
     */
    public function setCollection($collection)
    {
        $collection->joinTable(
            array('ccp' => $collection->getResource()->getTable('catalog_category_product')),
            'product_id=entity_id',
            array('category_id' => 'category_id')
        );

        $collection->addFieldToFilter('category_id', $this->currentCategoryId);

        parent::setCollection($collection);
    }

    //########################################

    protected function getSelectedProductsCallback()
    {
        return <<<JS
(function() {
    return function(callback) {

        saveSelectedProducts(function(transport) {

            new Ajax.Request('{$this->getUrl('*/*/getSessionProductsIds', array('_current' => true))}', {
                method: 'get',
                onSuccess: function(transport) {
                    var massGridObj = {$this->getMassactionBlock()->getJsObjectName()};

                    massGridObj.initialCheckedString = massGridObj.checkedString;

                    var response = transport.responseText.evalJSON();
                    var ids = response['ids'].join(',');

                    callback(ids);
                }
            });

        });
    }
})()
JS;

    }

    //########################################

    protected function _toHtml()
    {
        $html = parent::_toHtml();

        if ($this->getRequest()->getParam('category_change')) {
            $checkedString = implode(',', array_intersect($this->getCollectionIds(), $this->selectedIds));

            $this->js->add(<<<JS
    {$this->getMassactionBlock()->getJsObjectName()}.checkedString = '{$checkedString}';
    {$this->getMassactionBlock()->getJsObjectName()}.initCheckboxes();
    {$this->getMassactionBlock()->getJsObjectName()}.checkCheckboxes();
    {$this->getMassactionBlock()->getJsObjectName()}.updateCount();

    {$this->getMassactionBlock()->getJsObjectName()}.initialCheckedString =
        {$this->getMassactionBlock()->getJsObjectName()}.checkedString;
JS
            );
        }

        if ($this->getRequest()->isXmlHttpRequest()) {
            return $html;
        }

        return <<<HTML
        
<div class="page-layout-admin-2columns-left" style="margin-top: 20px;">
    <div class="page-columns">
        <div class="main-col">
            {$html}
        </div>
        <div class="side-col">
            {$this->getTreeBlock()->toHtml()}
        </div>
    </div>
</div>
HTML;

    }

    //########################################
}