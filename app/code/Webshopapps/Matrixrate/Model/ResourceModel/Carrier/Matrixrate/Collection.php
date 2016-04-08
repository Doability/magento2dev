<?php

/**
 * Webshopapps Shipping Module
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * Shipping MatrixRates
 *
 * @category   Webshopapps
 * @package    Webshopapps_Matrixrate
 * @copyright   Copyright (c) 2013 Zowta Ltd (http://www.WebShopApps.com)
 *              Copyright, 2013, Zowta, LLC - US license
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author     Karen Baker <sales@webshopapps.com>
 */

namespace Webshopapps\Matrixrate\Model\ResourceModel\Carrier\Matrixrate;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection {

    protected $_shipTable;
    protected $_countryTable;
    protected $_regionTable;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    protected function _construct() {
        $this->_init('Webshopapps\Matrixrate\Model\Carrier\Matrixrate', 'Webshopapps\Matrixrate\Model\ResourceModel\Carrier\Matrixrate');
        $this->_countryTable = $this->getTable('directory_country');
        $this->_regionTable = $this->getTable('directory_country_region');
    }

    public function _initSelect() {
        parent::_initSelect();

        $this->_select->joinLeft(
                ['country_table' => $this->_countryTable], 'country_table.country_id = main_table.dest_country_id', ['dest_country' => 'iso3_code']
        )->joinLeft(
                ['region_table' => $this->_regionTable], 'region_table.region_id = main_table.dest_region_id', ['dest_region' => 'code']
        );

        $this->addOrder('dest_country', self::SORT_ORDER_ASC);
        $this->addOrder('dest_region', self::SORT_ORDER_ASC);
        $this->addOrder('dest_zip', self::SORT_ORDER_ASC);
    }

    public function setWebsiteFilter($websiteId) {
        return $this->addFieldToFilter("website_id = ?", $websiteId);
    }

    public function setConditionFilter($conditionName) {
        return $this->addFieldToFilter("condition_name = ?", $conditionName);
    }

    public function setCountryFilter($countryId) {
        return $this->addFieldToFilter("dest_country_id = ?", $countryId);
    }

}
