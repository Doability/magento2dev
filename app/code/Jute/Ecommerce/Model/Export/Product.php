<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Jute\Ecommerce\Model\Export;
use Magento\ImportExport\Model\Import;
use \Magento\Store\Model\Store;
use \Magento\CatalogImportExport\Model\Import\Product as ImportProduct;

class Product extends \Magento\CatalogImportExport\Model\Export\Product
{


    protected $_exportMainAttrCodes = [
        self::COL_SKU,
        'product_id',
        'name',
        'description',
        'short_description',
        'weight',
        'product_online',
        'tax_class_name',
        'visibility',
        'price',
        'special_price',
        'special_price_from_date',
        'special_price_to_date',
        'url_key',
        'meta_title',
        'meta_keywords',
        'meta_description',
        'base_image',
        'base_image_label',
        'small_image',
        'small_image_label',
        'thumbnail_image',
        'thumbnail_image_label',
        'swatch_image',
        'swatch_image_label',
        'created_at',
        'updated_at',
        'new_from_date',
        'new_to_date',
        'display_product_options_in',
        'map_price',
        'msrp_price',
        'map_enabled',
        'special_price_from_date',
        'special_price_to_date',
        'gift_message_available',
        'custom_design',
        'custom_design_from',
        'custom_design_to',
        'custom_layout_update',
        'page_layout',
        'product_options_container',
        'msrp_price',
        'msrp_display_actual_price_type',
        'map_enabled',
        'country_of_manufacture',
        'map_price',
        'display_product_options_in',
    ];

        /**
     * Set headers columns
     *
     * @param array $customOptionsData
     * @param array $stockItemRows
     * @return void
     */
    protected function setHeaderColumns($customOptionsData, $stockItemRows)
    {
        if (!$this->_headerColumns) {
            $customOptCols = [
                'custom_options',
            ];
            $this->_headerColumns = array_merge(
                [
                    self::COL_SKU,
                    'product_id',
                    self::COL_STORE,
                    self::COL_ATTR_SET,
                    self::COL_TYPE,
                    self::COL_CATEGORY,
                    self::COL_PRODUCT_WEBSITES,
                ],
                $this->_getExportMainAttrCodes(),
                [self::COL_ADDITIONAL_ATTRIBUTES],
                reset($stockItemRows) ? array_keys(end($stockItemRows)) : [],
                [],
                [
                    'related_skus',
                    'crosssell_skus',
                    'upsell_skus',
                ],
                ['additional_images', 'additional_image_labels', 'hide_from_product_page']
            );
            // have we merge custom options columns
            if ($customOptionsData) {
                $this->_headerColumns = array_merge($this->_headerColumns, $customOptCols);
            }
        }
    }
    
    /**
     * @param array $dataRow
     * @param array $multiRawData
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function addMultirowData($dataRow, $multiRawData)
    {
        $result = [];
        $productId = $dataRow['product_id'];
        $storeId = $dataRow['store_id'];
        $sku = $dataRow[self::COL_SKU];

        unset($dataRow['product_id']);
        unset($dataRow['store_id']);
        unset($dataRow[self::COL_SKU]);

        if (Store::DEFAULT_STORE_ID == $storeId) {
            unset($dataRow[self::COL_STORE]);
            $this->updateDataWithCategoryColumns($dataRow, $multiRawData['rowCategories'], $productId);
            if (!empty($multiRawData['rowWebsites'][$productId])) {
                $websiteCodes = [];
                foreach ($multiRawData['rowWebsites'][$productId] as $productWebsite) {
                    $websiteCodes[] = $this->_websiteIdToCode[$productWebsite];
                }
                $dataRow[self::COL_PRODUCT_WEBSITES] =
                    implode(Import::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR, $websiteCodes);
                $multiRawData['rowWebsites'][$productId] = [];
            }
            if (!empty($multiRawData['mediaGalery'][$productId])) {
                $additionalImages = [];
                $additionalImageLabels = [];
                $additionalImageIsDisabled = [];
                foreach ($multiRawData['mediaGalery'][$productId] as $mediaItem) {
                    $additionalImages[] = $mediaItem['_media_image'];
                    $additionalImageLabels[] = $mediaItem['_media_label'];

                    if ($mediaItem['_media_is_disabled'] == true) {
                        $additionalImageIsDisabled[] = $mediaItem['_media_image'];
                    }
                }
                $dataRow['additional_images'] =
                    implode(Import::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR, $additionalImages);
                $dataRow['additional_image_labels'] =
                    implode(Import::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR, $additionalImageLabels);
                $dataRow['hide_from_product_page'] =
                    implode(Import::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR, $additionalImageIsDisabled);
                $multiRawData['mediaGalery'][$productId] = [];
            }
            foreach ($this->_linkTypeProvider->getLinkTypes() as $linkTypeName => $linkId) {
                if (!empty($multiRawData['linksRows'][$productId][$linkId])) {
                    $colPrefix = $linkTypeName . '_';

                    $associations = [];
                    foreach ($multiRawData['linksRows'][$productId][$linkId] as $linkData) {
                        if ($linkData['default_qty'] !== null) {
                            $skuItem = $linkData['sku'] . ImportProduct::PAIR_NAME_VALUE_SEPARATOR .
                                $linkData['default_qty'];
                        } else {
                            $skuItem = $linkData['sku'];
                        }
                        $associations[$skuItem] = $linkData['position'];
                    }
                    $multiRawData['linksRows'][$productId][$linkId] = [];
                    asort($associations);
                    $dataRow[$colPrefix . 'skus'] =
                        implode(Import::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR, array_keys($associations));
                }
            }
            $dataRow = $this->rowCustomizer->addData($dataRow, $productId);

        }

        if (!empty($this->collectedMultiselectsData[$storeId][$productId])) {
            foreach (array_keys($this->collectedMultiselectsData[$storeId][$productId]) as $attrKey) {
                if (!empty($this->collectedMultiselectsData[$storeId][$productId][$attrKey])) {
                    $dataRow[$attrKey] = implode(
                        Import::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR,
                        $this->collectedMultiselectsData[$storeId][$productId][$attrKey]
                    );
                }
            }
        }

        if (!empty($multiRawData['customOptionsData'][$productId][$storeId])) {
            $customOptionsRows = $multiRawData['customOptionsData'][$productId][$storeId];
            $multiRawData['customOptionsData'][$productId][$storeId] = [];
            $customOptions = implode(ImportProduct::PSEUDO_MULTI_LINE_SEPARATOR, $customOptionsRows);

            $dataRow = array_merge($dataRow, ['custom_options' => $customOptions]);
        }

        if (empty($dataRow)) {
            return $result;
        } elseif ($storeId != Store::DEFAULT_STORE_ID) {
            $dataRow[self::COL_STORE] = $this->_storeIdToCode[$storeId];
            if (isset($productData[Store::DEFAULT_STORE_ID][self::COL_VISIBILITY])) {
                $dataRow[self::COL_VISIBILITY] = $productData[Store::DEFAULT_STORE_ID][self::COL_VISIBILITY];
            }
        }
        $dataRow[self::COL_SKU] = $sku;
        $dataRow['product_id'] = $productId;        
        $result[] = $dataRow;

        return $result;
    }

}
