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

namespace Webshopapps\Matrixrate\Model\ResourceModel\Carrier;

use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\DirectoryList;

class Matrixrate extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb {

    /**
     * Import table rates website ID
     *
     * @var int
     */
    protected $_importWebsiteId = 0;

    /**
     * Errors in import process
     *
     * @var array
     */
    protected $_importErrors = [];

    /**
     * Count of imported table rates
     *
     * @var int
     */
    protected $_importedRows = 0;

    /**
     * Array of unique table rate keys to protect from duplicates
     *
     * @var array
     */
    protected $_importUniqueHash = [];

    /**
     * Array of countries keyed by iso2 code
     *
     * @var array
     */
    protected $_importIso2Countries;

    /**
     * Array of countries keyed by iso3 code
     *
     * @var array
     */
    protected $_importIso3Countries;

    /**
     * Associative array of countries and regions
     * [country_id][region_code] = region_id
     *
     * @var array
     */
    protected $_importRegions;

    /**
     * Import Table Rate condition name
     *
     * @var string
     */
    protected $_importConditionName;

    /**
     * Array of condition full names
     *
     * @var array
     */
    protected $_conditionFullNames = [];

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_coreConfig;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\OfflineShipping\Model\Carrier\Tablerate
     */
    protected $_carrierTablerate;

    /**
     * @var \Magento\Directory\Model\ResourceModel\Country\CollectionFactory
     */
    protected $_countryCollectionFactory;

    /**
     * @var \Magento\Directory\Model\ResourceModel\Region\CollectionFactory
     */
    protected $_regionCollectionFactory;

    /**
     * Filesystem instance
     *
     * @var \Magento\Framework\Filesystem
     */
    protected $_filesystem;

    protected function _construct() {
        $this->_init('shipping_matrixrate', 'pk');
    }

    public function __construct(
    \Magento\Framework\Model\ResourceModel\Db\Context $context, \Psr\Log\LoggerInterface $logger, \Magento\Framework\App\Config\ScopeConfigInterface $coreConfig, \Magento\Store\Model\StoreManagerInterface $storeManager, \Webshopapps\Matrixrate\Model\Carrier\Matrixrate $carrierTablerate, \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory, \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionCollectionFactory, \Magento\Framework\Filesystem $filesystem, $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->_coreConfig = $coreConfig;
        $this->_logger = $logger;
        $this->_storeManager = $storeManager;
        $this->_carrierTablerate = $carrierTablerate;
        $this->_countryCollectionFactory = $countryCollectionFactory;
        $this->_regionCollectionFactory = $regionCollectionFactory;
        $this->_filesystem = $filesystem;
    }

    public function getNewRate(\Magento\Quote\Model\Quote\Address\RateRequest $request, $zipRangeSet = 0) {
        $connection = $this->getConnection();
        $postcode = $request->getDestPostcode();
        $bind = [
            ':website_id' => (int) $request->getWebsiteId(),
            ':country_id' => $request->getDestCountryId(),
            ':region_id' => (int) $request->getDestRegionId(),
            ':postcode' => (string) $request->getDestPostcode(),
            ':city' => (string) $request->getDestCity()
        ];
        if ($zipRangeSet && is_numeric($postcode)) {
            #  Want to search for postcodes within a range
            $zipSearchString = ' AND ' . $postcode . ' BETWEEN dest_zip AND dest_zip_to )';
        } else {
            $zipSearchString = $connection->quoteInto(" AND ? LIKE dest_zip )", $postcode);
        }

        for ($j = 0; $j < 10; $j++) {
            $select = $connection->select()->from(
                            $this->getMainTable()
                    )->where(
                            $connection->quoteInto("website_id = ?", $bind[':website_id'])
                    )->order(
                    ['dest_country_id DESC', 'dest_region_id DESC', 'dest_zip DESC']
            );
            switch ($j) {
                case 0:
                    $select->where(
                            $connection->quoteInto(" (dest_country_id=? ", $bind[':country_id']) .
                            $connection->quoteInto(" AND dest_region_id=? ", $bind[':region_id']) .
                            $connection->quoteInto(" AND STRCMP(LOWER(dest_city),LOWER(?)) = 0  ", $bind[':city']) .
                            $zipSearchString
                    );
                    break;
                case 1:
                    $select->where(
                            $connection->quoteInto(" (dest_country_id=? ", $bind[':country_id']) .
                            $connection->quoteInto(" AND dest_region_id=?  AND dest_city=''", $bind[':region_id']) .
                            $zipSearchString
                    );
                    break;
                case 2:
                    $select->where(
                            $connection->quoteInto(" (dest_country_id=? ", $request->getDestCountryId()) .
                            $connection->quoteInto(" AND dest_region_id=? ", $request->getDestRegionId()) .
                            $connection->quoteInto(" AND STRCMP(LOWER(dest_city),LOWER(?)) = 0  AND dest_zip='')", $bind[':city'])
                    );
                    break;
                case 3:
                    $select->where(
                            $connection->quoteInto("  (dest_country_id=? ", $bind[':country_id']) .
                            $connection->quoteInto(" AND STRCMP(LOWER(dest_city),LOWER(?)) = 0  AND dest_region_id='0'", $bind[':city']) .
                            $zipSearchString
                    );
                    break;
                case 4:
                    $select->where(
                            $connection->quoteInto("  (dest_country_id=? ", $bind[':country_id']) .
                            $connection->quoteInto(" AND STRCMP(LOWER(dest_city),LOWER(?)) = 0  AND dest_region_id='0' AND dest_zip='') ", $bind[':city'])
                    );
                    break;
                case 5:
                    $select->where(
                            $connection->quoteInto("  (dest_country_id=? AND dest_region_id='0' AND dest_city='' ", $bind[':country_id']) .
                            $zipSearchString
                    );
                    break;
                case 6:
                    $select->where(
                            $connection->quoteInto("  (dest_country_id=? ", $bind[':country_id']) .
                            $connection->quoteInto(" AND dest_region_id=? AND dest_city='' AND dest_zip='') ", $bind[':region_id'])
                    );
                    break;

                case 7:
                    $select->where(
                            $connection->quoteInto("  (dest_country_id=? AND dest_region_id='0' AND dest_city='' AND dest_zip='') ", $bind[':country_id'])
                    );
                    break;
                case 8:
                    $select->where(
                            "  (dest_country_id='0' AND dest_region_id='0'" .
                            $zipSearchString
                    );
                    break;

                case 9:
                    $select->where(
                            "  (dest_country_id='0' AND dest_region_id='0' AND dest_zip='')"
                    );
                    break;
            }


            if (is_array($request->getMRConditionName())) {
                $i = 0;
                foreach ($request->getMRConditionName() as $conditionName) {
                    if ($i == 0) {
                        $select->where('condition_name=?', $conditionName);
                    } else {
                        $select->orWhere('condition_name=?', $conditionName);
                    }
                    $select->where('condition_from_value<=?', $request->getData($conditionName));


                    $i++;
                }
            } else {
                $select->where('condition_name=?', $request->getMRConditionName());
                $select->where('condition_from_value<=?', $request->getData($request->getMRConditionName()));
                $select->where('condition_to_value>=?', $request->getData($request->getMRConditionName()));
            }


            $newdata = array();
            $row = $connection->fetchAll($select, $bind);
            if (!empty($row)) {
                // have found a result or found nothing and at end of list!
                foreach ($row as $data) {
                    $newdata[] = $data;
                }
                break;
            }
        }
        return $newdata;
    }

    protected function _getConditionFullName($conditionName) {
        if (!isset($this->_conditionFullNames[$conditionName])) {
            $name = $this->_carrierTablerate->getCode('condition_name_short', $conditionName);
            $this->_conditionFullNames[$conditionName] = $name;
        }

        return $this->_conditionFullNames[$conditionName];
    }

    public function uploadAndImport(\Magento\Framework\DataObject $object) {
        $csvFile = $_FILES["groups"]["tmp_name"]["matrixrate"]["fields"]["import"]["value"];

        if (!empty($csvFile)) {

            $csv = trim(file_get_contents($csvFile));

            $table = $this->getMainTable();
            $websiteModel = $this->_storeManager->getWebsite($object->getScopeId());
            $websiteId = (int) $websiteModel->getId();
            $this->_importWebsiteId = (int) $websiteModel->getId();
            $this->_importUniqueHash = [];
            $this->_importErrors = [];
            $this->_importedRows = 0;

            /*
              getting condition name from post instead of the following commented logic
             */
            if ($object->getData('groups/matrixrate/fields/condition_name/inherit') == '1') {
                $conditionName = (string) $this->_coreConfig->getValue('carriers/matrixrate/condition_name', 'default');
            } else {
                $conditionName = $object->getData('groups/matrixrate/fields/condition_name/value');
            }
            $conditionFullName = $this->_getConditionFullName($conditionName);
            if (!empty($csv)) {
                $exceptions = array();
                $csvLines = explode("\n", $csv);
                $csvLine = array_shift($csvLines);
                $csvLine = $this->_getCsvValues($csvLine);
                if (count($csvLine) < 7) {
                    $exceptions[0] = __('Invalid Matrix Rates File Format');
                }

                $countryCodes = array();
                $regionCodes = array();
                foreach ($csvLines as $k => $csvLine) {
                    $csvLine = $this->_getCsvValues($csvLine);
                    if (count($csvLine) > 0 && count($csvLine) < 7) {
                        $exceptions[0] = __('Invalid Matrix Rates File Format');
                    } else {
                        $countryCodes[] = $csvLine[0];
                        $regionCodes[] = $csvLine[1];
                    }
                }

                if (empty($exceptions)) {
                    $data = array();
                    $countryCodesToIds = array();
                    $regionCodesToIds = array();
                    $countryCodesIso2 = array();

                    /** @var $collection \Magento\Directory\Model\ResourceModel\Country\Collection */
                    $collection = $this->_countryCollectionFactory->create();
                    $collection->addCountryCodeFilter($countryCodes);
                    foreach ($collection->getData() as $row) {
                        $countryCodesToIds[$row['iso2_code']] = $row['country_id'];
                        $countryCodesToIds[$row['iso3_code']] = $row['country_id'];
                        $countryCodesIso2[] = $row['iso2_code'];
                    }

                    /** @var $collection \Magento\Directory\Model\ResourceModel\Region\Collection */
                    $collection = $this->_regionCollectionFactory->create()->addRegionCodeFilter($regionCodes)
                            ->addCountryFilter($countryCodesIso2);
                    foreach ($collection->getData() as $row) {
                        $regionCodesToIds[$row['country_id']][$row['code']] = (int) $row['region_id'];
                    }



                    foreach ($csvLines as $k => $csvLine) {

                        $csvLine = $this->_getCsvValues($csvLine);

                        if (empty($countryCodesToIds) || !array_key_exists($csvLine[0], $countryCodesToIds)) {
                            $countryId = '0';
                            if ($csvLine[0] != '*' && $csvLine[0] != '') {
                                $exceptions[] = __('Invalid Country "%s" in the Row #%s', $csvLine[0], ($k + 1));
                            }
                        } else {
                            $countryId = $countryCodesToIds[$csvLine[0]];
                        }

                        if (!isset($countryCodesToIds[$csvLine[0]]) || !isset($regionCodesToIds[$countryCodesToIds[$csvLine[0]]]) || !array_key_exists($csvLine[1], $regionCodesToIds[$countryCodesToIds[$csvLine[0]]])) {
                            $regionId = '0';
                            if ($csvLine[1] != '*' && $csvLine[1] != '') {
                                $exceptions[] = __('Invalid Region/State "%s" in the Row #%s', $csvLine[1], ($k + 1));
                            }
                        } else {
                            $regionId = $regionCodesToIds[$countryCodesToIds[$csvLine[0]]][$csvLine[1]];
                        }

                        if (count($csvLine) == 9) {
                            // we are searching for postcodes in ranges & including cities
                            if ($csvLine[2] == '*' || $csvLine[2] == '') {
                                $city = '';
                            } else {
                                $city = $csvLine[2];
                            }


                            if ($csvLine[3] == '*' || $csvLine[3] == '') {
                                $zip = '';
                            } else {
                                $zip = $csvLine[3];
                            }


                            if ($csvLine[4] == '*' || $csvLine[4] == '') {
                                $zip_to = '';
                            } else {
                                $zip_to = $csvLine[4];
                            }


                            if (!$this->_isPositiveDecimalNumber($csvLine[5]) || $csvLine[5] == '*' || $csvLine[5] == '') {
                                $exceptions[] = __('Invalid %s From "%s" in the Row #%s', $conditionFullName, $csvLine[5], ($k + 1));
                            } else {
                                $csvLine[5] = (float) $csvLine[5];
                            }

                            if (!$this->_isPositiveDecimalNumber($csvLine[6])) {
                                $exceptions[] = __('Invalid %s To "%s" in the Row #%s', $conditionFullName, $csvLine[6], ($k + 1));
                            } else {
                                $csvLine[6] = (float) $csvLine[6];
                            }


                            $data[] = array('website_id' => $websiteId, 'dest_country_id' => $countryId, 'dest_region_id' => $regionId, 'dest_city' => $city, 'dest_zip' => $zip, 'dest_zip_to' => $zip_to, 'condition_name' => $conditionName, 'condition_from_value' => $csvLine[5], 'condition_to_value' => $csvLine[6], 'price' => $csvLine[7], 'cost' => 0, 'delivery_type' => $csvLine[8]);
                        } else {

                            if ($csvLine[2] == '*' || $csvLine[2] == '') {
                                $zip = '';
                            } else {
                                $zip = $csvLine[2] . "%";
                            }

                            $city = '';
                            $zip_to = '';

                            if (!$this->_isPositiveDecimalNumber($csvLine[3]) || $csvLine[3] == '*' || $csvLine[3] == '') {
                                $exceptions[] = __('Invalid %s From "%s" in the Row #%s', $conditionFullName, $csvLine[3], ($k + 1));
                            } else {
                                $csvLine[3] = (float) $csvLine[3];
                            }

                            if (!$this->_isPositiveDecimalNumber($csvLine[4])) {
                                $exceptions[] = __('Invalid %s To "%s" in the Row #%s', $conditionFullName, $csvLine[4], ($k + 1));
                            } else {
                                $csvLine[4] = (float) $csvLine[4];
                            }
                            $data[] = array('website_id' => $websiteId, 'dest_country_id' => $countryId, 'dest_region_id' => $regionId, 'dest_city' => $city, 'dest_zip' => $zip, 'dest_zip_to' => $zip_to, 'condition_name' => $conditionName, 'condition_from_value' => $csvLine[3], 'condition_to_value' => $csvLine[4], 'price' => $csvLine[5], 'cost' => 0, 'delivery_type' => $csvLine[6]);
                        }


                        $dataDetails[] = array('country' => $csvLine[0], 'region' => $csvLine[1]);
                    }
                }
                if (empty($exceptions)) {
                    $connection = $this->getConnection();
                    $condition = array(
                        $connection->quoteInto('website_id = ?', $websiteId),
                        $connection->quoteInto('condition_name = ?', $conditionName),
                    );
                    $connection->delete($table, $condition);

                    //foreach ($data as $k => $dataLine) {
                    try {
                        foreach ($data as $v) {
                            $data2[] = array_values($v);
                        }
                        $this->_saveImportData($data2);
                    } catch (\Exception $e) {
                        //$connection->rollback();                          
                        $this->_logger->critical($e);
                        $exceptions[] = __($e->__toString()); //__('Duplicate Row #%s (Country "%s", Region/State "%s", City "%s", Zip From "%s", Zip To "%s", Delivery Type "%s", Value From "%s" and Value To "%s")', ($k + 1), $dataDetails[$k]['country'], $dataDetails[$k]['region'], $dataLine['dest_city'], $dataLine['dest_zip'], $dataLine['dest_zip_to'], $dataLine['delivery_type'], $dataLine['condition_from_value'], $dataLine['condition_to_value']);
                    }
                    //}
                }
                if (!empty($exceptions)) {
                    throw new \Magento\Framework\Exception\LocalizedException(__(implode("\n", $exceptions)));
                }
            }
        }
    }

    protected function _saveImportData(array $data) {
        if (!empty($data)) {
            $columns = [
                'website_id',
                'dest_country_id',
                'dest_region_id',
                'dest_city',
                'dest_zip',
                'dest_zip_to',
                'condition_name',
                'condition_from_value',
                'condition_to_value',
                'price',
                'cost',
                'delivery_type',
            ];

            $this->getConnection()->insertArray($this->getMainTable(), $columns, $data);

            //$this->_importedRows += count($data);
        }

        return $this;
    }

    private function _getCsvValues($string, $separator = ",") {
        $elements = explode($separator, trim($string));
        for ($i = 0; $i < count($elements); $i++) {
            $nquotes = substr_count($elements[$i], '"');
            if ($nquotes % 2 == 1) {
                for ($j = $i + 1; $j < count($elements); $j++) {
                    if (substr_count($elements[$j], '"') > 0) {
                        // Put the quoted string's pieces back together again
                        array_splice($elements, $i, $j - $i + 1, implode($separator, array_slice($elements, $i, $j - $i + 1)));
                        break;
                    }
                }
            }
            if ($nquotes > 0) {
                // Remove first and last quotes, then merge pairs of quotes
                $qstr = & $elements[$i];
                $qstr = substr_replace($qstr, '', strpos($qstr, '"'), 1);
                $qstr = substr_replace($qstr, '', strrpos($qstr, '"'), 1);
                $qstr = str_replace('""', '"', $qstr);
            }
            $elements[$i] = trim($elements[$i]);
        }
        return $elements;
    }

    private function _isPositiveDecimalNumber($n) {
        return preg_match("/^[0-9]+(\.[0-9]*)?$/", $n);
    }

}
