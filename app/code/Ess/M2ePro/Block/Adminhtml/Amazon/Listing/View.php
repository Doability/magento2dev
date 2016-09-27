<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

namespace Ess\M2ePro\Block\Adminhtml\Amazon\Listing;

class View extends \Ess\M2ePro\Block\Adminhtml\Magento\Grid\AbstractContainer
{
    const VIEW_MODE_AMAZON   = 'amazon';
    const VIEW_MODE_MAGENTO  = 'magento';
    const VIEW_MODE_SELLERCENTRAL = 'sellercentral';
    const VIEW_MODE_SETTINGS = 'settings';

    const DEFAULT_VIEW_MODE = self::VIEW_MODE_AMAZON;
    
    /** @var  \Ess\M2ePro\Model\Listing */
    protected $listing;

    //########################################

    public function _construct()
    {
        parent::_construct();

        $this->listing = $this->getHelper('Data\GlobalData')->getValue('view_listing');

        // Initialization block
        // ---------------------------------------
        $this->setId('amazonListingView');
        $this->_controller = 'adminhtml_amazon_listing_view_' . $this->getViewMode();
        // ---------------------------------------

        // Set buttons actions
        // ---------------------------------------
        $this->removeButton('add');
        // ---------------------------------------
    }

    protected function _prepareLayout()
    {
        $this->css->addFile('listing/autoAction.css');
        $this->css->addFile('amazon/style.css');
        $this->css->addFile('amazon/listing/view.css');
        $this->css->addFile('amazon/listing/product/variation/grid.css');

        if (!$this->getRequest()->isXmlHttpRequest()) {

            $this->appendHelpBlock([
                'content' => $this->__(
                    '<p>M2E Pro Listing is a group of Magento Products sold on a certain Marketplace from a 
                    particular Account. M2E Pro has several options to display the content of Listings 
                    referring to different data details. Each of the view options contains a unique set of 
                    available Actions accessible in the Mass Actions drop-down.</p><br>
                    <p>More detailed information you can find <a href="%url%" target="_blank">here</a>.</p>',
                    $this->getHelper('Module\Support')->getDocumentationUrl(NULL, NULL, 'x/eQItAQ')
                )
            ]);
            
            $this->setPageActionsBlock(
                'Amazon\Listing\View\Switcher',
                'amazon_listing_view_switcher'
            );

            $this->getLayout()->getBlock('amazon_listing_view_switcher')->addData([
                'current_view_mode' => $this->getViewMode()
            ]);
        }

        // ---------------------------------------
        $this->addButton('back', array(
            'label'   => $this->__('Back'),
            'onclick' => 'setLocation(\''.$this->getUrl('*/amazon_listing/index') . '\');',
            'class'   => 'back'
        ));
        // ---------------------------------------

        // ---------------------------------------
        $this->addButton('view_logs', array(
            'label'   => $this->__('View Log'),
            'onclick' => 'window.open(\''.$this->getUrl('*/amazon_listing_log/index', [
                'id' => $this->listing->getId()
            ]) . '\');',
            'class'   => '',
        ));
        // ---------------------------------------

        // ---------------------------------------
        $this->addButton('edit_settings', array(
            'label'   => $this->__('Edit Settings'),
            'onclick' => '',
            'class'   => 'drop_down edit_default_settings_drop_down primary',
            'class_name' => 'Ess\M2ePro\Block\Adminhtml\Magento\Button\DropDown',
            'options' => $this->getSettingsButtonDropDownItems()
        ));
        // ---------------------------------------

        // ---------------------------------------
        $this->addButton('add_products', array(
            'id'        => 'add_products',
            'label'     => $this->__('Add Products'),
            'class'     => 'add',
            'button_class' => '',
            'class_name' => 'Ess\M2ePro\Block\Adminhtml\Magento\Button\DropDown',
            'options' => $this->getAddProductsDropDownItems(),
        ));
        // ---------------------------------------

        return parent::_prepareLayout();
    }

    //########################################

    public function getViewMode()
    {
        $allowedModes = array(
            self::VIEW_MODE_AMAZON,
            self::VIEW_MODE_SETTINGS,
            self::VIEW_MODE_MAGENTO,
            self::VIEW_MODE_SELLERCENTRAL
        );
        $mode = $this->getParam('view_mode', self::DEFAULT_VIEW_MODE);

        if (in_array($mode, $allowedModes)) {
            return $mode;
        }

        return self::DEFAULT_VIEW_MODE;
    }

    protected function getParam($paramName, $default = NULL)
    {
        $session = $this->getHelper('Data\Session');
        $sessionParamName = $this->getId() . $this->listing->getId() . $paramName;

        if ($this->getRequest()->has($paramName)) {
            $param = $this->getRequest()->getParam($paramName);
            $session->setValue($sessionParamName, $param);
            return $param;
        } elseif ($param = $session->getValue($sessionParamName)) {
            return $param;
        }

        return $default;
    }

    //########################################

    // TODO NOT SUPPORTED FEATURES "Listing header selector"
//    public function getHeaderHtml()
//    {
//        $this->listing = $this->getHelper('Data\GlobalData')->getValue('temp_data');
//
//        // ---------------------------------------
//        $collection = Mage::getModel('M2ePro/Listing')->getCollection();
//        $collection->addFieldToFilter('component_mode', \Ess\M2ePro\Helper\Component\Amazon::NICK);
//        $collection->addFieldToFilter('id', array('neq' => $this->listing['id']));
//        $collection->setPageSize(200);
//        $collection->setOrder('title', 'ASC');
//
//        $items = array();
//        foreach ($collection->getItems() as $item) {
//            $items[] = array(
//                'label' => $item->getTitle(),
//                'url' => $this->getUrl('*/*/view', array('id' => $item->getId()))
//            );
//        }
//        // ---------------------------------------
//
//        if (count($items) == 0) {
//            return parent::getHeaderHtml();
//        }
//
//        // ---------------------------------------
//        $data = array(
//            'target_css_class' => 'listing-profile-title',
//            'style' => 'max-height: 120px; overflow: auto; width: 200px;',
//            'items' => $items
//        );
//        $dropDownBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_widget_button_dropDown');
//        $dropDownBlock->setData($data);
//        // ---------------------------------------
//
//        return parent::getHeaderHtml() . $dropDownBlock->toHtml();
//    }

// TODO NOT SUPPORTED FEATURES "Listing header selector"
//    public function getHeaderText()
//    {
//        // ---------------------------------------
//        $changeProfile = $this->__('Change Listing');
//        $headerText = parent::getHeaderText();
//        $this->listing = $this->getHelper('Data\GlobalData')->getValue('temp_data');
//        $listingTitle = $this->getHelper('Data')->escapeHtml($this->listing['title']);
//        // ---------------------------------------
//
//        return <<<HTML
//{$headerText} <a href="javascript: void(0);"
//   id="listing-profile-title"
//   class="listing-profile-title"
//   style="font-weight: bold;"
//   title="{$changeProfile}"><span class="drop_down_header">"{$listingTitle}"</span></a>
//HTML;
//    }

    //########################################

    protected function _toHtml()
    {
        return '<div id="listing_view_progress_bar"></div>' .
            '<div id="listing_container_errors_summary" class="errors_summary" style="display: none;"></div>' .
            '<div id="listing_view_content_container">' .
            parent::_toHtml() .
            '</div>';
    }

    //########################################

    public function getGridHtml()
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            return parent::getGridHtml();
        }

        $this->jsPhp->addConstants(
            $this->getHelper('Data')->getClassConstants('\Ess\M2ePro\Model\Listing')
        );

        $showAutoAction = json_encode((bool)$this->getRequest()->getParam('auto_actions'));

        // ---------------------------------------
        $this->jsUrl->addUrls($this->getHelper('Data')->getControllerActions(
            'Amazon\Listing\AutoAction', array('id' => $this->getRequest()->getParam('id'))
        ));

        $path = 'amazon_listing_product_log/index';
        $this->jsUrl->add($this->getUrl('*/' . $path, array(
            'back' => $this->getHelper('Data')->makeBackUrlParam('*/amazon_listing/view',array(
                'id' => $this->listing['id']
            ))
        )), $path);

        $path = 'amazon_listing/duplicateProducts';
        $this->jsUrl->add($this->getUrl('*/' . $path), $path);

        $this->jsUrl->add($this->getUrl('*/amazon_listing_log/index', array(
            'id' => $this->listing['id'],
            'back' => $this->getHelper('Data')->makeBackUrlParam('*/amazon_listing/view', ['id' =>$this->listing['id']])
        )), 'logViewUrl');

        $this->jsUrl->add($this->getUrl('*/listing/getErrorsSummary'), 'getErrorsSummary');

        $this->jsUrl->addUrls($this->getHelper('Data')->getControllerActions('Amazon\Listing'));

        $this->jsUrl->addUrls([
            'runListProducts' => $this->getUrl('*/amazon_listing/runListProducts'),
            'runRelistProducts' => $this->getUrl('*/amazon_listing/runRelistProducts'),
            'runReviseProducts' => $this->getUrl('*/amazon_listing/runReviseProducts'),
            'runStopProducts' => $this->getUrl('*/amazon_listing/runStopProducts'),
            'runStopAndRemoveProducts' => $this->getUrl('*/amazon_listing/runStopAndRemoveProducts'),
            'runDeleteAndRemoveProducts' => $this->getUrl('*/amazon_listing/runDeleteAndRemoveProducts'),
            'runRemoveProducts' => $this->getUrl('*/amazon_listing/runRemoveProducts')
        ]);

        $this->jsUrl->addUrls($this->getHelper('Data')->getControllerActions('Amazon\Listing\Product'));
        $this->jsUrl->addUrls($this->getHelper('Data')->getControllerActions('Amazon\Listing\Product\Fulfillment'));
        $this->jsUrl->addUrls($this->getHelper('Data')->getControllerActions('Amazon\Listing\Product\Search'));
        $this->jsUrl->addUrls(
            $this->getHelper('Data')->getControllerActions('Amazon\Listing\Product\Template\Description')
        );
        $this->jsUrl->addUrls($this->getHelper('Data')->getControllerActions('Amazon\Listing\Product\Variation'));
        $this->jsUrl->addUrls(
            $this->getHelper('Data')->getControllerActions('Amazon\Listing\Product\Variation\Manage')
        );
        $this->jsUrl->addUrls(
            $this->getHelper('Data')->getControllerActions('Amazon\Listing\Product\Variation\Individual')
        );

        $this->jsUrl->add(
            $this->getUrl('*/amazon_listing_view_settings_moving/moveToListingGrid',
                ['listing_view' => true]
            ), 'moveToListingGridHtml'
        );
        $this->jsUrl->add($this->getUrl('*/listing_moving/prepareMoveToListing'), 'prepareData');
        $this->jsUrl->add($this->getUrl('*/listing_moving/getFailedProductsGrid'), 'getFailedProductsGridHtml');
        $this->jsUrl->add($this->getUrl('*/listing_moving/tryToMoveToListing'), 'tryToMoveToListing');
        $this->jsUrl->add($this->getUrl('*/listing_moving/moveToListing'), 'moveToListing');

        $this->jsUrl->add($this->getUrl('*/amazon_marketplace/index'), 'marketplaceSynchUrl');

        $this->jsUrl->add($this->getUrl('*/listing/saveListingAdditionalData', [
            'id' => $this->listing['id']
        ]), 'saveListingAdditionalData');

//        TODO
//        $this->jsUrl->addUrls($this->getHelper('Data')->getControllerActions(
//            'amazon_listing_repricing',
//            array(
//                'id' => $this->listing['id'],
//                'account_id' => $this->listing['account_id']
//            )
//        ));

        // ---------------------------------------

        $component = \Ess\M2ePro\Helper\Component\Amazon::NICK;
        $gridId = $this->getChildBlock('grid')->getId();
        $ignoreListings = json_encode(array($this->listing['id']));
        $marketplace = json_encode(array_merge(
            $this->listing->getMarketplace()->getData(),
            $this->listing->getMarketplace()->getChildObject()->getData()
        ));
        $isNewAsinAvailable = json_encode($this->listing->getMarketplace()->getChildObject()->isNewAsinAvailable());

        $temp = $this->getHelper('Data\Session')->getValue('products_ids_for_list', true);
        $productsIdsForList = empty($temp) ? '' : $temp;

//        TODO
//        $getUpdatedRepricingPriceBySkus = $this->getUrl(
//            '*/amazon_listing_repricing/getUpdatedPriceBySkus'
//        );

        $templateDescriptionPopupTitle = $this->__('Assign Description Policy');

        $popupTitle = $this->__('Moving Amazon Items');
        $popupTitleSingle = $this->__('Moving Amazon Item');
        $failedProductsPopupTitle = $this->__('Products failed to move');

        $taskCompletedMessage = $this->__('Task completed. Please wait ...');
        $taskCompletedSuccessMessage = $this->__('"%task_title%" Task has successfully submitted to be processed.');
        $taskCompletedWarningMessage = $this->__(
            '"%task_title%" Task has completed with warnings. <a target="_blank" href="%url%">View Log</a> for details.'
        );
        $taskCompletedErrorMessage = $this->__(
            '"%task_title%" Task has completed with errors. <a target="_blank" href="%url%">View Log</a> for details.'
        );

        $lockedObjNoticeMessage = $this->__('Some Amazon request(s) are being processed now.');
        $sendingDataToAmazonMessage = $this->__('Sending %product_title% Product(s) data on Amazon.');
        $viewAllProductLogMessage = $this->__('View Full Product Log');

        $listingLockedMessage = $this->__('The Listing was locked by another process. Please try again later.');
        $listingEmptyMessage = $this->__('Listing is empty.');

        $listingAllItemsMessage = $this->__('Listing All Items On Amazon');
        $listingSelectedItemsMessage = $this->__('Listing Selected Items On Amazon');
        $revisingSelectedItemsMessage = $this->__('Revising Selected Items On Amazon');
        $relistingSelectedItemsMessage = $this->__('Relisting Selected Items On Amazon');
        $stoppingSelectedItemsMessage = $this->__('Stopping Selected Items On Amazon');
        $stoppingAndRemovingSelectedItemsMessage = $this->__(
            'Stopping On Amazon And Removing From Listing Selected Items'
        );
        $deletingAndRemovingSelectedItemsMessage = $this->__('Removing From Amazon And Listing Selected Items');
        $removingSelectedItemsMessage = $this->__('Removing From Listing Selected Items');

        $successfullyMovedMessage = $this->__('Product(s) was successfully Moved.');
        $productsWereNotMovedMessage = $this->__(
            'Product(s) was not Moved. <a target="_blank" href="%url%">View Log</a> for details.'
        );
        $someProductsWereNotMovedMessage = $this->__(
            'Some Product(s) was not Moved. <a target="_blank" href="%url%">View Log</a> for details.'
        );

        $selectItemsMessage = $this->__('Please select the Products you want to perform the Action on.');
        $selectActionMessage = $this->__('Please select Action.');

        $assignString = $this->__('Assign');

        $templateShippingOverridePopupTitle = $this->__('Assign Shipping Override Policy');

        $enterProductSearchQueryMessage = $this->__('Please enter Product Title or ASIN/ISBN/UPC/EAN.');
        $autoMapAsinSearchProducts = $this->__('Search %product_title% Product(s) on Amazon.');
        $autoMapAsinProgressTitle = $this->__('Automatic Assigning ASIN/ISBN to Item(s)');
        $autoMapAsinErrorMessage = $this->__('Server is currently unavailable. Please try again later.');
        $newAsinNotAvailable = $this->__(
            'The new ASIN/ISBN creation functionality is not available in %code% Marketplace yet.'
        );
        $notSynchronizedMarketplace = $this->__(
            'In order to use New ASIN/ISBN functionality, please re-synchronize Marketplace data.') . ' ' .
            $this->__('Press "Save And Update" Button after redirect on Marketplace Page.');

        $noVariationsLeftText = $this->__('All variations are already added.');

        $notSet = $this->__('Not Set');
        $setAttributes = $this->__('Set Attributes');
        $variationManageMatchedAttributesError = $this->__('Please choose valid Attributes.');
        $variationManageMatchedAttributesErrorDuplicateSelection =
            $this->__('You can not choose the same Attribute twice.');

        $variationManageSkuPopUpTitle =
            $this->__('Enter Amazon Parent Product SKU');

        $switchToIndividualModePopUpTitle = $this->__('Change "Manage Variations" Mode');
        $switchToParentModePopUpTitle = $this->__('Change "Manage Variations" Mode');

        $emptySkuError = $this->__('Please enter Amazon Parent Product SKU.');

        $this->jsTranslator->addTranslations([
            'Remove Category' => $this->__('Remove Category'),
            'Add New Group' => $this->__('Add New Group'),
            'Add/Edit Categories Rule' => $this->__('Add/Edit Categories Rule'),
            'Auto Add/Remove Rules' => $this->__('Auto Add/Remove Rules'),
            'Based on Magento Categories' => $this->__('Based on Magento Categories'),
            'You must select at least 1 Category.' => $this->__('You must select at least 1 Category.'),
            'Rule with the same Title already exists.' => $this->__('Rule with the same Title already exists.'),

            'Clear Search Results' => $this->__('Clear Search Results'),

            'popup_title' => $popupTitle,
            'popup_title_single' => $popupTitleSingle,
            'failed_products_popup_title' => $failedProductsPopupTitle,

            'task_completed_message' => $taskCompletedMessage,
            'task_completed_success_message' => $taskCompletedSuccessMessage,
            'task_completed_warning_message' => $taskCompletedWarningMessage,
            'task_completed_error_message' => $taskCompletedErrorMessage,

            'locked_obj_notice' => $lockedObjNoticeMessage,
            'sending_data_message' => $sendingDataToAmazonMessage,
            'view_all_product_log_message' => $viewAllProductLogMessage,

            'listing_locked_message' => $listingLockedMessage,
            'listing_empty_message' => $listingEmptyMessage,

            'listing_all_items_message' => $listingAllItemsMessage,
            'listing_selected_items_message' => $listingSelectedItemsMessage,
            'revising_selected_items_message' => $revisingSelectedItemsMessage,
            'relisting_selected_items_message' => $relistingSelectedItemsMessage,
            'stopping_selected_items_message' => $stoppingSelectedItemsMessage,
            'stopping_and_removing_selected_items_message' => $stoppingAndRemovingSelectedItemsMessage,
            'deleting_and_removing_selected_items_message' => $deletingAndRemovingSelectedItemsMessage,
            'removing_selected_items_message' => $removingSelectedItemsMessage,

            'successfully_moved' => $successfullyMovedMessage,
            'products_were_not_moved' => $productsWereNotMovedMessage,
            'some_products_were_not_moved' => $someProductsWereNotMovedMessage,

            'select_items_message' => $selectItemsMessage,
            'select_action_message' => $selectActionMessage,

            'templateDescriptionPopupTitle' => $templateDescriptionPopupTitle,

            'templateShippingOverridePopupTitle' => $templateShippingOverridePopupTitle,

            'assign' => $assignString,

            'enter_productSearch_query' => $enterProductSearchQueryMessage,
            'automap_asin_search_products' => $autoMapAsinSearchProducts,
            'automap_asin_progress_title' => $autoMapAsinProgressTitle,
            'automap_error_message' => $autoMapAsinErrorMessage,

            'new_asin_not_available' => $newAsinNotAvailable,
            'not_synchronized_marketplace' => $notSynchronizedMarketplace,

            'no_variations_left' => $noVariationsLeftText,

            'not_set' => $notSet,
            'set_attributes' => $setAttributes,
            'variation_manage_matched_attributes_error' => $variationManageMatchedAttributesError,
            'variation_manage_matched_attributes_error_duplicate' =>
                $variationManageMatchedAttributesErrorDuplicateSelection,

            'error_changing_product_options' => $this->__('Please Select Product Options.'),

            'variation_manage_matched_sku_popup_title' => $variationManageSkuPopUpTitle,
            'empty_sku_error' => $emptySkuError,

            'switch_to_individual_mode_popup_title' => $switchToIndividualModePopUpTitle,
            'switch_to_parent_mode_popup_title' => $switchToParentModePopUpTitle,

            'Add New Description Policy' => $this->__('Add New Description Policy'),
            'Add New Child Product' => $this->__('Add New Child Product')
        ]);

        $this->js->addOnReadyJs(
<<<JS
    require([
        'M2ePro/Amazon/Listing/View/Grid',
        'M2ePro/Amazon/Listing/AfnQty',
        'M2ePro/Amazon/Listing/AutoAction',
        'M2ePro/Amazon/Listing/Product/Variation'
    ], function(){

        M2ePro.productsIdsForList = '{$productsIdsForList}';
    
        M2ePro.customData.componentMode = '{$component}';
        M2ePro.customData.gridId = '{$gridId}';
        M2ePro.customData.ignoreListings = '{$ignoreListings}';
    
        M2ePro.customData.marketplace = {$marketplace};
        M2ePro.customData.isNewAsinAvailable = {$isNewAsinAvailable};
        
        ListingGridHandlerObj = new AmazonListingViewGrid(
            '{$gridId}',
            {$this->listing['id']}
        );
        ListingGridHandlerObj.afterInitPage();
        
        ListingGridHandlerObj.movingHandler.setOptions(M2ePro);
           
        ListingGridHandlerObj.actionHandler.setProgressBar('listing_view_progress_bar');
        ListingGridHandlerObj.actionHandler.setGridWrapper('listing_view_content_container');
            
        AmazonListingProductVariationObj = new AmazonListingProductVariation(ListingGridHandlerObj);

        if (M2ePro.productsIdsForList) {
            ListingGridHandlerObj.getGridMassActionObj().checkedString = M2ePro.productsIdsForList;
            ListingGridHandlerObj.actionHandler.listAction();
        }
    
        window.ListingAutoActionObj = new AmazonListingAutoAction();
        if ({$showAutoAction}) {
            ListingAutoActionObj.loadAutoActionHtml();
        }
    
        AmazonListingAfnQtyObj = new AmazonListingAfnQty();
        // TODO
        // CommonAmazonListingRepricingPriceHandlerObj = new CommonAmazonListingRepricingPriceHandler();
    });
JS
        );

        $productSearchBlock = $this->createBlock('Amazon\Listing\Product\Search\Main');

        // TODO NOT SUPPORTED FEATURES "Listing header selector"
//        // ---------------------------------------
//        $listingSwitcher = $this->getLayout()->createBlock(
//            'M2ePro/adminhtml_common_amazon_listing_view_listingSwitcher'
//        );
//        // ---------------------------------------

        // ---------------------------------------
        $viewHeaderBlock = $this->createBlock('Listing\View\Header','', [
            'data' => ['listing' => $this->listing]
        ]);
        // ---------------------------------------

//        // ---------------------------------------
//        $switchToIndividualPopup = $this->getLayout()->createBlock(
//            'M2ePro/adminhtml_common_amazon_listing_variation_product_switchToIndividualPopup');
//        // ---------------------------------------
//
//        // ---------------------------------------
//        $switchToParentPopup = $this->getLayout()->createBlock(
//            'M2ePro/adminhtml_common_amazon_listing_variation_product_switchToParentPopup');
//        // ---------------------------------------

        return $viewHeaderBlock->toHtml()
//            . $listingSwitcher->toHtml()
            . $productSearchBlock->toHtml()
//            . $switchToIndividualPopup->toHtml()
//            . $switchToParentPopup->toHtml()
            . parent::getGridHtml();
    }

    protected function getSettingsButtonDropDownItems()
    {
        $items = [];

        $backUrl = $this->getHelper('Data')->makeBackUrlParam('*/amazon_listing/view', [
            'id' => $this->listing['id']
        ]);

        // ---------------------------------------
        $url = $this->getUrl('*/amazon_listing/edit', [
            'id' => $this->listing['id'],
            'back' => $backUrl,
            'tab' => 'selling'
        ]);
        $items[] = [
            'label' => $this->__('Selling'),
            'onclick' => 'window.open(\'' . $url . '\',\'_blank\');',
            'default' => true
        ];
        // ---------------------------------------

        // ---------------------------------------
        $url = $this->getUrl('*/amazon_listing/edit', [
            'id' => $this->listing['id'],
            'back' => $backUrl,
            'tab' => 'search'
        ]);
        $items[] = [
            'label' => $this->__('Search'),
            'onclick' => 'window.open(\'' . $url . '\',\'_blank\');'
        ];
        // ---------------------------------------

        // ---------------------------------------
        $items[] = [
            'onclick' => 'ListingAutoActionObj.loadAutoActionHtml();',
            'label' => $this->__('Auto Add/Remove Rules')
        ];
        // ---------------------------------------

        return $items;
    }

    //########################################

    public function getAddProductsDropDownItems()
    {
        $items = [];

        $backUrl = $this->getHelper('Data')->makeBackUrlParam('*/amazon_listing/view', [
            'id' => $this->listing['id']
        ]);

        // ---------------------------------------
        $url = $this->getUrl('*/amazon_listing_product_add/index', [
            'id' => $this->listing['id'],
            'back' => $backUrl,
            'component' => \Ess\M2ePro\Helper\Component\Amazon::NICK,
            'clear' => 1,
            'step' => 2,
            'source' => \Ess\M2ePro\Block\Adminhtml\Amazon\Listing\Product\Add\SourceMode::MODE_PRODUCT
        ]);
        $items[] = [
            'label' => $this->__('From Products List'),
            'onclick' => "setLocation('" . $url . "')",
            'default' => true
        ];
        // ---------------------------------------

        // ---------------------------------------
        $url = $this->getUrl('*/amazon_listing_product_add/index', [
            'id' => $this->listing['id'],
            'back' => $backUrl,
            'component' => \Ess\M2ePro\Helper\Component\Amazon::NICK,
            'clear' => 1,
            'step' => 2,
            'source' => \Ess\M2ePro\Block\Adminhtml\Amazon\Listing\Product\Add\SourceMode::MODE_CATEGORY
        ]);
        $items[] = [
            'label' => $this->__('From Categories'),
            'onclick' => "setLocation('" . $url . "')"
        ];
        // ---------------------------------------

        return $items;
    }

    //########################################
}