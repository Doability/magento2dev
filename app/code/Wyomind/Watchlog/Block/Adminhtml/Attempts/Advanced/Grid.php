<?php

/**
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Watchlog\Block\Adminhtml\Attempts\Advanced;

/**
 * Grid definition
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{

    protected $_attemptsCollectionFactory;
    protected $_coreHelper;
    

    /**
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Wyomind\Core\Helper\Data $coreHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Wyomind\Watchlog\Model\ResourceModel\Attempts\CollectionFactory $attemptsCollectionFactory,
        \Wyomind\Core\Helper\Data $coreHelper,
        array $data = []
    ) {
        $this->_attemptsCollectionFactory = $attemptsCollectionFactory;
        $this->_coreHelper = $coreHelper;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * initializer
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('attemptsGrid');
        $this->setDefaultSort('failed');
        $this->setDefaultDir('DESC');
    }

    /**
     * Prepare collection
     * @return \Magento\Backend\Block\Widget\Grid
     */
    public function _prepareCollection()
    {
        $collection = $this->_attemptsCollectionFactory->create()->getHistory();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Prepare columns
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     */
    protected function _prepareColumns()
    {
        $this->addColumn('ip', ['header' => __('IP'), 'index' => 'ip', 'renderer'=>"\Wyomind\Watchlog\Block\Adminhtml\Attempts\Renderer\Ip"]);
        $this->addColumn('date', ['header' => __('Last Attempts'), 'index' => 'date', 'type'=>'datetime']);
        $this->addColumn('attempts', ['header' => __('Attempts'), 'index' => 'attempts']);
        $this->addColumn('failed', ['header' => __('Failed'), 'index' => 'failed']);
        $this->addColumn('succeeded', ['header' => __('Succeeded'), 'index' => 'succeeded']);
        return parent::_prepareColumns();
    }

    /**
     * Row click url
     * @param  \Magento\Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return "";
    }
}
