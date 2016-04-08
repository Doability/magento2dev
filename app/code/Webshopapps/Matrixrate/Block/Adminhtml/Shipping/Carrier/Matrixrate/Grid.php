<?php

namespace Webshopapps\Matrixrate\Block\Adminhtml\Shipping\Carrier\Matrixrate;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended {

    /**
     * Prepare table columns
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareColumns() {
        $this->addColumn('dest_country', [
            'header' => __('Country'),
            'index' => 'dest_country',
            'default' => '*',
        ]);

        $this->addColumn('dest_region', [
            'header' => __('Region/State'),
            'index' => 'dest_region',
            'default' => '*',
        ]);

        $this->addColumn('dest_city', [
            'header' => __('City'),
            'index' => 'dest_city',
            'default' => '*',
        ]);

        $this->addColumn('dest_zip', [
            'header' => __('Zip/Postal Code From'),
            'index' => 'dest_zip',
        ]);

        $this->addColumn('dest_zip_to', [
            'header' => __('Zip/Postal Code To'),
            'index' => 'dest_zip_to',
        ]);

        $label = $this->_tablerate->getCode('condition_name_short', $this->getConditionName());

        $this->addColumn('condition_from_value', [
            'header' => $label . ' From',
            'index' => 'condition_from_value',
        ]);

        $this->addColumn('condition_to_value', [
            'header' => $label . ' To',
            'index' => 'condition_to_value',
        ]);

        $this->addColumn('price', [
            'header' => __('Shipping Price'),
            'index' => 'price',
        ]);

        $this->addColumn('delivery_type', [
            'header' => __('Delivery Type'),
            'index' => 'delivery_type',
        ]);

        return parent::_prepareColumns();
    }

    protected function _prepareCollection() {
        /** @var $collection \Magento\OfflineShipping\Model\ResourceModel\Carrier\Tablerate\Collection */
        $collection = $this->_collectionFactory->create();
        $collection->setConditionFilter($this->getConditionName())->setWebsiteFilter($this->getWebsiteId());
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Website filter
     *
     * @var int
     */
    protected $_websiteId;

    /**
     * Condition filter
     *
     * @var string
     */
    protected $_conditionName;

    public function __construct(
    \Magento\Backend\Block\Template\Context $context, \Magento\Backend\Helper\Data $backendHelper, \Webshopapps\Matrixrate\Model\ResourceModel\Carrier\Matrixrate\CollectionFactory $collectionFactory, \Webshopapps\Matrixrate\Model\Carrier\Matrixrate $tablerate, array $data = []
    ) {
        $this->_collectionFactory = $collectionFactory;
        $this->_tablerate = $tablerate;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Define grid properties
     *
     * @return void
     */
    public function _construct() {
        parent::_construct();
        $this->setId('shippingTablerateGrid');
        $this->_exportPageSize = 10000;
    }

    /**
     * Set current website
     *
     * @param int $websiteId
     * @return Mage_Adminhtml_Block_Shipping_Carrier_Tablerate_Grid
     */
    public function setWebsiteId($websiteId) {
        $this->_websiteId = $this->_storeManager->getWebsite($websiteId)->getId();
        return $this;
    }

    /**
     * Retrieve current website id
     *
     * @return int
     */
    public function getWebsiteId() {
        if ($this->_websiteId === null) {
            $this->_websiteId = $this->_storeManager->getWebsite()->getId();
        }
        return $this->_websiteId;
    }

    /**
     * Set current website
     *
     * @param int $websiteId
     * @return Mage_Adminhtml_Block_Shipping_Carrier_Tablerate_Grid
     */
    public function setConditionName($name) {
        $this->_conditionName = $name;
        return $this;
    }

    /**
     * Retrieve current website id
     *
     * @return int
     */
    public function getConditionName() {
        return $this->_conditionName;
    }

}
