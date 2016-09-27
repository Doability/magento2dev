<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Block\Adminhtml\Amazon\Listing\View\Amazon;

use Ess\M2ePro\Model\Amazon\Listing\Product\Variation\Manager\Type\Relation\ParentRelation;

class Grid extends \Ess\M2ePro\Block\Adminhtml\Listing\View\Grid
{
    private $lockedDataCache = array();

    private $childProductsWarningsData;

    private $hideSwitchToIndividualConfirm;
    private $hideSwitchToParentConfirm;

    /** @var  \Ess\M2ePro\Model\Listing */
    protected $listing;

    protected $magentoProductCollectionFactory;
    protected $amazonFactory;
    protected $priceCurrency;
    protected $resourceConnection;

    //########################################

    public function __construct(
        \Ess\M2ePro\Model\ResourceModel\Magento\Product\CollectionFactory $magentoProductCollectionFactory,
        \Ess\M2ePro\Model\ActiveRecord\Component\Parent\Amazon\Factory $amazonFactory,
        \Magento\Directory\Model\PriceCurrency $priceCurrency,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Ess\M2ePro\Block\Adminhtml\Magento\Context\Template $context,
        \Magento\Backend\Helper\Data $backendHelper,
        array $data = []
    )
    {
        $this->magentoProductCollectionFactory = $magentoProductCollectionFactory;
        $this->amazonFactory = $amazonFactory;
        $this->priceCurrency = $priceCurrency;
        $this->resourceConnection = $resourceConnection;

        parent::__construct($context, $backendHelper, $data);
    }

    //########################################

    public function _construct()
    {
        parent::_construct();

        $this->listing = $this->getHelper('Data\GlobalData')->getValue('view_listing');

        $this->hideSwitchToIndividualConfirm =
            $this->listing->getSetting('additional_data', 'hide_switch_to_individual_confirm', 0);

        $this->hideSwitchToParentConfirm =
            $this->listing->getSetting('additional_data', 'hide_switch_to_parent_confirm', 0);

        // Initialization block
        // ---------------------------------------
        $this->setId('amazonListingViewAmazonGrid'.$this->listing['id']);
        // ---------------------------------------

        $this->showAdvancedFilterProductsOption = false;
    }

    //########################################

    protected function _prepareCollection()
    {
        // Get collection
        // ---------------------------------------
        /* @var $collection \Ess\M2ePro\Model\ResourceModel\Magento\Product\Collection */
        $collection = $this->magentoProductCollectionFactory->create();

        $collection->setListingProductModeOn();
        $collection->addAttributeToSelect('sku');
        $collection->addAttributeToSelect('name');

        $collection->joinTable(
            array('cisi' => 'cataloginventory_stock_item'),
            'product_id=entity_id',
            array(
                'qty' => 'qty'
            ),
            '{{table}}.stock_id=1',
            'left'
        );

        // ---------------------------------------

        // Join listing product tables
        // ---------------------------------------
        $lpTable = $this->activeRecordFactory->getObject('Listing\Product')->getResource()->getMainTable();
        $collection->joinTable(
            array('lp' => $lpTable),
            'product_id=entity_id',
            array(
                'id'              => 'id',
                'amazon_status'   => 'status',
                'component_mode'  => 'component_mode',
                'additional_data' => 'additional_data'
            ),
            array(
                'listing_id' => (int)$this->listing['id']
            )
        );

        $alpTable = $this->activeRecordFactory->getObject('Amazon\Listing\Product')->getResource()->getMainTable();
        $collection->joinTable(
            array('alp' => $alpTable),
            'listing_product_id=id',
            array(
                'general_id'                     => 'general_id',
                'general_id_search_info'         => 'general_id_search_info',
                'search_settings_status'         => 'search_settings_status',
                'search_settings_data'           => 'search_settings_data',
                'variation_child_statuses'       => 'variation_child_statuses',
                'amazon_sku'                     => 'sku',
                'online_qty'                     => 'online_qty',
                'online_price'                   => 'online_price',
                'online_sale_price'              => 'IF(
                  `alp`.`online_sale_price_start_date` IS NOT NULL AND
                  `alp`.`online_sale_price_end_date` IS NOT NULL AND
                  `alp`.`online_sale_price_end_date` >= CURRENT_DATE(),
                  `alp`.`online_sale_price`,
                  NULL
                )',
                'online_sale_price_start_date'   => 'online_sale_price_start_date',
                'online_sale_price_end_date'     => 'online_sale_price_end_date',
                'is_afn_channel'                 => 'is_afn_channel',
                'is_general_id_owner'            => 'is_general_id_owner',
                'is_variation_parent'            => 'is_variation_parent',
                'defected_messages'              => 'defected_messages',
                'min_online_price'                      => 'IF(
                    (`t`.`variation_min_price` IS NULL),
                    IF(
                      `alp`.`online_sale_price_start_date` IS NOT NULL AND
                      `alp`.`online_sale_price_end_date` IS NOT NULL AND
                      `alp`.`online_sale_price_start_date` <= CURRENT_DATE() AND
                      `alp`.`online_sale_price_end_date` >= CURRENT_DATE(),
                      `alp`.`online_sale_price`,
                      `alp`.`online_price`
                    ),
                    `t`.`variation_min_price`
                )',
                'max_online_price'                      => 'IF(
                    (`t`.`variation_max_price` IS NULL),
                    IF(
                      `alp`.`online_sale_price_start_date` IS NOT NULL AND
                      `alp`.`online_sale_price_end_date` IS NOT NULL AND
                      `alp`.`online_sale_price_start_date` <= CURRENT_DATE() AND
                      `alp`.`online_sale_price_end_date` >= CURRENT_DATE(),
                      `alp`.`online_sale_price`,
                      `alp`.`online_price`
                    ),
                    `t`.`variation_max_price`
                )'
            ),
            '{{table}}.variation_parent_id is NULL'
        );

//        TODO
//        $collection->getSelect()->joinLeft(
//            array('malpr' => Mage::getResourceModel('M2ePro/Amazon_Listing_Product_Repricing')->getMainTable()),
//            '(`alp`.`listing_product_id` = `malpr`.`listing_product_id`)',
//            array(
//                'is_repricing' => 'listing_product_id',
//                'is_repricing_disabled' => 'is_online_disabled',
//            )
//        );
        
        $collection->getSelect()->joinLeft(
            new \Zend_Db_Expr('(
                SELECT
                    `malp`.`variation_parent_id`,
                    MIN(
                        IF(
                            `malp`.`online_sale_price_start_date` IS NOT NULL AND
                            `malp`.`online_sale_price_end_date` IS NOT NULL AND
                            `malp`.`online_sale_price_start_date` <= CURRENT_DATE() AND
                            `malp`.`online_sale_price_end_date` >= CURRENT_DATE(),
                            `malp`.`online_sale_price`,
                            `malp`.`online_price`
                        )
                    ) as variation_min_price,
                    MAX(
                        IF(
                            `malp`.`online_sale_price_start_date` IS NOT NULL AND
                            `malp`.`online_sale_price_end_date` IS NOT NULL AND
                            `malp`.`online_sale_price_start_date` <= CURRENT_DATE() AND
                            `malp`.`online_sale_price_end_date` >= CURRENT_DATE(),
                            `malp`.`online_sale_price`,
                            `malp`.`online_price`
                        )
                    ) as variation_max_price
                FROM `'. $alpTable .'` as malp
                INNER JOIN `'. $lpTable .'` AS `mlp`
                    ON (`malp`.`listing_product_id` = `mlp`.`id`)
                WHERE `mlp`.`status` IN (
                    ' . \Ess\M2ePro\Model\Listing\Product::STATUS_LISTED . ',
                    ' . \Ess\M2ePro\Model\Listing\Product::STATUS_STOPPED . ',
                    ' . \Ess\M2ePro\Model\Listing\Product::STATUS_UNKNOWN . '
                ) AND `malp`.`variation_parent_id` IS NOT NULL
                GROUP BY `malp`.`variation_parent_id`
            )'),
            'alp.listing_product_id=t.variation_parent_id',
            array(
                'variation_min_price' => 'variation_min_price',
                'variation_max_price' => 'variation_max_price',
            )
        );

//        TODO
//        $collection->getSelect()->joinLeft(
//            new Zend_Db_Expr('(
//                SELECT
//                    `malp`.`variation_parent_id`,
//                    COUNT(*) AS `variation_repricing_enabled_count`
//                FROM `'. Mage::getResourceModel('M2ePro/Amazon_Listing_Product')->getMainTable() .'` as malp
//                LEFT JOIN `'.Mage::getResourceModel('M2ePro/Amazon_Listing_Product_Repricing')->getMainTable().'`
//                    AS `malpr` ON (`malp`.`listing_product_id` = `malpr`.`listing_product_id`)
//                WHERE `malp`.`listing_product_id` IS NOT NULL AND `malpr`.`is_online_disabled` = 0
//                GROUP BY `malp`.`variation_parent_id`
//            )'),
//            'lp.id=t_2.variation_parent_id',
//            array(
//                'variation_repricing_enabled_count' => 'variation_repricing_enabled_count',
//            )
//        );

//        TODO
//        $collection->getSelect()->joinLeft(
//            new Zend_Db_Expr('(
//                SELECT
//                    `malp`.`variation_parent_id`,
//                    COUNT(*) AS `variation_repricing_disabled_count`
//                FROM `'. Mage::getResourceModel('M2ePro/Amazon_Listing_Product')->getMainTable() .'` as malp
//                LEFT JOIN `'.Mage::getResourceModel('M2ePro/Amazon_Listing_Product_Repricing')->getMainTable().'`
//                    AS `malpr` ON (`malp`.`listing_product_id` = `malpr`.`listing_product_id`)
//                WHERE `malp`.`listing_product_id` IS NOT NULL AND `malpr`.`is_online_disabled` = 1
//                GROUP BY `malp`.`variation_parent_id`
//            )'),
//            'lp.id=t_3.variation_parent_id',
//            array(
//                'variation_repricing_disabled_count' => 'variation_repricing_disabled_count',
//            )
//        );

//        TODO
//        $collection->getSelect()->joinLeft(
//            new \Zend_Db_Expr('(
//                SELECT
//                    `malp`.`variation_parent_id`,
//                    COUNT(*) AS `variation_afn_count`
//                FROM `'. $alpTable .'` as malp
//                WHERE `malp`.`is_afn_channel` = 1
//                GROUP BY `malp`.`variation_parent_id`
//            )'),
//            'lp.id=t_4.variation_parent_id',
//            array(
//                'variation_afn_count' => 'variation_afn_count',
//            )
//        );

        // ---------------------------------------

        // Set collection to grid
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('product_id', array(
            'header'    => $this->__('Product ID'),
            'align'     => 'right',
            'width'     => '100px',
            'type'      => 'number',
            'index'     => 'entity_id',
            'frame_callback' => array($this, 'callbackColumnProductId')
        ));

        $this->addColumn('name', array(
            'header'    => $this->__('Product Title / Product SKU'),
            'align'     => 'left',
            'type'      => 'text',
            'index'     => 'name',
            'filter_index' => 'name',
            'frame_callback' => array($this, 'callbackColumnProductTitle'),
            'filter_condition_callback' => array($this, 'callbackFilterTitle')
        ));

        $this->addColumn('sku', array(
            'header' => $this->__('SKU'),
            'align' => 'left',
            'width' => '150px',
            'type' => 'text',
            'index' => 'amazon_sku',
            'filter_index' => 'amazon_sku',
            'frame_callback' => array($this, 'callbackColumnAmazonSku')
        ));

        $this->addColumn('general_id', array(
            'header' => $this->__('ASIN / ISBN'),
            'align' => 'left',
            'width' => '140px',
            'type' => 'text',
            'index' => 'general_id',
            'filter_index' => 'general_id',
            'frame_callback' => array($this, 'callbackColumnGeneralId')
        ));

        $this->addColumn('online_qty', array(
            'header' => $this->__('QTY'),
            'align' => 'right',
            'width' => '70px',
            'type' => 'number',
            'index' => 'online_qty',
            'filter_index' => 'online_qty',
            'frame_callback' => array($this, 'callbackColumnAvailableQty'),
            'filter'   => 'Ess\M2ePro\Block\Adminhtml\Amazon\Grid\Column\Filter\Qty',
            'filter_condition_callback' => array($this, 'callbackFilterQty')
        ));

        $dir = $this->getParam($this->getVarNameDir(), $this->_defaultDir);

        if ($dir == 'desc') {
            $priceSortField = 'max_online_price';
        } else {
            $priceSortField = 'min_online_price';
        }

        $priceColumn = array(
            'header' => $this->__('Price'),
            'align' => 'right',
            'width' => '110px',
            'type' => 'number',
            'index' => $priceSortField,
            'filter_index' => $priceSortField,
            'frame_callback' => array($this, 'callbackColumnPrice'),
            'filter_condition_callback' => array($this, 'callbackFilterPrice')
        );
        
//        TODO
//        if (Mage::helper('M2ePro/Component_Amazon_Repricing')->isEnabled()) {
//            $priceColumn['filter'] = 'Ess\M2ePro\Block\Adminhtml\Amazon\Grid\Column\Filter\Price';
//        }

        $this->addColumn('online_price', $priceColumn);

        $this->addColumn('status', array(
            'header' => $this->__('Status'),
            'width' => '155px',
            'index' => 'amazon_status',
            'filter_index' => 'amazon_status',
            'type' => 'options',
            'sortable' => false,
            'options' => array(
                \Ess\M2ePro\Model\Listing\Product::STATUS_UNKNOWN => $this->__('Unknown'),
                \Ess\M2ePro\Model\Listing\Product::STATUS_NOT_LISTED => $this->__('Not Listed'),
                \Ess\M2ePro\Model\Listing\Product::STATUS_LISTED => $this->__('Active'),
                \Ess\M2ePro\Model\Listing\Product::STATUS_STOPPED => $this->__('Inactive'),
                \Ess\M2ePro\Model\Listing\Product::STATUS_BLOCKED => $this->__('Inactive (Blocked)')
            ),
            'frame_callback' => array($this, 'callbackColumnStatus'),
            'filter_condition_callback' => array($this, 'callbackFilterStatus')
        ));

//        TODO
//        if (Mage::helper('M2ePro/Module')->isDevelopmentMode()) {
//            $this->addColumn('developer_action', array(
//                'header'     => $this->__('Actions'),
//                'align'      => 'left',
//                'width'      => '100px',
//                'type'       => 'text',
//                'renderer'   => 'M2ePro/adminhtml_listing_view_grid_column_renderer_developerAction',
//                'index'      => 'value',
//                'filter'     => false,
//                'sortable'   => false,
//                'js_handler' => 'ListingGridHandlerObj'
//            ));
//        }

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        // Set massaction identifiers
        // ---------------------------------------
        $this->setMassactionIdField('id');
        $this->setMassactionIdFieldOnlyIndexValue(true);
        // ---------------------------------------

        // Set mass-action
        // ---------------------------------------
        $groups = array(
            'actions'            => $this->__('Actions'),
            'asin_isbn'          => $this->__('ASIN / ISBN'),
            'description_policy' => $this->__('Description Policy'),
            'other'              => $this->__('Other'),
        );

        $this->getMassactionBlock()->setGroups($groups);

        $this->getMassactionBlock()->addItem('list', array(
            'label'    => $this->__('List Item(s)'),
            'url'      => '',
            'confirm'  => $this->__('Are you sure?')
        ), 'actions');

        $this->getMassactionBlock()->addItem('revise', array(
            'label'    => $this->__('Revise Item(s)'),
            'url'      => '',
            'confirm'  => $this->__('Are you sure?')
        ), 'actions');

        $this->getMassactionBlock()->addItem('relist', array(
            'label'    => $this->__('Relist Item(s)'),
            'url'      => '',
            'confirm'  => $this->__('Are you sure?')
        ), 'actions');

        $this->getMassactionBlock()->addItem('stop', array(
            'label'    => $this->__('Stop Item(s)'),
            'url'      => '',
            'confirm'  => $this->__('Are you sure?')
        ), 'actions');

        $this->getMassactionBlock()->addItem('stopAndRemove', array(
            'label'    => $this->__('Stop on Channel / Remove from Listing'),
            'url'      => '',
            'confirm'  => $this->__('Are you sure?')
        ), 'actions');

        $this->getMassactionBlock()->addItem('deleteAndRemove', array(
            'label'    => $this->__('Remove from Channel & Listing'),
            'url'      => '',
            'confirm'  => $this->__('Are you sure?')
        ), 'actions');

        $this->getMassactionBlock()->addItem('remove', array(
            'label'    => $this->__('Remove from Listing'),
            'url'      => '',
            'confirm'  => $this->__('Are you sure?')
        ), 'actions');

        $this->getMassactionBlock()->addItem('assignGeneralId', array(
            'label'    => $this->__('Search Automatically'),
            'url'      => '',
            'confirm'  => $this->__('Are you sure?')
        ), 'asin_isbn');

        $this->getMassactionBlock()->addItem('newGeneralId', array(
            'label'    => $this->__('Assign Settings for New ASIN/ISBN'),
            'url'      => '',
        ), 'asin_isbn');

        $this->getMassactionBlock()->addItem('unassignGeneralId', array(
            'label'    => $this->__('Reset Information'),
            'url'      => '',
            'confirm'  => $this->__('Are you sure?')
        ), 'asin_isbn');
        // ---------------------------------------

        return parent::_prepareMassaction();
    }

    //########################################

    public function callbackColumnProductTitle($productTitle, $row, $column, $isExport)
    {
        $productTitle = $this->getHelper('Data')->escapeHtml($productTitle);

        $value = '<span>'.$productTitle.'</span>';

        if (is_null($sku = $row->getData('sku'))) {
            $sku = $this->modelFactory->getObject('Magento\Product')
                ->setProductId($row->getData('entity_id'))
                ->getSku();
        }

        $value .= '<br/><strong>'.$this->__('SKU') .
            ':</strong> '.$this->getHelper('Data')->escapeHtml($sku) . '<br/>';

        $listingProductId = (int)$row->getData('id');
        /** @var \Ess\M2ePro\Model\Listing\Product $listingProduct */
        $listingProduct = $this->amazonFactory->getObjectLoaded('Listing\Product', $listingProductId);

        if (!$listingProduct->getChildObject()->getVariationManager()->isVariationProduct()) {
            return $value;
        }

        $generalId = $row->getData('general_id');

        /** @var \Ess\M2ePro\Model\Amazon\Listing\Product $amazonListingProduct */
        $amazonListingProduct = $listingProduct->getChildObject();
        $variationManager = $amazonListingProduct->getVariationManager();

        if ($variationManager->isRelationParentType()) {

            $productAttributes = (array)$variationManager->getTypeModel()->getProductAttributes();
            $virtualProductAttributes = $variationManager->getTypeModel()->getVirtualProductAttributes();
            $virtualChannelAttributes = $variationManager->getTypeModel()->getVirtualChannelAttributes();

            $value .= '<div style="font-size: 11px; font-weight: bold; color: grey; margin-left: 7px"><br/>';
            $attributesStr = '';
            if (empty($virtualProductAttributes) && empty($virtualChannelAttributes)) {
                $attributesStr = implode(', ', $productAttributes);
            } else {
                foreach ($productAttributes as $attribute) {
                    if (in_array($attribute, array_keys($virtualProductAttributes))) {

                        $attributesStr .= '<span style="border-bottom: 2px dotted grey">' . $attribute .
                            ' (' . $virtualProductAttributes[$attribute] . ')</span>, ';

                    } else if (in_array($attribute, array_keys($virtualChannelAttributes))) {

                        $attributesStr .= '<span>' . $attribute .
                            ' (' . $virtualChannelAttributes[$attribute] . ')</span>, ';

                    } else {
                        $attributesStr .= $attribute . ', ';
                    }
                }
                $attributesStr = rtrim($attributesStr, ', ');
            }
            $value .= $attributesStr;

            if (empty($generalId) && !$amazonListingProduct->isGeneralIdOwner()) {
                $popupTitle = $this->getHelper('Data')->escapeJs($this->getHelper('Data')->escapeHtml(
                    $this->__('Manage Magento Product Variations'))
                );

                $linkTitle = $this->getHelper('Data')->escapeJs($this->getHelper('Data')->escapeHtml(
                    $this->__('Change "Magento Variations" Mode'))
                );

                $iconSettingsPath = $this->getViewFileUrl('Ess_M2ePro::images/settings.png');

                $switchToIndividualJsMethod = <<<JS
AmazonListingProductVariationObj
    .setListingProductId({$listingProductId})
    .showSwitchToIndividualModePopUp('{$popupTitle}');
JS;

                if ($this->hideSwitchToIndividualConfirm) {
                    $switchToIndividualJsMethod = <<<JS
AmazonListingProductVariationObj
    .setListingProductId({$listingProductId})
        .showManagePopup('{$popupTitle}');
JS;
                }

                $value .= <<<HTML
&nbsp;
<a  href="javascript:"
    class="amazon-listing-view-switch-variation-mode"
    onclick="{$switchToIndividualJsMethod}"
    title="{$linkTitle}">
</a>
HTML;
            }

            $value .= '</div>';

            if (!empty($generalId) || $amazonListingProduct->isGeneralIdOwner()) {
                /** @var ParentRelation $parentType */
                $parentType = $variationManager->getTypeModel();

                $linkContent = $this->__('Manage Variations');
                $vpmt = $this->__('Manage Variations of &quot;%s%&quot; ', $productTitle);
                $vpmt = addslashes($vpmt);

                if (!empty($generalId)) {
                    $vpmt .= '('. $generalId .')';
                }

                $problemStyle = '';
                $problemIcon = '';

                $linkTitle = $this->__('Open Manage Variations Tool');

                if (empty($generalId) && $amazonListingProduct->isGeneralIdOwner()) {
                    if (!$parentType->hasChannelTheme() || !$parentType->hasMatchedAttributes()) {

                        $linkTitle = $this->__('Action Required');
                        $problemStyle = 'style="font-weight: bold; color: #FF0000;" ';
                        $iconPath = $this->getViewFileUrl('Ess_M2ePro::images/error.png');
                        $problemIcon = '<img style="vertical-align: middle;" src="'
                            . $iconPath . '" title="' . $linkTitle . '" alt="" width="16" height="16">';
                    }
                } elseif (!empty($generalId)) {
                    if (!$parentType->hasMatchedAttributes()) {

                        $linkTitle = $this->__('Action Required');
                        $problemStyle = 'style="font-weight: bold;color: #FF0000;" ';
                        $iconPath = $this->getViewFileUrl('Ess_M2ePro::images/error.png');
                        $problemIcon = '<img style="vertical-align: middle;" src="'
                            . $iconPath . '" title="' . $linkTitle . '" alt="" width="16" height="16">';
                    } elseif (($listingProduct->getChildObject()->isGeneralIdOwner() &&
                              !$parentType->hasChannelTheme()) ||
                              $this->hasChildWithWarning($listingProductId)) {

                        $linkTitle = $this->__('Action Required');
                        $problemStyle = 'style="font-weight: bold;" ';
                        $iconPath = $this->getViewFileUrl('Ess_M2ePro::images/warning.png');
                        $problemIcon = '<img style="vertical-align: middle;" src="'
                            . $iconPath . '" title="' . $linkTitle . '" alt="" width="16" height="16">';
                    }
                }

                $value .= <<<HTML
<div style="float: left; margin: 0 0 0 7px">
    <a {$problemStyle}href="javascript:"
    onclick="ListingGridHandlerObj.variationProductManageHandler.openPopUp({$listingProductId}, '{$vpmt}')"
    title="{$linkTitle}">{$linkContent}</a>&nbsp;{$problemIcon}
</div>
HTML;
            }

            return $value;
        }

        $productOptions = $variationManager->getTypeModel()->getProductOptions();

        if (!empty($productOptions)) {
            $value .= '<div style="font-size: 11px; color: grey; margin-left: 7px"><br/>';
            foreach ($productOptions as $attribute => $option) {
                !$option && $option = '--';
                $value .= '<strong>' . $this->getHelper('Data')->escapeHtml($attribute) .
                    '</strong>:&nbsp;' . $this->getHelper('Data')->escapeHtml($option) . '<br/>';
            }
            $value .= '</div>';
        }

        // ---------------------------------------
        $hasInActionLock = $this->getLockedData($row);
        $hasInActionLock = $hasInActionLock['in_action'];
        // ---------------------------------------

        if (!$hasInActionLock) {

            $popupTitle = $this->__('Manage Magento Product Variation');
            $linkTitle  = $this->__('Edit Variation');

            $value .= <<<HTML
<div style="clear: both"></div>
<div style="margin: 0 0 0 7px; float: left;">
    <a  href="javascript:"
        class="amazon-listing-view-edit-variation"
        onclick="AmazonListingProductVariationObj
            .setListingProductId({$listingProductId})
            .showEditPopup('{$popupTitle}');"
        title="{$linkTitle}"></a>
</div>
HTML;
        }

        $popupTitle = $this->__('Manage Magento Product Variations')   ;
        $linkTitle  = $this->__('Add Another Variation(s)');

        $value.= <<<HTML
<div style="margin: 0 0 0 7px; float: left;">
    <a  href="javascript:"
        class="amazon-listing-view-add-variation"
        onclick="AmazonListingProductVariationObj
            .setListingProductId({$listingProductId})
            .showManagePopup('{$popupTitle}');"
        title="{$linkTitle}"></a>
</div>
HTML;

        if (empty($generalId) && !$amazonListingProduct->isGeneralIdOwner()) {
            $linkTitle = $this->getHelper('Data')->escapeJs($this->getHelper('Data')->escapeHtml(
                $this->__('Change "Magento Variations" Mode'))
            );

            $switchToParentJsMethod = <<<JS
AmazonListingProductVariationObj
    .setListingProductId({$listingProductId})
        .showSwitchToParentModePopUp('{$popupTitle}');
JS;

            if ($this->hideSwitchToParentConfirm) {
                $switchToParentJsMethod = <<<JS
AmazonListingProductVariationObj
    .setListingProductId({$listingProductId})
        .resetListingProductVariation();
JS;
            }

            $value .= <<<HTML
<div style="margin: 0 0 0 7px; float: left;">
    <a href="javascript:"
        class="amazon-listing-view-switch-variation-mode"
        onclick="{$switchToParentJsMethod}"
        title="{$linkTitle}"></a>
</div>
HTML;
        }

        return $value;
    }

    public function callbackColumnAmazonSku($value, $row, $column, $isExport)
    {
        if (is_null($value) || $value === '') {
            $value = $this->__('N/A');
        }

        if (!$row->getData('is_variation_parent') && $row->getData('defected_messages')) {
            $defectedMessages = json_decode($row->getData('defected_messages'), true);

            $msg = '';
            foreach ($defectedMessages as $message) {
                if (empty($message['message'])) {
                    continue;
                }

                $msg .= '<p>'.$message['message'] . '&nbsp;';
                if (!empty($message['value'])) {
                    $msg .= $this->__('Current Value') . ': "' . $message['value'] . '"';
                }
                $msg .= '</p>';
            }

            if (empty($msg)) {
                return $value;
            }

            $value .= <<<HTML
<span style="float:right;">
    {$this->getTooltipHtml($msg, 'map_link_defected_message_icon_'.$row->getId())}
</span>
HTML;
        }

        return $value;
    }

    public function callbackColumnGeneralId($generalId, $row, $column, $isExport)
    {
        if (empty($generalId)) {
            if ($row->getData('is_general_id_owner') == 1) {
                return $this->getGeneralIdColumnValueGeneralIdOwner($row);
            }
            return $this->getGeneralIdColumnValueEmptyGeneralId($row);
        }

        return $this->getGeneralIdColumnValueNotEmptyGeneralId($row);
    }

    public function callbackColumnAvailableQty($value, $row, $column, $isExport)
    {
        $listingProductId = $row->getData('id');

        if (!$row->getData('is_variation_parent')) {

            if ($row->getData('amazon_status') == \Ess\M2ePro\Model\Listing\Product::STATUS_NOT_LISTED) {
                return '<span style="color: gray;">' . $this->__('Not Listed') . '</span>';
            }

            if ((bool)$row->getData('is_afn_channel')) {
                $sku = $row->getData('amazon_sku');

                if (empty($sku)) {
                    return $this->__('AFN');
                }

                /** @var \Ess\M2ePro\Model\Listing\Product $listingProduct */
                $listingProduct = $this->amazonFactory->getObjectLoaded('Listing\Product', $listingProductId);

                $afn = $this->__('AFN');
                $total = $this->__('Total');
                $inStock = $this->__('In Stock');
                $accountId = $listingProduct->getListing()->getAccountId();

                return <<<HTML
<div id="m2ePro_afn_qty_value_{$listingProductId}">
    <span class="m2ePro-online-sku-value" productId="{$listingProductId}" style="display: none">{$sku}</span>
    <span class="m2epro-empty-afn-qty-data" style="display: none">{$afn}</span>
    <div class="m2epro-afn-qty-data" style="display: none">
        <div class="total">{$total}: <span></span></div>
        <div class="in-stock">{$inStock}: <span></span></div>
    </div>
    <a href="javascript:void(0)"
        onclick="AmazonListingAfnQtyObj.showAfnQty(this,'{$sku}',{$listingProductId}, {$accountId})">
        {$afn}
    </a>
</div>
HTML;
            }

            if (is_null($value) || $value === '') {
                return '<i style="color:gray;">receiving...</i>';
            }

            if ($value <= 0) {
                return '<span style="color: red;">0</span>';
            }

            return $value;
        }

        if ($row->getData('amazon_status') == \Ess\M2ePro\Model\Listing\Product::STATUS_NOT_LISTED ||
            $row->getData('general_id') == '') {
            return '<span style="color: gray;">' . $this->__('Not Listed') . '</span>';
        }

        $variationChildStatuses = json_decode($row->getData('variation_child_statuses'), true);

        $activeChildrenCount = 0;
        foreach ($variationChildStatuses as $childStatus => $count) {
            if ($childStatus == \Ess\M2ePro\Model\Listing\Product::STATUS_NOT_LISTED) {
                continue;
            }
            $activeChildrenCount += (int)$count;
        }

        if ($activeChildrenCount == 0) {
            return $this->__('N/A');
        }

        if (!(bool)$row->getData('is_afn_channel')) {
            return $value;
        }

        $resultValue = $this->__('AFN');

        if ($row->getData('variation_afn_count')) {
            $filter = base64_encode('online_qty[afn]=1');

            $productTitle = $this->getHelper('Data')->escapeHtml($row->getData('name'));
            $vpmt = $this->__('Manage Variations of &quot;%s%&quot; ', $productTitle);
            $vpmt = addslashes($vpmt);

            $linkTitle = $this->__('Show AFN Child Products.');

            $resultValue = $resultValue."&nbsp;<a href=\"javascript:void(0)\"
                               class=\"hover-underline\"
                               title=\"{$linkTitle}\"
                               onclick=\"ListingGridHandlerObj.variationProductManageHandler.openPopUp(
                                {$listingProductId}, '{$vpmt}', '{$filter}'
                            )\">[".$row->getData('variation_afn_count')."]</a>";
        }

        if ($value > 0) {
            $resultValue = $value . '<br/>' . $resultValue;
        }

        return $resultValue;
    }

    public function callbackColumnPrice($value, $row, $column, $isExport)
    {
        if ($row->getData('amazon_status') == \Ess\M2ePro\Model\Listing\Product::STATUS_NOT_LISTED) {
            return '<span style="color: gray;">' . $this->__('Not Listed') . '</span>';
        }

        $listingProductId = (int)$row->getData('id');

        $repricingHtml ='';

//        TODO
//        if (Mage::helper('M2ePro/Component_Amazon_Repricing')->isEnabled() &&
//            ($row->getData('is_repricing') || $row->getData('is_variation_parent'))
//        ) {
//
//            $enabledCount  = $row->getData('variation_repricing_enabled_count');
//            $disabledCount = $row->getData('variation_repricing_disabled_count');
//
//            if ($row->getData('is_variation_parent') && ($enabledCount || $disabledCount)) {
//
//                if ($enabledCount && $disabledCount) {
//                    $image = 'money_mixed';
//                    $countHtml = '['.$enabledCount.'/'.$disabledCount.']';
//                    $text = $this->__('Parent has enabled and disabled repricing products. TODO TEXT.');
//                } elseif ($enabledCount) {
//                    $image = 'money';
//                    $countHtml = '['.$enabledCount.']';
//                    $text = $this->__('Parent has enabled repricing products. TODO TEXT.');
//                } else {
//                    $image = 'money_disabled';
//                    $countHtml = '['.$disabledCount.']';
//                    $text = $this->__('Parent has disabled repricing products. TODO TEXT.');
//                }
//
//                $filter = base64_encode('online_price[is_repricing]=1');
//
//                $productTitle = $this->getHelper('Data')->escapeHtml($row->getData('name'));
//                $vpmt = $this->__('Manage Variations of &quot;%s%&quot; ', $productTitle);
//                $vpmt = addslashes($vpmt);
//
//                $generalId = $row->getData('general_id');
//                if (!empty($generalId)) {
//                    $vpmt .= '('. $generalId .')';
//                }
//
//                $linkTitle = $this->__('Show repricing child products. TODO TEXT');
//
//                $repricingHtml = <<<HTML
//<br /><span style="float:right; text-align: left;">
//    <img class="tool-tip-image"
//         style="vertical-align: middle; width: 16px;"
//         src="{$this->getViewFileUrl('Ess_M2ePro::images/'.$image.'.png')}">
//    <span class="tool-tip-message tool-tip-message tip-left" style="display:none;">
//        <img src="{$this->getViewFileUrl('Ess_M2ePro::images/i_icon.png')}">
//        <span>{$text}</span>
//    </span>&nbsp;<a href="javascript:void(0)"
//       class="hover-underline"
//       title="{$linkTitle}"
//       onclick="ListingGridHandlerObj.variationProductManageHandler.openPopUp(
//        {$listingProductId}, '{$vpmt}', '{$filter}'
//    )">$countHtml</a>
//</span>
//HTML;
//            } elseif (!$row->getData('is_variation_parent')) {
//                $image = 'money';
//                $text = $this->__(
//                    'This product is used by Amazon Repricing Tool.
//                 The Price cannot be updated through the M2E Pro.'
//                );
//
//                if ((int)$row->getData('is_repricing_disabled') == 1) {
//                    $image = 'money_disabled';
//                    $text = $this->__(
//                        'This product is disabled on Amazon Repricing Tool.
//                     The Price is updated through the M2E Pro.'
//                    );
//                }
//
//                $repricingHtml = <<<HTML
//<span style="float:right; text-align: left;">&nbsp;
//    <img class="tool-tip-image"
//         style="vertical-align: middle; width: 16px;"
//         src="{$this->getViewFileUrl('Ess_M2ePro::images/'.$image.'.png')}">
//    <span class="tool-tip-message tool-tip-message tip-left" style="display:none;">
//        <img src="{$this->getViewFileUrl('Ess_M2ePro::images/i_icon.png')}">
//        <span>{$text}</span>
//    </span>
//</span>
//HTML;
//            }
//        }

        $onlineMinPrice = $row->getData('min_online_price');
        $onlineMaxPrice = $row->getData('max_online_price');

        if (is_null($onlineMinPrice) || $onlineMinPrice === '') {
            if ($row->getData('amazon_status') == \Ess\M2ePro\Model\Listing\Product::STATUS_NOT_LISTED ||
                $row->getData('is_variation_parent')
            ) {
                return $this->__('N/A') . $repricingHtml;
            } else {
                return '<i style="color:gray;">receiving...</i>' . $repricingHtml;
            }
        }

        $currency = $this->listing->getMarketplace()->getChildObject()->getDefaultCurrency();

        if ($row->getData('is_variation_parent')) {
            $onlineMinPriceStr = $this->convertAndFormatPriceCurrency($onlineMinPrice, $currency);
            $onlineMaxPriceStr = $this->convertAndFormatPriceCurrency($onlineMaxPrice, $currency);

            return $onlineMinPriceStr.(($onlineMinPrice != $onlineMaxPrice)?' - '.$onlineMaxPriceStr:'').$repricingHtml;
        }

        $onlinePrice = $row->getData('online_price');
        if ((float)$onlinePrice <= 0) {
            $priceValue = '<span style="color: #f00;">0</span>';
        } else {
            $priceValue = $this->convertAndFormatPriceCurrency($onlinePrice, $currency);
        }

        if ($row->getData('is_repricing') &&
            !$row->getData('is_repricing_disabled') &&
            !$row->getData('is_variation_parent')
        ) {
            $this->listing = $this->getHelper('Data\GlobalData')->getValue('temp_data');
            $accountId = $this->listing['account_id'];
            $sku = $row->getData('amazon_sku');

            $priceValue =<<<HTML
<a id="m2epro_repricing_price_value_{$sku}"
   class="m2epro-repricing-price-value"
   sku="{$sku}"
   account_id="{$accountId}"
   href="javascript:void(0)"
   onclick="CommonAmazonListingRepricingPriceHandlerObj.showRepricingPrice()">
    {$priceValue}
</a>
HTML;
        }

        $resultHtml = '';

        $salePrice = $row->getData('online_sale_price');
        if (!$row->getData('is_variation_parent') && (float)$salePrice > 0) {
            $currentTimestamp = strtotime($this->getHelper('Data')->getCurrentGmtDate(false,'Y-m-d 00:00:00'));

            $startDateTimestamp = strtotime($row->getData('online_sale_price_start_date'));
            $endDateTimestamp   = strtotime($row->getData('online_sale_price_end_date'));

            if ($currentTimestamp <= $endDateTimestamp) {
                $fromDate = $this->_localeDate->formatDate(
                    $row->getData('online_sale_price_start_date'), \IntlDateFormatter::MEDIUM
                );

                $toDate = $this->_localeDate->formatDate(
                    $row->getData('online_sale_price_end_date'), \IntlDateFormatter::MEDIUM
                );

                $intervalHtml = <<<HTML
<div class="m2epro-field-tooltip m2epro-field-tooltip-price-info admin__field-tooltip">
    <a class="admin__field-tooltip-action" href="javascript://"></a>
    <div class="admin__field-tooltip-content">
        <span style="color:gray;">
            <strong>From:</strong> {$fromDate}<br/>
            <strong>To:</strong> {$toDate}
        </span>    
    </div>
</div>
HTML;

                $salePriceValue = $this->convertAndFormatPriceCurrency($salePrice, $currency);

                if ($currentTimestamp >= $startDateTimestamp &&
                    $currentTimestamp <= $endDateTimestamp &&
                    $salePrice < (float)$onlinePrice
                ) {
                    $resultHtml .= '<span style="color: grey; text-decoration: line-through;">'.$priceValue.'</span>' .
                                    $repricingHtml;
                    $resultHtml .= '<br/>'.$intervalHtml.'&nbsp;'.$salePriceValue;
                } else {
                    $resultHtml .= $priceValue . $repricingHtml;
                    $resultHtml .= '<br/>'.$intervalHtml.
                        '<span style="color:gray;">'.'&nbsp;'.$salePriceValue.'</span>';
                }
            }
        }

        if (empty($resultHtml)) {
            $resultHtml = $priceValue . $repricingHtml;
        }

        return $resultHtml;
    }

    public function callbackColumnStatus($value, $row, $column, $isExport)
    {
        $listingProductId  = (int)$row->getData('id');
        $isVariationParent = (bool)(int)$row->getData('is_variation_parent');
        $additionalData    = (array)json_decode($row->getData('additional_data'), true);

        $html = $this->getViewLogIconHtml($listingProductId, $isVariationParent);

        if (!empty($additionalData['synch_template_list_rules_note'])) {

            $synchNote = $this->getHelper('View')->getModifiedLogMessage(
                $additionalData['synch_template_list_rules_note']
            );

            if (empty($html)) {
                $html = <<<HTML
<span style="float:right;">
    {$this->getTooltipHtml($synchNote, 'map_link_error_icon_'.$row->getId())}
</span>
HTML;
            } else {
                $html .= <<<HTML
<div id="synch_template_list_rules_note_{$listingProductId}" style="display: none">{$synchNote}</div>
HTML;
            }
        }

        if (!$isVariationParent) {
            return $html . $this->getProductStatus($row->getData('amazon_status')). $this->getLockedTag($row);
        } else {

            $statusUnknown   = \Ess\M2ePro\Model\Listing\Product::STATUS_UNKNOWN;
            $statusNotListed = \Ess\M2ePro\Model\Listing\Product::STATUS_NOT_LISTED;
            $statusListed    = \Ess\M2ePro\Model\Listing\Product::STATUS_LISTED;
            $statusStopped   = \Ess\M2ePro\Model\Listing\Product::STATUS_STOPPED;
            $statusBlocked   = \Ess\M2ePro\Model\Listing\Product::STATUS_BLOCKED;

            $generalId = $row->getData('general_id');
            $variationChildStatuses = $row->getData('variation_child_statuses');
            if (empty($generalId) || empty($variationChildStatuses)) {
                return $html . $this->getProductStatus($statusNotListed) .
                    $this->getLockedTag($row);
            }

            $variationChildStatuses = json_decode($variationChildStatuses, true);

            $sortedStatuses = array();
            if (isset($variationChildStatuses[$statusUnknown])) {
                $sortedStatuses[$statusUnknown] = $variationChildStatuses[$statusUnknown];
            }
            if (isset($variationChildStatuses[$statusNotListed])) {
                $sortedStatuses[$statusNotListed] = $variationChildStatuses[$statusNotListed];
            }
            if (isset($variationChildStatuses[$statusListed])) {
                $sortedStatuses[$statusListed] = $variationChildStatuses[$statusListed];
            }
            if (isset($variationChildStatuses[$statusStopped])) {
                $sortedStatuses[$statusStopped] = $variationChildStatuses[$statusStopped];
            }
            if (isset($variationChildStatuses[$statusBlocked])) {
                $sortedStatuses[$statusBlocked] = $variationChildStatuses[$statusBlocked];
            }

            $linkTitle = $this->__('Show all Child Products with such Status');

            foreach ($sortedStatuses as $status => $productsCount) {
                if (empty($productsCount)) {
                    continue;
                }

                $filter = base64_encode('status=' . $status);

                $productTitle = $this->getHelper('Data')->escapeHtml($row->getData('name'));
                $vpmt = $this->__('Manage Variations of &quot;%s%&quot; ', $productTitle);
                $vpmt = addslashes($vpmt);

                $generalId = $row->getData('general_id');
                if (!empty($generalId)) {
                    $vpmt .= '('. $generalId .')';
                }

                $productsCount = <<<HTML
<a onclick="ListingGridHandlerObj.variationProductManageHandler.openPopUp({$listingProductId}, '{$vpmt}', '{$filter}')"
   class="hover-underline"
   title="{$linkTitle}"
   href="javascript:void(0)">[{$productsCount}]</a>
HTML;

                $html .= $this->getProductStatus($status) . '&nbsp;'. $productsCount . '<br/>';
            }

            $html .= $this->getLockedTag($row);
        }

        return $html;
    }

    private function getProductStatus($status)
    {
        switch ($status) {

            case \Ess\M2ePro\Model\Listing\Product::STATUS_UNKNOWN:
                return '<span style="color: gray;">' . $this->__('Unknown') . '</span>';

            case \Ess\M2ePro\Model\Listing\Product::STATUS_NOT_LISTED:
                return '<span style="color: gray;">' . $this->__('Not Listed') . '</span>';

            case \Ess\M2ePro\Model\Listing\Product::STATUS_LISTED:
                return '<span style="color: green;">' . $this->__('Active') . '</span>';

            case \Ess\M2ePro\Model\Listing\Product::STATUS_STOPPED:
                return'<span style="color: red;">' . $this->__('Inactive') . '</span>';

            case \Ess\M2ePro\Model\Listing\Product::STATUS_BLOCKED:
                return'<span style="color: orange; font-weight: bold;">' .
                    $this->__('Inactive (Blocked)') . '</span>';
        }

        return '';
    }

    private function getLockedTag($row)
    {
        $html = '';

        $tempLocks = $this->getLockedData($row);
        $tempLocks = $tempLocks['object_locks'];

        $childCount = 0;

        foreach ($tempLocks as $lock) {

            switch ($lock->getTag()) {

                case 'list_action':
                    $html .= '<br/><span style="color: #605fff">[List in Progress...]</span>';
                    break;

                case 'relist_action':
                    $html .= '<br/><span style="color: #605fff">[Relist in Progress...]</span>';
                    break;

                case 'revise_action':
                    $html .= '<br/><span style="color: #605fff">[Revise in Progress...]</span>';
                    break;

                case 'stop_action':
                    $html .= '<br/><span style="color: #605fff">[Stop in Progress...]</span>';
                    break;

                case 'stop_and_remove_action':
                    $html .= '<br/><span style="color: #605fff">[Stop And Remove in Progress...]</span>';
                    break;

                case 'delete_and_remove_action':
                    $html .= '<br/><span style="color: #605fff">[Remove in Progress...]</span>';
                    break;

                case 'switch_to_afn_action':
                    $html .= '<br/><span style="color: #605fff">[Switch to AFN in Progress...]</span>';
                    break;

                case 'switch_to_mfn_action':
                    $html .= '<br/><span style="color: #605fff">[Switch to MFN in Progress...]</span>';
                    break;

                case 'child_products_in_action':
                    $childCount++;
                    break;

                default:
                    break;

            }
        }

        if ($childCount > 0) {
            $html .= '<br/><span style="color: #605fff">[Child(s) in Action...]</span>';
        }

        return $html;
    }

    // ---------------------------------------

    protected function callbackFilterTitle($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value == null) {
            return;
        }

        $collection->addFieldToFilter(
            array(
                array('attribute'=>'sku','like'=>'%'.$value.'%'),
                array('attribute'=>'name', 'like'=>'%'.$value.'%')
            )
        );
    }

    protected function callbackFilterQty($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if (empty($value)) {
            return;
        }

        $where = '';

        if (isset($value['from']) && $value['from'] != '') {
            $where .= 'online_qty >= ' . $value['from'];
        }

        if (isset($value['to']) && $value['to'] != '') {
            if (isset($value['from']) && $value['from'] != '') {
                $where .= ' AND ';
            }
            $where .= 'online_qty <= ' . $value['to'];
        }

        if (!empty($value['afn'])) {
            if (!empty($where)) {
                $where = '(' . $where . ') OR ';
            }
            $where .= 'is_afn_channel = ' . \Ess\M2ePro\Model\Amazon\Listing\Product::IS_AFN_CHANNEL_YES;
        }

        $collection->getSelect()->where($where);
    }

    protected function callbackFilterPrice($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if (empty($value)) {
            return;
        }

        $condition = '';

        if (isset($value['from']) || isset($value['to'])) {

            if (isset($value['from']) && $value['from'] != '') {
                $condition = 'min_online_price >= \''.$value['from'].'\'';
            }
            if (isset($value['to']) && $value['to'] != '') {
                if (isset($value['from']) && $value['from'] != '') {
                    $condition .= ' AND ';
                }
                $condition .= 'min_online_price <= \''.$value['to'].'\'';
            }

            $condition = '(' . $condition . ') OR (';

            if (isset($value['from']) && $value['from'] != '') {
                $condition .= 'max_online_price >= \''.$value['from'].'\'';
            }
            if (isset($value['to']) && $value['to'] != '') {
                if (isset($value['from']) && $value['from'] != '') {
                    $condition .= ' AND ';
                }
                $condition .= 'max_online_price <= \''.$value['to'].'\'';
            }

            $condition .= ')';

        }

//        TODO
//        if (Mage::helper('M2ePro/Component_Amazon_Repricing')->isEnabled() && !empty($value['is_repricing'])) {
//            if (!empty($condition)) {
//                $condition = '(' . $condition . ') OR ';
//            }
//
//            $condition .= 'is_repricing > 0 OR
//                           variation_repricing_enabled_count IS NOT NULL OR
//                           variation_repricing_disabled_count IS NOT NULL';
//        }

        $collection->getSelect()->having($condition);
    }

    protected function callbackFilterStatus($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value == null) {
            return;
        }

        $collection->getSelect()->where("lp.status = {$value} OR
            (alp.variation_child_statuses REGEXP '\"{$value}\":[^0]') AND alp.is_variation_parent = 1");
    }

    // ---------------------------------------

    public function getViewLogIconHtml($listingProductId, $isVariationParent)
    {
        $listingProductId = (int)$listingProductId;

        $connection = $this->resourceConnection->getConnection();

        // Get last messages
        // ---------------------------------------
        $dbSelect = $connection->select()
            ->from(
                $this->activeRecordFactory->getObject('Listing\Log')->getResource()->getMainTable(),
                array('action_id','action','type','description','create_date','initiator','listing_product_id')
            )
            ->where('`action_id` IS NOT NULL')
            ->order(array('id DESC'))
            ->limit(30);

        if ($isVariationParent) {
            $dbSelect->where('listing_product_id = ? OR parent_listing_product_id = ?', $listingProductId);
        } else {
            $dbSelect->where('listing_product_id = ?', $listingProductId);
        }

        $logRows = $connection->fetchAll($dbSelect);
        // ---------------------------------------

        // Get grouped messages by action_id
        // ---------------------------------------
        $actionsRows = array();
        $tempActionRows = array();
        $lastActionId = false;

        foreach ($logRows as $row) {

            $row['description'] = $this->getHelper('View')->getModifiedLogMessage($row['description']);

            if ($row['action_id'] !== $lastActionId) {
                if (count($tempActionRows) > 0) {
                    $actionsRows[] = array(
                        'action_id' => $lastActionId,
                        'type' => $this->getMainTypeForActionId($tempActionRows),
                        'date' => $this->getMainDateForActionId($tempActionRows),
                        'action' => $this->getActionForAction($tempActionRows[0]),
                        'initiator' => $this->getInitiatorForAction($tempActionRows[0]),
                        'items' => $tempActionRows
                    );
                    $tempActionRows = array();
                }
                $lastActionId = $row['action_id'];
            }
            $tempActionRows[] = $row;
        }

        if (count($tempActionRows) > 0) {
            $actionsRows[] = array(
                'action_id' => $lastActionId,
                'type' => $this->getMainTypeForActionId($tempActionRows),
                'date' => $this->getMainDateForActionId($tempActionRows),
                'action' => $this->getActionForAction($tempActionRows[0]),
                'initiator' => $this->getInitiatorForAction($tempActionRows[0]),
                'items' => $tempActionRows
            );
        }

        if (count($actionsRows) <= 0) {
            return '';
        }

        if ($isVariationParent) {
            foreach ($actionsRows as &$actionsRow) {
                if (!empty($actionsRow['items']) && $actionsRow['items'][0]['listing_product_id']==$listingProductId) {
                    continue;
                }
                // todo is not used !!
                $actionsRow['action_in_progress'] = $this->isActionInProgress($actionsRow['action_id']);

                $descArr = array();
                foreach ($actionsRow['items'] as $key => &$item) {
                    if (array_key_exists($item['description'], $descArr)) {
                        $descArr[$item['description']]['count']++;
                        unset($actionsRow['items'][$key]);
                        continue;
                    }
                    $item['count'] = 1;
                    $descArr[$item['description']] = $item;
                }
                $actionsRow['items'] = array_values($descArr);
            }
        }

        foreach ($actionsRows as &$actionsRow) {
            usort($actionsRow['items'], function($a, $b)
            {
                $sortOrder = array(
                    \Ess\M2ePro\Model\Log\AbstractLog::TYPE_SUCCESS => 1,
                    \Ess\M2ePro\Model\Log\AbstractLog::TYPE_ERROR => 2,
                    \Ess\M2ePro\Model\Log\AbstractLog::TYPE_WARNING => 3,
                );

                return $sortOrder[$a["type"]] > $sortOrder[$b["type"]];
            });
        }

        $tips = array(
            \Ess\M2ePro\Model\Log\AbstractLog::TYPE_SUCCESS => 'Last Action was completed successfully.',
            \Ess\M2ePro\Model\Log\AbstractLog::TYPE_ERROR => 'Last Action was completed with error(s).',
            \Ess\M2ePro\Model\Log\AbstractLog::TYPE_WARNING => 'Last Action was completed with warning(s).'
        );

        $icons = array(
            \Ess\M2ePro\Model\Log\AbstractLog::TYPE_SUCCESS => 'normal',
            \Ess\M2ePro\Model\Log\AbstractLog::TYPE_ERROR => 'error',
            \Ess\M2ePro\Model\Log\AbstractLog::TYPE_WARNING => 'warning'
        );

        $summary = $this->createBlock('Log\Grid\Summary', '', ['data' => [
            'entity_id' => $listingProductId,
            'rows' => $actionsRows,
            'tips' => $tips,
            'icons' => $icons,
            'view_help_handler' => 'ListingGridHandlerObj.viewItemHelp',
            'hide_help_handler' => 'ListingGridHandlerObj.hideItemHelp',
        ]]);

        return $summary->toHtml();
    }

    public function getActionForAction($actionRows)
    {
        $string = '';

        switch ($actionRows['action']) {
            case \Ess\M2ePro\Model\Listing\Log::ACTION_LIST_PRODUCT_ON_COMPONENT:
                $string = $this->__('List');
                break;
            case \Ess\M2ePro\Model\Listing\Log::ACTION_RELIST_PRODUCT_ON_COMPONENT:
                $string = $this->__('Relist');
                break;
            case \Ess\M2ePro\Model\Listing\Log::ACTION_REVISE_PRODUCT_ON_COMPONENT:
                $string = $this->__('Revise');
                break;
            case \Ess\M2ePro\Model\Listing\Log::ACTION_STOP_PRODUCT_ON_COMPONENT:
                $string = $this->__('Stop');
                break;
            case \Ess\M2ePro\Model\Listing\Log::ACTION_DELETE_PRODUCT_FROM_COMPONENT:
                $string = $this->__('Remove from Channel');
                break;
            case \Ess\M2ePro\Model\Listing\Log::ACTION_STOP_AND_REMOVE_PRODUCT:
                $string = $this->__('Stop on Channel / Remove from Listing');
                break;
            case \Ess\M2ePro\Model\Listing\Log::ACTION_DELETE_AND_REMOVE_PRODUCT:
                $string = $this->__('Remove from Channel & Listing');
                break;
            case \Ess\M2ePro\Model\Listing\Log::ACTION_DELETE_PRODUCT_FROM_LISTING:
                $string = $this->__('Remove from Listing');
                break;
            case \Ess\M2ePro\Model\Listing\Log::ACTION_CHANNEL_CHANGE:
                $string = $this->__('Channel Change');
                break;
            case \Ess\M2ePro\Model\Listing\Log::ACTION_SWITCH_TO_AFN_ON_COMPONENT:
                $string = $this->__('Switch to AFN');
                break;
            case \Ess\M2ePro\Model\Listing\Log::ACTION_SWITCH_TO_MFN_ON_COMPONENT:
                $string = $this->__('Switch to MFN');
                break;
        }

        return $string;
    }

    public function getInitiatorForAction($actionRows)
    {
        $string = '';

        switch ((int)$actionRows['initiator']) {
            case \Ess\M2ePro\Helper\Data::INITIATOR_UNKNOWN:
                $string = '';
                break;
            case \Ess\M2ePro\Helper\Data::INITIATOR_USER:
                $string = $this->__('Manual');
                break;
            case \Ess\M2ePro\Helper\Data::INITIATOR_EXTENSION:
                $string = $this->__('Automatic');
                break;
        }

        return $string;
    }

    public function getMainTypeForActionId($actionRows)
    {
        $type = \Ess\M2ePro\Model\Log\AbstractLog::TYPE_SUCCESS;

        foreach ($actionRows as $row) {
            if ($row['type'] == \Ess\M2ePro\Model\Log\AbstractLog::TYPE_ERROR) {
                $type = \Ess\M2ePro\Model\Log\AbstractLog::TYPE_ERROR;
                break;
            }
            if ($row['type'] == \Ess\M2ePro\Model\Log\AbstractLog::TYPE_WARNING) {
                $type = \Ess\M2ePro\Model\Log\AbstractLog::TYPE_WARNING;
            }
        }

        return $type;
    }

    public function getMainDateForActionId($actionRows)
    {
        return $this->_localeDate->formatDate($actionRows[0]['create_date'], \IntlDateFormatter::MEDIUM, true);
    }

    public function isActionInProgress($actionId)
    {
        $connection = $this->resourceConnection->getConnection();

        // Get last messages
        // ---------------------------------------
        $dbSelect = $connection->select()
            ->from(
                $this->activeRecordFactory->getObject('Processing')->getResource()->getMainTable()
            )
            ->where('params REGEXP \'"logs_action_id":'.$actionId.'\'')
            ->limit(1);

        $result = $connection->query($dbSelect)->fetch();
        return  $result !== false;
    }

    //########################################

    public function getRowUrl($row)
    {
        return false;
    }

    //########################################

    public function getTooltipHtml($content, $id = '')
    {
        return <<<HTML
<div style="width: 25px; display: inline-block; margin-left: -23px;">
    <div id="{$id}" class="m2epro-field-tooltip admin__field-tooltip" style="vertical-align: inherit; margin: 0;">
        <a class="admin__field-tooltip-action" href="javascript://"></a>
        <div class="admin__field-tooltip-content" style="">
            {$content}
        </div>
    </div>
</div>
HTML;
    }

    //########################################

    protected function _toHtml()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->js->add(
<<<JS
    ListingGridHandlerObj.afterInitPage();
JS
            );
        }

        return parent::_toHtml();
    }

    //########################################

    private function getLockedData($row)
    {
        $listingProductId = $row->getData('id');
        if (!isset($this->lockedDataCache[$listingProductId])) {
            $objectLocks = $this->activeRecordFactory->getObjectLoaded('Listing\Product', $listingProductId)
                ->getProcessingLocks();
            $tempArray = array(
                'object_locks' => $objectLocks,
                'in_action' => !empty($objectLocks),
            );
            $this->lockedDataCache[$listingProductId] = $tempArray;
        }

        return $this->lockedDataCache[$listingProductId];
    }

    //########################################

    private function getGeneralIdColumnValueEmptyGeneralId($row)
    {
        // ---------------------------------------
        if ((int)$row->getData('amazon_status') != \Ess\M2ePro\Model\Listing\Product::STATUS_NOT_LISTED) {
            return '<i style="color:gray;">'.$this->__('receiving...').'</i>';
        }
        // ---------------------------------------

        // ---------------------------------------
        $iconPath = 'Ess_M2ePro::images/search_statuses/';
        // ---------------------------------------

        // ---------------------------------------
        $lpId = $row->getData('id');

        $productTitle = $this->getHelper('Data')->escapeHtml($row->getData('name'));
        if (strlen($productTitle) > 60) {
            $productTitle = substr($productTitle, 0, 60) . '...';
        }
        $productTitle = $this->__('Assign ASIN/ISBN For &quot;%product_title%&quot;', $productTitle);
        $productTitle = $this->getHelper('Data')->escapeJs($productTitle);
        // ---------------------------------------

        // ---------------------------------------

        $searchSettingsStatus = $row->getData('search_settings_status');

        // ---------------------------------------
        if ($searchSettingsStatus == \Ess\M2ePro\Model\Amazon\Listing\Product::SEARCH_SETTINGS_STATUS_IN_PROGRESS) {

            $tip = $this->__('Automatic ASIN/ISBN Search in Progress.');
            $iconSrc = $this->getViewFileUrl($iconPath.'processing.gif');

            return <<<HTML
&nbsp;
<a href="javascript: void(0);" title="{$tip}">
    <img src="{$iconSrc}" alt="">
</a>
HTML;
        }
        // ---------------------------------------

        // ---------------------------------------
        $searchSettingsData = $row->getData('search_settings_data');

        $suggestData = array();
        if (!is_null($searchSettingsData)) {
            $searchSettingsData = @json_decode($searchSettingsData,true);
            !empty($searchSettingsData['data']) && $suggestData = $searchSettingsData['data'];

        }
        // ---------------------------------------

        $na = $this->__('N/A');

        if (!empty($suggestData)) {

            $tip = $this->__('Choose ASIN/ISBN from the list');

            return <<<HTML
{$na} &nbsp;
<a href="javascript:;" title="{$tip}" class="amazon-listing-view-icon amazon-listing-view-generalId-search-data"
   onclick="ListingGridHandlerObj.productSearchHandler.openPopUp(1,'{$productTitle}',{$lpId})">
</a>
HTML;
        }

        if ($searchSettingsStatus == \Ess\M2ePro\Model\Amazon\Listing\Product::SEARCH_SETTINGS_STATUS_NOT_FOUND) {

            $tip = $this->__(
                'There were no Products found on Amazon according to the Listing Search Settings.'
            );
            $tip = $this->getHelper('Data')->escapeJs($tip);

            return <<<HTML
{$na} &nbsp;
<a href="javascript: void(0);"
   title="{$tip}"
   class="amazon-listing-view-icon amazon-listing-view-generalId-search-error"
    onclick="ListingGridHandlerObj.productSearchHandler.openPopUp(0,'{$productTitle}',{$lpId});">
</a>
HTML;
        }

        $tip = $this->__('Search for ASIN/ISBN');

        return <<<HTML
{$na} &nbsp;
<a href="javascript:;" title="{$tip}" class="amazon-listing-view-icon amazon-listing-view-generalId-search"
   onclick="ListingGridHandlerObj.productSearchHandler.openPopUp(0,'{$productTitle}',{$lpId});">
</a>
HTML;
    }

    private function getGeneralIdColumnValueNotEmptyGeneralId($row)
    {
        $generalId = $row->getData('general_id');

        $url = $this->getHelper('Component\Amazon')->getItemUrl($generalId, $this->listing->getMarketplaceId());

        $generalIdOwnerHtml = '';
        if ($row->getData('is_general_id_owner') == \Ess\M2ePro\Model\Amazon\Listing\Product::IS_GENERAL_ID_OWNER_YES) {

            $generalIdOwnerHtml = '<br/><span style="font-size: 10px; color: grey;">'.
                                   $this->__('creator of ASIN/ISBN').
                                  '</span>';
        }

        if ((int)$row->getData('amazon_status') != \Ess\M2ePro\Model\Listing\Product::STATUS_NOT_LISTED) {

            return <<<HTML
<a href="{$url}" target="_blank">{$generalId}</a>{$generalIdOwnerHtml}
HTML;
        }

        $generalIdSearchInfo = $row->getData('general_id_search_info');

        if (!empty($generalIdSearchInfo)) {
            $generalIdSearchInfo = @json_decode($generalIdSearchInfo, true);
        }

        if (!empty($generalIdSearchInfo['is_set_automatic'])) {

            $tip = $this->__('ASIN/ISBN was found automatically');

            $text = <<<HTML
<a href="{$url}" target="_blank" title="{$tip}" style="color:#40AADB;">{$generalId}</a>
HTML;

        } else {

            $text = <<<HTML
<a href="{$url}" target="_blank">{$generalId}</a>
HTML;

        }

        // ---------------------------------------
        $hasInActionLock = $this->getLockedData($row);
        $hasInActionLock = $hasInActionLock['in_action'];
        // ---------------------------------------

        if ($hasInActionLock) {
            return $text . $generalIdOwnerHtml;
        }

        $listingProductId = (int)$row->getData('id');

        /** @var \Ess\M2ePro\Model\Listing\Product $listingProduct */
        $listingProduct = $this->amazonFactory->getObjectLoaded('Listing\Product',$listingProductId);
        /** @var \Ess\M2ePro\Model\Amazon\Listing\Product\Variation\Manager $variationManager */
        $variationManager = $listingProduct->getChildObject()->getVariationManager();
        $variationChildStatuses = $row->getData('variation_child_statuses');

        if ($variationManager->isVariationParent() && !empty($variationChildStatuses)) {
            $variationChildStatuses = json_decode($variationChildStatuses, true);
            unset($variationChildStatuses[\Ess\M2ePro\Model\Listing\Product::STATUS_NOT_LISTED]);

            foreach ($variationChildStatuses as $variationChildStatus) {
                if (!empty($variationChildStatus)) {
                    return $text . $generalIdOwnerHtml;
                }
            }
        }

        $tip = $this->__('Unassign ASIN/ISBN');

        $text .= <<<HTML
&nbsp;
<a href="javascript:;"
    class="amazon-listing-view-icon amazon-listing-view-generalId-remove"
    onclick="ListingGridHandlerObj.productSearchHandler.showUnmapFromGeneralIdPrompt({$listingProductId});"
    title="{$tip}">
</a>{$generalIdOwnerHtml}
HTML;

        return $text;
    }

    private function getGeneralIdColumnValueGeneralIdOwner($row)
    {
        $text = $this->__('New ASIN/ISBN');

        // ---------------------------------------
        $hasInActionLock = $this->getLockedData($row);
        $hasInActionLock = $hasInActionLock['in_action'];
        // ---------------------------------------

        if ($hasInActionLock) {
            return $text;
        }

        $tip = $this->__('Unassign ASIN/ISBN');

        $lpId = $row->getData('id');

        $text .= <<<HTML
&nbsp;
<a href="javascript:;"
    class="amazon-listing-view-icon amazon-listing-view-generalId-remove"
    onclick="ListingGridHandlerObj.productSearchHandler.showUnmapFromGeneralIdPrompt({$lpId});"
    title="{$tip}">
</a>
HTML;
        return $text;
    }

    //########################################

    protected function getChildProductsWarningsData()
    {
        if (is_null($this->childProductsWarningsData)) {
            $this->childProductsWarningsData = array();

            $productsIds = array();
            foreach ($this->getCollection()->getData() as $row) {
                $productsIds[] = $row['id'];
            }

            $connection = $this->resourceConnection->getConnection();
            $tableAmazonListingProduct = $this->activeRecordFactory
                ->getObject('Amazon\Listing\Product')->getResource()->getMainTable();

            $select = $connection->select();
            $select->distinct(true);
            $select->from(array('alp' => $tableAmazonListingProduct), array('variation_parent_id'))
                ->where('variation_parent_id IN (?)', $productsIds)
                ->where(
                    'is_variation_product_matched = 0 OR
                    (general_id IS NOT NULL AND is_variation_channel_matched = 0)'
                );

            $this->childProductsWarningsData = $connection->fetchCol($select);
        }

        return $this->childProductsWarningsData;
    }

    protected function hasChildWithWarning($listingProductId)
    {
        return in_array($listingProductId, $this->getChildProductsWarningsData());
    }

    //########################################

    private function convertAndFormatPriceCurrency($price, $currency)
    {
        return $this->priceCurrency->convertAndFormat($price, null, null, null, $currency);
    }

    //########################################
}