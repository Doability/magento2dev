<?php

/**
 * Copyright © 2015 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\SimpleGoogleShopping\Helper;

/**
 * Simple Google Shopping template parser
 */
class Parser extends \Magento\Framework\App\Helper\AbstractHelper
{

    protected $_regexAttributeCalls = "/(?<attribute_calls>{{.*}})/xsU";
    protected $_regexInsideAttributeCalls = "
        /(?<pattern>
            \s*
            (?<object>[a-z]+)
            \.
            (?<property>[a-z0-9_]+)
            \s?
            (?:
                \s*
                (?<parameters>[^\|}]*)
            )?
            \s*
            (?<or>\|)?
        )/sx";
    protected $_regexParameters = '/(?<name>\b\w+\b)\s*=\s*(?<value>"[^"]*"|\'[^\']*\'|[^"\'<>\s]+)/';

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context
    ) {
        parent::__construct($context);

        $priceAttributesOptions = [
            'currency', 'vat_rate'
        ];

        $this->attributeOptions = [
            'price' => $priceAttributesOptions,
            'normal_price' => $priceAttributesOptions,
            'final_price' => $priceAttributesOptions,
            'special_price' => $priceAttributesOptions,
            'price_rules' => $priceAttributesOptions,
            'is_special_price' => ['yes', 'no'],
            'min_price' => $priceAttributesOptions,
            'max_price' => $priceAttributesOptions,
            'image' => ['index'],
            'qty' => ['float'],
            'is_in_stock' => ['in_stock', 'out_of_stock', 'backorderable'],
            'categories' => ['nb_path', 'from_level', 'nb_cat_in_each_path', 'path_separator', 'cat_separator'],
            'category_mapping' => ['index'],
        ];
    }

    /**
     * @param string $template
     * @return array
     */
    public function extractAttributeCalls($template)
    {
        $result = [];
        $matches = [];
        // first step : get all occurrences of {{....}}
        preg_match_all($this->_regexAttributeCalls, $template, $matches);
        foreach ($matches['attribute_calls'] as $attributeCall) {
            $matchesTwo = [];
            // second step : parse the content of {{....}}
            preg_match_all($this->_regexInsideAttributeCalls, $attributeCall, $matchesTwo);
            $objects = $matchesTwo['object'];
            $properties = $matchesTwo['property'];
            $parameters = $matchesTwo['parameters'];
            $ors = $matchesTwo['or'];
            $i = 0;
            $result[$attributeCall] = [];
            foreach ($objects as $object) {
                $tmp = [];
                $tmp['object'] = $object;
                $tmp['property'] = $properties[$i];
                $parametersTmp = trim($parameters[$i]);
                if ($parametersTmp != "") {
                    $matchesThree = [];
                    // third step : parse the parameters value="xxx"
                    preg_match_all($this->_regexParameters, $parametersTmp, $matchesThree);
                    $names = $matchesThree['name'];
                    $values = $matchesThree['value'];
                    $j = 0;
                    foreach ($names as $name) {
                        $tmp['parameters'][$name] = trim($values[$j], "\"'");
                        $j++;
                    }
                } else {
                    $tmp['parameters'] = "";
                }
                $tmp['or'] = $ors[$i] == "|";
                $result[$attributeCall][] = $tmp;
                $i++;
            }
        }
        return $result;
    }
}
