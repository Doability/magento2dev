<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

/**
 * @method \Ess\M2ePro\Model\Template\Synchronization getParentObject()
 * @method \Ess\M2ePro\Model\ResourceModel\Ebay\Template\Synchronization getResource()
 */
namespace Ess\M2ePro\Model\Ebay\Template;

class Synchronization extends \Ess\M2ePro\Model\ActiveRecord\Component\Child\Ebay\AbstractModel
{
    const LIST_MODE_NONE = 0;
    const LIST_MODE_YES = 1;

    const LIST_STATUS_ENABLED_NONE = 0;
    const LIST_STATUS_ENABLED_YES  = 1;

    const LIST_IS_IN_STOCK_NONE = 0;
    const LIST_IS_IN_STOCK_YES  = 1;

    const LIST_QTY_NONE    = 0;
    const LIST_QTY_LESS    = 1;
    const LIST_QTY_BETWEEN = 2;
    const LIST_QTY_MORE    = 3;

    const REVISE_UPDATE_QTY_NONE = 0;
    const REVISE_UPDATE_QTY_YES  = 1;

    const REVISE_MAX_AFFECTED_QTY_MODE_OFF = 0;
    const REVISE_MAX_AFFECTED_QTY_MODE_ON = 1;

    const REVISE_UPDATE_QTY_MAX_APPLIED_VALUE_DEFAULT = 5;

    const REVISE_UPDATE_PRICE_NONE = 0;
    const REVISE_UPDATE_PRICE_YES  = 1;

    const REVISE_MAX_ALLOWED_PRICE_DEVIATION_MODE_OFF = 0;
    const REVISE_MAX_ALLOWED_PRICE_DEVIATION_MODE_ON  = 1;

    const REVISE_UPDATE_PRICE_MAX_ALLOWED_DEVIATION_DEFAULT = 3;

    const REVISE_UPDATE_TITLE_NONE = 0;
    const REVISE_UPDATE_TITLE_YES  = 1;

    const REVISE_UPDATE_DESCRIPTION_NONE = 0;
    const REVISE_UPDATE_DESCRIPTION_YES  = 1;

    const REVISE_UPDATE_SUB_TITLE_NONE = 0;
    const REVISE_UPDATE_SUB_TITLE_YES  = 1;

    const REVISE_UPDATE_IMAGES_NONE = 0;
    const REVISE_UPDATE_IMAGES_YES  = 1;

    const REVISE_CHANGE_PAYMENT_TEMPLATE_NONE = 0;
    const REVISE_CHANGE_PAYMENT_TEMPLATE_YES  = 1;

    const REVISE_CHANGE_SHIPPING_TEMPLATE_NONE = 0;
    const REVISE_CHANGE_SHIPPING_TEMPLATE_YES  = 1;

    const REVISE_CHANGE_RETURN_TEMPLATE_NONE = 0;
    const REVISE_CHANGE_RETURN_TEMPLATE_YES  = 1;

    const REVISE_CHANGE_DESCRIPTION_TEMPLATE_NONE = 0;
    const REVISE_CHANGE_DESCRIPTION_TEMPLATE_YES  = 1;

    const REVISE_CHANGE_CATEGORY_TEMPLATE_NONE = 0;
    const REVISE_CHANGE_CATEGORY_TEMPLATE_YES  = 1;

    const RELIST_FILTER_USER_LOCK_NONE = 0;
    const RELIST_FILTER_USER_LOCK_YES  = 1;

    const RELIST_SEND_DATA_NONE = 0;
    const RELIST_SEND_DATA_YES  = 1;

    const RELIST_MODE_NONE = 0;
    const RELIST_MODE_YES  = 1;

    const RELIST_STATUS_ENABLED_NONE = 0;
    const RELIST_STATUS_ENABLED_YES  = 1;

    const RELIST_IS_IN_STOCK_NONE = 0;
    const RELIST_IS_IN_STOCK_YES  = 1;

    const RELIST_QTY_NONE    = 0;
    const RELIST_QTY_LESS    = 1;
    const RELIST_QTY_BETWEEN = 2;
    const RELIST_QTY_MORE    = 3;

    const STOP_STATUS_DISABLED_NONE = 0;
    const STOP_STATUS_DISABLED_YES  = 1;

    const STOP_OUT_OFF_STOCK_NONE = 0;
    const STOP_OUT_OFF_STOCK_YES  = 1;

    const STOP_QTY_NONE    = 0;
    const STOP_QTY_LESS    = 1;
    const STOP_QTY_BETWEEN = 2;
    const STOP_QTY_MORE    = 3;

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('Ess\M2ePro\Model\ResourceModel\Ebay\Template\Synchronization');
    }

    /**
     * @return string
     */
    public function getNick()
    {
        return \Ess\M2ePro\Model\Ebay\Template\Manager::TEMPLATE_SYNCHRONIZATION;
    }

    //########################################

    /**
     * @return bool
     * @throws \Ess\M2ePro\Model\Exception\Logic
     */
    public function isLocked()
    {
        if (parent::isLocked()) {
            return true;
        }

        return (bool)$this->activeRecordFactory->getObject('Ebay\Listing')
                            ->getCollection()
                            ->addFieldToFilter('template_synchronization_mode',
                                               \Ess\M2ePro\Model\Ebay\Template\Manager::MODE_TEMPLATE)
                            ->addFieldToFilter('template_synchronization_id', $this->getId())
                            ->getSize() ||
               (bool)$this->activeRecordFactory->getObject('Ebay\Listing\Product')
                            ->getCollection()
                            ->addFieldToFilter('template_synchronization_mode',
                                               \Ess\M2ePro\Model\Ebay\Template\Manager::MODE_TEMPLATE)
                            ->addFieldToFilter('template_synchronization_id', $this->getId())
                            ->getSize();
    }

    //########################################

    public function save()
    {
        $this->getHelper('Data\Cache\Permanent')->removeTagValues('template_synchronization');
        return parent::save();
    }

    public function delete()
    {
        $this->getHelper('Data\Cache\Permanent')->removeTagValues('template_synchronization');
        return parent::delete();
    }

    //########################################

    /**
     * @return bool
     */
    public function isListMode()
    {
        return $this->getData('list_mode') != self::LIST_MODE_NONE;
    }

    /**
     * @return bool
     */
    public function isListStatusEnabled()
    {
        return $this->getData('list_status_enabled') != self::LIST_STATUS_ENABLED_NONE;
    }

    /**
     * @return bool
     */
    public function isListIsInStock()
    {
        return $this->getData('list_is_in_stock') != self::LIST_IS_IN_STOCK_NONE;
    }

    /**
     * @return bool
     */
    public function isListWhenQtyMagentoHasValue()
    {
        return $this->getData('list_qty_magento') != self::LIST_QTY_NONE;
    }

    /**
     * @return bool
     */
    public function isListWhenQtyCalculatedHasValue()
    {
        return $this->getData('list_qty_calculated') != self::LIST_QTY_NONE;
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getReviseUpdateQtyMaxAppliedValueMode()
    {
        return (int)$this->getData('revise_update_qty_max_applied_value_mode');
    }

    /**
     * @return bool
     */
    public function isReviseUpdateQtyMaxAppliedValueModeOn()
    {
        return $this->getReviseUpdateQtyMaxAppliedValueMode() == self::REVISE_MAX_AFFECTED_QTY_MODE_ON;
    }

    /**
     * @return bool
     */
    public function isReviseUpdateQtyMaxAppliedValueModeOff()
    {
        return $this->getReviseUpdateQtyMaxAppliedValueMode() == self::REVISE_MAX_AFFECTED_QTY_MODE_OFF;
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getReviseUpdateQtyMaxAppliedValue()
    {
        return (int)$this->getData('revise_update_qty_max_applied_value');
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getReviseUpdatePriceMaxAllowedDeviationMode()
    {
        return (int)$this->getData('revise_update_price_max_allowed_deviation_mode');
    }

    /**
     * @return bool
     */
    public function isReviseUpdatePriceMaxAllowedDeviationModeOn()
    {
        return $this->getReviseUpdatePriceMaxAllowedDeviationMode() == self::REVISE_MAX_ALLOWED_PRICE_DEVIATION_MODE_ON;
    }

    /**
     * @return bool
     */
    public function isReviseUpdatePriceMaxAllowedDeviationModeOff()
    {
        return $this->getReviseUpdatePriceMaxAllowedDeviationMode()
                    == self::REVISE_MAX_ALLOWED_PRICE_DEVIATION_MODE_OFF;
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getReviseUpdatePriceMaxAllowedDeviation()
    {
        return (int)$this->getData('revise_update_price_max_allowed_deviation');
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isReviseWhenChangeQty()
    {
        return $this->getData('revise_update_qty') != self::REVISE_UPDATE_QTY_NONE;
    }

    /**
     * @return bool
     */
    public function isReviseWhenChangePrice()
    {
        return $this->getData('revise_update_price') != self::REVISE_UPDATE_PRICE_NONE;
    }

    /**
     * @return bool
     */
    public function isReviseWhenChangeTitle()
    {
        return $this->getData('revise_update_title') != self::REVISE_UPDATE_TITLE_NONE;
    }

    /**
     * @return bool
     */
    public function isReviseWhenChangeSubTitle()
    {
        return $this->getData('revise_update_sub_title') != self::REVISE_UPDATE_SUB_TITLE_NONE;
    }

    /**
     * @return bool
     */
    public function isReviseWhenChangeDescription()
    {
        return $this->getData('revise_update_description') != self::REVISE_UPDATE_DESCRIPTION_NONE;
    }

    /**
     * @return bool
     */
    public function isReviseWhenChangeImages()
    {
        return $this->getData('revise_update_images') != self::REVISE_UPDATE_IMAGES_NONE;
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isReviseCategoryTemplate()
    {
        return (int)$this->getData('revise_change_category_template') !=
            self::REVISE_CHANGE_CATEGORY_TEMPLATE_NONE;
    }

    /**
     * @return bool
     */
    public function isRevisePaymentTemplate()
    {
        return (int)$this->getData('revise_change_payment_template') !=
            self::REVISE_CHANGE_PAYMENT_TEMPLATE_NONE;
    }

    /**
     * @return bool
     */
    public function isReviseReturnTemplate()
    {
        return (int)$this->getData('revise_change_return_policy_template') !=
            self::REVISE_CHANGE_RETURN_TEMPLATE_NONE;
    }

    /**
     * @return bool
     */
    public function isReviseShippingTemplate()
    {
        return (int)$this->getData('revise_change_shipping_template') !=
            self::REVISE_CHANGE_SHIPPING_TEMPLATE_NONE;
    }

    /**
     * @return bool
     */
    public function isReviseDescriptionTemplate()
    {
        return (int)$this->getData('revise_change_description_template') !=
            self::REVISE_CHANGE_DESCRIPTION_TEMPLATE_NONE;
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isRelistMode()
    {
        return $this->getData('relist_mode') != self::RELIST_MODE_NONE;
    }

    /**
     * @return bool
     */
    public function isRelistFilterUserLock()
    {
        return $this->getData('relist_filter_user_lock') != self::RELIST_FILTER_USER_LOCK_NONE;
    }

    /**
     * @return bool
     */
    public function isRelistSendData()
    {
        return $this->getData('relist_send_data') != self::RELIST_SEND_DATA_NONE;
    }

    /**
     * @return bool
     */
    public function isRelistStatusEnabled()
    {
        return $this->getData('relist_status_enabled') != self::RELIST_STATUS_ENABLED_NONE;
    }

    /**
     * @return bool
     */
    public function isRelistIsInStock()
    {
        return $this->getData('relist_is_in_stock') != self::RELIST_IS_IN_STOCK_NONE;
    }

    /**
     * @return bool
     */
    public function isRelistWhenQtyMagentoHasValue()
    {
        return $this->getData('relist_qty_magento') != self::RELIST_QTY_NONE;
    }

    /**
     * @return bool
     */
    public function isRelistWhenQtyCalculatedHasValue()
    {
        return $this->getData('relist_qty_calculated') != self::RELIST_QTY_NONE;
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isStopStatusDisabled()
    {
        return $this->getData('stop_status_disabled') != self::STOP_STATUS_DISABLED_NONE;
    }

    /**
     * @return bool
     */
    public function isStopOutOfStock()
    {
        return $this->getData('stop_out_off_stock') != self::STOP_OUT_OFF_STOCK_NONE;
    }

    /**
     * @return bool
     */
    public function isStopWhenQtyMagentoHasValue()
    {
        return $this->getData('stop_qty_magento') != self::STOP_QTY_NONE;
    }

    /**
     * @return bool
     */
    public function isStopWhenQtyCalculatedHasValue()
    {
        return $this->getData('stop_qty_calculated') != self::STOP_QTY_NONE;
    }

    //########################################

    public function getListWhenQtyMagentoHasValueType()
    {
        return $this->getData('list_qty_magento');
    }

    public function getListWhenQtyMagentoHasValueMin()
    {
        return $this->getData('list_qty_magento_value');
    }

    public function getListWhenQtyMagentoHasValueMax()
    {
        return $this->getData('list_qty_magento_value_max');
    }

    // ---------------------------------------

    public function getListWhenQtyCalculatedHasValueType()
    {
        return $this->getData('list_qty_calculated');
    }

    public function getListWhenQtyCalculatedHasValueMin()
    {
        return $this->getData('list_qty_calculated_value');
    }

    public function getListWhenQtyCalculatedHasValueMax()
    {
        return $this->getData('list_qty_calculated_value_max');
    }

    // ---------------------------------------

    public function getRelistWhenQtyMagentoHasValueType()
    {
        return $this->getData('relist_qty_magento');
    }

    public function getRelistWhenQtyMagentoHasValueMin()
    {
        return $this->getData('relist_qty_magento_value');
    }

    public function getRelistWhenQtyMagentoHasValueMax()
    {
        return $this->getData('relist_qty_magento_value_max');
    }

    // ---------------------------------------

    public function getRelistWhenQtyCalculatedHasValueType()
    {
        return $this->getData('relist_qty_calculated');
    }

    public function getRelistWhenQtyCalculatedHasValueMin()
    {
        return $this->getData('relist_qty_calculated_value');
    }

    public function getRelistWhenQtyCalculatedHasValueMax()
    {
        return $this->getData('relist_qty_calculated_value_max');
    }

    // ---------------------------------------

    public function getStopWhenQtyMagentoHasValueType()
    {
        return $this->getData('stop_qty_magento');
    }

    public function getStopWhenQtyMagentoHasValueMin()
    {
        return $this->getData('stop_qty_magento_value');
    }

    public function getStopWhenQtyMagentoHasValueMax()
    {
        return $this->getData('stop_qty_magento_value_max');
    }

    // ---------------------------------------

    public function getStopWhenQtyCalculatedHasValueType()
    {
        return $this->getData('stop_qty_calculated');
    }

    public function getStopWhenQtyCalculatedHasValueMin()
    {
        return $this->getData('stop_qty_calculated_value');
    }

    public function getStopWhenQtyCalculatedHasValueMax()
    {
        return $this->getData('stop_qty_calculated_value_max');
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isScheduleEnabled()
    {
        return (int)$this->getData('schedule_mode') == 1;
    }

    /**
     * @return bool
     * @throws \Ess\M2ePro\Model\Exception\Logic
     */
    public function isScheduleIntervalNow()
    {
        $intervalSettings = $this->getSettings('schedule_interval_settings');

        if (empty($intervalSettings)) {
            return true;
        }

        if (!isset($intervalSettings['mode'], $intervalSettings['date_from'], $intervalSettings['date_to'])) {
            return true;
        }

        if ($intervalSettings['mode'] == 0) {
            return true;
        }

        $from = strtotime($intervalSettings['date_from']);
        $to   = strtotime($intervalSettings['date_to']);
        $now  = $this->getHelper('Data')->getCurrentGmtDate(true);

        return $now >= $from && $now <= $to;
    }

    /**
     * @return bool
     * @throws \Ess\M2ePro\Model\Exception\Logic
     */
    public function isScheduleWeekNow()
    {
        $weekSettings = $this->getSettings('schedule_week_settings');

        if (empty($weekSettings)) {
            return false;
        }

        $todayDayOfWeek = getdate($this->getHelper('Data')->getCurrentTimezoneDate(true));
        $todayDayOfWeek = strtolower($todayDayOfWeek['weekday']);

        if (!isset($weekSettings[$todayDayOfWeek])) {
            return false;
        }

        if (!isset($weekSettings[$todayDayOfWeek]['time_from'], $weekSettings[$todayDayOfWeek]['time_to'])) {
            return false;
        }

        $now = $this->getHelper('Data')->getCurrentTimezoneDate(true);

        list($fromHour,$fromMinute,$fromSecond) = explode(':',$weekSettings[$todayDayOfWeek]['time_from']);
        $from = mktime($fromHour,$fromMinute,$fromSecond, date('m',$now),date('d',$now),date('Y',$now));

        list($toHour,$toMinute,$toSecond) = explode(':',$weekSettings[$todayDayOfWeek]['time_to']);
        $to = mktime($toHour,$toMinute,$toSecond, date('m',$now),date('d',$now),date('Y',$now));

        return $now >= $from && $now <= $to;
    }

    //########################################

    /**
     * @return array
     */
    public function getDefaultSettings()
    {
        return array_merge(
            $this->getListDefaultSettings(),
            $this->getReviseDefaultSettings(),
            $this->getRelistDefaultSettings(),
            $this->getStopDefaultSettings(),
            $this->getScheduleDefaultSettings()
        );
    }

    // ---------------------------------------

    /**
     * @return array
     */
    public function getListDefaultSettings()
    {
        return array(
            'list_mode'           => self::LIST_MODE_YES,
            'list_status_enabled' => self::LIST_STATUS_ENABLED_YES,
            'list_is_in_stock'    => self::LIST_IS_IN_STOCK_YES,

            'list_qty_magento'           => self::LIST_QTY_NONE,
            'list_qty_magento_value'     => '1',
            'list_qty_magento_value_max' => '10',

            'list_qty_calculated'           => self::LIST_QTY_NONE,
            'list_qty_calculated_value'     => '1',
            'list_qty_calculated_value_max' => '10',
        );
    }

    /**
     * @return array
     */
    public function getReviseDefaultSettings()
    {
        return array(
            'revise_update_qty'                              => self::REVISE_UPDATE_QTY_YES,
            'revise_update_qty_max_applied_value_mode'       => self::REVISE_MAX_AFFECTED_QTY_MODE_ON,
            'revise_update_qty_max_applied_value'            => self::REVISE_UPDATE_QTY_MAX_APPLIED_VALUE_DEFAULT,
            'revise_update_price'                            => self::REVISE_UPDATE_PRICE_YES,
            'revise_update_price_max_allowed_deviation_mode' => self::REVISE_MAX_ALLOWED_PRICE_DEVIATION_MODE_ON,
            'revise_update_price_max_allowed_deviation'      => self::REVISE_UPDATE_PRICE_MAX_ALLOWED_DEVIATION_DEFAULT,
            'revise_update_title'                            => self::REVISE_UPDATE_TITLE_NONE,
            'revise_update_sub_title'                        => self::REVISE_UPDATE_SUB_TITLE_NONE,
            'revise_update_description'                      => self::REVISE_UPDATE_DESCRIPTION_NONE,
            'revise_update_images'                           => self::REVISE_UPDATE_IMAGES_NONE,

            'revise_change_selling_format_template'          =>
                   \Ess\M2ePro\Model\Template\Synchronization::REVISE_CHANGE_SELLING_FORMAT_TEMPLATE_YES,
            'revise_change_description_template'             => self::REVISE_CHANGE_DESCRIPTION_TEMPLATE_NONE,
            'revise_change_category_template'                => self::REVISE_CHANGE_CATEGORY_TEMPLATE_YES,
            'revise_change_payment_template'                 => self::REVISE_CHANGE_PAYMENT_TEMPLATE_YES,
            'revise_change_shipping_template'                => self::REVISE_CHANGE_SHIPPING_TEMPLATE_YES,
            'revise_change_return_policy_template'           => self::REVISE_CHANGE_RETURN_TEMPLATE_YES
        );
    }

    /**
     * @return array
     */
    public function getRelistDefaultSettings()
    {
        return array(
            'relist_mode'             => self::RELIST_MODE_YES,
            'relist_filter_user_lock' => self::RELIST_FILTER_USER_LOCK_YES,
            'relist_send_data'        => self::RELIST_SEND_DATA_NONE,
            'relist_status_enabled'   => self::RELIST_STATUS_ENABLED_YES,
            'relist_is_in_stock'      => self::RELIST_IS_IN_STOCK_YES,

            'relist_qty_magento'           => self::RELIST_QTY_NONE,
            'relist_qty_magento_value'     => '1',
            'relist_qty_magento_value_max' => '10',

            'relist_qty_calculated'           => self::RELIST_QTY_NONE,
            'relist_qty_calculated_value'     => '1',
            'relist_qty_calculated_value_max' => '10'
        );
    }

    /**
     * @return array
     */
    public function getStopDefaultSettings()
    {
        return array(
            'stop_status_disabled' => self::STOP_STATUS_DISABLED_YES,
            'stop_out_off_stock'   => self::STOP_OUT_OFF_STOCK_YES,

            'stop_qty_magento'           => self::STOP_QTY_NONE,
            'stop_qty_magento_value'     => '0',
            'stop_qty_magento_value_max' => '10',

            'stop_qty_calculated'           => self::STOP_QTY_NONE,
            'stop_qty_calculated_value'     => '0',
            'stop_qty_calculated_value_max' => '10'
        );
    }

    /**
     * @return array
     */
    public function getScheduleDefaultSettings()
    {
        return array(
            'schedule_mode'              => 0,

            'schedule_interval_settings' => json_encode(array(
                'mode'      => 0,
                'date_from' => $this->getHelper('Data')->getCurrentTimezoneDate(false,'Y-m-d'),
                'date_to'   => $this->getHelper('Data')->getCurrentTimezoneDate(false,'Y-m-d')
            )),

            'schedule_week_settings'     => json_encode(array())
        );
    }

    //########################################

    /**
     * @param bool $asArrays
     * @param string|array $columns
     * @return array
     */
    public function getAffectedListingsProducts($asArrays = true, $columns = '*')
    {
        $templateManager = $this->modelFactory->getObject('Ebay\Template\Manager');
        $templateManager->setTemplate(\Ess\M2ePro\Model\Ebay\Template\Manager::TEMPLATE_SYNCHRONIZATION);

        $listingsProducts = $templateManager->getAffectedOwnerObjects(
           \Ess\M2ePro\Model\Ebay\Template\Manager::OWNER_LISTING_PRODUCT, $this->getId(), $asArrays, $columns
        );

        $listings = $templateManager->getAffectedOwnerObjects(
           \Ess\M2ePro\Model\Ebay\Template\Manager::OWNER_LISTING, $this->getId(), false
        );

        foreach ($listings as $listing) {

            $tempListingsProducts = $listing->getChildObject()
                                            ->getAffectedListingsProductsByTemplate(
                                                \Ess\M2ePro\Model\Ebay\Template\Manager::TEMPLATE_SYNCHRONIZATION,
                                                $asArrays, $columns
                                            );

            foreach ($tempListingsProducts as $listingProduct) {
                if (!isset($listingsProducts[$listingProduct['id']])) {
                    $listingsProducts[$listingProduct['id']] = $listingProduct;
                }
            }
        }

        return $listingsProducts;
    }

    public function setSynchStatusNeed($newData, $oldData)
    {
        $listingsProducts = $this->getAffectedListingsProducts(true, array('id', 'synch_status', 'synch_reasons'));
        if (empty($listingsProducts)) {
            return;
        }

        $this->getResource()->setSynchStatusNeed($newData,$oldData,$listingsProducts);
    }

    //########################################

    public function isCacheEnabled()
    {
        return true;
    }

    //########################################
}