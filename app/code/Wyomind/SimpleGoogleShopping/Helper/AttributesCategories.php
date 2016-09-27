<?php

/**
 * Copyright © 2015 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\SimpleGoogleShopping\Helper;

/**
 * Attributes management
 */
class AttributesCategories extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * {g_google_product_category} attribute processing
     * @param \Wyomind\Simplegoogleshopping\Model\Feeds $model
     * @param array                                     $options
     * @param \Magento\Catalog\Model\Product            $product
     * @param string                                    $reference
     * @return string g:google_product_category xml tags
     */
    public function googleProductCategory(
        $model,
        $options,
        $product,
        $reference
    ) {
        $item = $model->checkReference($reference, $product);
        if ($item == null) {
            return "";
        }
        $values = [];
        $type = (!isset($options["type"])) ? "longest" : $options["type"];

        foreach ($item->getCategoryIds() as $key => $category) {
            if (isset($model->categoriesMapping[$category])) {
                $values[] = $model->categoriesMapping[$category];
            }
        }
        usort($values, ["\Wyomind\SimpleGoogleShopping\Helper\Attributes", "cmp"]);

        if ($type == "shortest") {
            $values = array_reverse($values);
        }
        $googleProductCategory = array_shift($values);
        $value = "";
        if ($googleProductCategory != "") {
            $value = $googleProductCategory;
        }
        return $value;
    }

    /**
     * {categories} attribute processing
     * @param \Wyomind\Simplegoogleshopping\Model\Feeds $model
     * @param array                                     $options
     * @param \Magento\Catalog\Model\Product            $product
     * @param string                                    $reference
     * @return string formatted version of the product categories
     */
    public function categories(
        $model,
        $options,
        $product,
        $reference
    ) {

        $item = $model->checkReference($reference, $product);
        if ($item == null) {
            return "";
        }

        $index = "";
        $maxNumber = INF;
        $fromLevel = 1;
        $maxLength = INF;
        if (isset($options["index"])) {
            $index = $options["index"];
            $maxNumber = 1;
        } else {
            $maxNumber = (!isset($options["nb_path"]) || !$options["nb_path"] || $options["nb_path"] == "INF") ? INF : $options["nb_path"];
            $fromLevel = (!isset($options["from_level"])) ? 1 : $options["from_level"];
            $maxLength = (!isset($options["nb_cat_in_each_path"]) || !$options["nb_cat_in_each_path"] || $options["nb_cat_in_each_path"] == "INF") ? INF : $options["nb_cat_in_each_path"];
        }
        $separator = (!isset($options["path_separator"])) ? ", " : $options["path_separator"];
        $children = (!isset($options["cat_separator"])) ? " > " : $options["cat_separator"];

        $path = 0;
        $categorieList = [];

        foreach ($item->getCategoryIds() as $key => $category) {
            $isIncategoryFilter = $model->params["simplegoogleshopping_category_filter"] && isset($model->categories[$category]) && isset($model->categories[$category]["path"]);

            if (isset($model->categories[$category]) && $model->categories[$category]["include_in_menu"] && ($isIncategoryFilter || $model->categoriesFilterList[0] == "*")) {
                $path++;
                $categorieList[$path] = [];

                $pathIds = explode("/", $model->categories[$category]["path"]);
                if (in_array($model->rootCategory, $pathIds)) {
                    foreach ($pathIds as $pathId) {
                        if (isset($model->categories[$pathId]) && $model->categories[$pathId]["name"] != null) {
                            $categorieList[$path][] = ($model->categories[$pathId]["name"]);
                        }
                    }
                }
            }
        }
        $categoriesToArray = [];
        usort($categorieList, ["\Wyomind\SimpleGoogleShopping\Helper\Attributes", "cmpArray"]);
        if ($index == "longest") {
            $categorieList = array_reverse($categorieList);
        }
        $item->setCategoriesArray($categorieList);


        if (is_numeric($index)) {
            if (isset($categorieList[$index])) {
                $categorieList = [$categorieList[$index]];
            } else {
                return "";
            }
        }

        $mn = 0;
        foreach ($categorieList as $key => $c) {
            if ($mn < $maxNumber) {
                if (!isset($categoriesToArray[$mn])) {
                    $categoriesToArray[$mn] = [];
                }
                foreach ($c as $skey => $sc) {
                    if ($skey >= $fromLevel && $skey <= $maxLength) {
                        $categoriesToArray[$mn][] .= $sc;
                    }
                }
                if (count($categoriesToArray[$mn])) {
                    $mn++;
                }
            }
        }

        $value = null;

        foreach (array_values($categoriesToArray) as $key => $cat) {
            if ($key > 0) {
                $value.=$separator;
            }
            $value.=implode($children, $cat);
        }
        return $value;
    }

    /**
     * {category_mapping} attribute processing
     * @param \Wyomind\Simplegoogleshopping\Model\Feeds $model
     * @param array                                     $options
     * @param \Magento\Catalog\Model\Product            $product
     * @param string                                    $reference
     * @return string mapped category
     */
    public function categoryMapping(
        $model,
        $options,
        $product,
        $reference
    ) {
        $item = $model->checkReference($reference, $product);
        if ($item == null) {
            return "";
        }
        $num = (isset($options["index"])) ? $options["index"] : 0;
        $value = "";
        $n = 0;
        foreach ($item->getCategoryIds() as $key => $category) {
            if (isset($model->categoriesMapping[$category])) {
                if ($n == $num) {
                    $value.=$model->categoriesMapping[$category];
                    break;
                }
                $n++;
            }
        }
        return $value;
    }
}
