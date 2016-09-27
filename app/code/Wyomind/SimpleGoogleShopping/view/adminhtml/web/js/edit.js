/**
 * Copyright © 2015 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

SimpleGoogleShopping = {
    /**
     * All about categories selection/filter
     */
    categories: {
        /**
         * Update the selected categories
         * @returns {undefined}
         */
        updateSelection: function () {
            var selection = {};
            jQuery('input.category').each(function () {
                var elt = jQuery(this);
                var id = elt.attr('id').replace('cat_id_', '');
                var mapping = jQuery('#category_mapping_' + id).val();
                selection[id] = {c: (jQuery(this).prop('checked') === true ? '1' : '0'), m: mapping};
            });
            jQuery('#simplegoogleshopping_categories').val(JSON.stringify(selection));

        },
        /**
         * Select all children categories
         * @param {type} elt
         * @returns {undefined}
         */
        selectChildren: function (elt) {
            var checked = elt.prop('checked');
            elt.parent().parent().find('input.category').each(function () {
                if (checked) {
                    jQuery(this).parent().addClass('selected'); } else {
jQuery(this).parent().removeClass('selected'); }
                jQuery(this).prop('checked', checked);
            });
        },
        /**
         * Init the categories tree from the model data
         * @returns {undefined}
         */
        loadCategories: function () {
            var cats = jQuery('#simplegoogleshopping_categories').val();
            if (cats === "") {
                jQuery('#simplegoogleshopping_categories').val('*');
                cats = '*';
            }
            if (cats === "*") {
                return; }
            var sel = jQuery.parseJSON(cats);
            for (var i in sel) {
                if (sel[i]['c'] == "1") {
                    // select the category
                    jQuery('#cat_id_' + i).prop('checked', true);
                    jQuery('#cat_id_' + i).parent().addClass('selected');
                    // open the tv-switcher for all previous level
                    jQuery('#cat_id_' + i).parent().parent().parent().addClass('opened').removeClass('closed');
                    var path = jQuery('#cat_id_' + i).attr('parent_id').split('/');
                    path.each(function (j) {
                        jQuery('#cat_id_' + j).parent().parent().parent().addClass('opened').removeClass('closed');
                        jQuery('#cat_id_' + j).prev().addClass('opened').removeClass('closed');
                    });
                }
                // set the category mapping
                jQuery('#category_mapping_' + i).val(sel[i]['m']);
            }
        },
        /**
         * Load the categories filter (exclude/include)
         * @returns {undefined}
         */
        loadCategoriesFilter: function () {
            if (jQuery("#simplegoogleshopping_category_filter").val() == "") {
                jQuery("#simplegoogleshopping_category_filter").val(1);
            }
            if (jQuery("#simplegoogleshopping_category_type").val() == "") {
                jQuery("#simplegoogleshopping_category_type").val(0);
            }
            jQuery('#category_filter_' + jQuery("#simplegoogleshopping_category_filter").val()).prop('checked', true);
            jQuery('#category_type_' + jQuery("#simplegoogleshopping_category_type").val()).prop('checked', true);
        },
        /**
         * Update all children with the parent mapping
         * @param {type} mapping
         * @returns {undefined}
         */
        updateChildrenMapping: function (mapping) {
            mapping.parent().parent().parent().find('input.mapping').each(function () {
                jQuery(this).val(mapping.val());
            });
            SimpleGoogleShopping.categories.updateSelection();
        },
        /**
         * Initialiaz autocomplete fields for the mapping
         * @returns {undefined}
         */
        initAutoComplete: function () {
            jQuery('.mapping').each(function () {
                jQuery(this).autocomplete({
                    source: jQuery('#categories_url').val() + "?file=" + jQuery('#feed_taxonomy').val(),
                    minLength: 2,
                    select: function (event, ui) {
                        SimpleGoogleShopping.categories.updateSelection();
                    }
                });
            });
        },
        /**
         * Reinit the autocomple fields with a new taxonomy file
         * @returns {undefined}
         */
        updateAutoComplete: function () {
            jQuery('.mapping').each(function () {
                jQuery(this).autocomplete("option", "source", jQuery('#categories_url').val() + "?file=" + jQuery('#feed_taxonomy').val());
            });
        }
    },
    /**
     * All about filters
     */
    filters: {
        /**
         * Load the selected product types
         * @returns {undefined}
         */
        loadProductTypes: function () {
            var values = jQuery('#simplegoogleshopping_type_ids').val();
            if (jQuery('#simplegoogleshopping_type_ids').val() === "") {
                jQuery('#simplegoogleshopping_type_ids').val('*');
                values = '*';
            }
            if (values !== '*') {
                values = values.split(',');
                values.each(function (v) {
                    jQuery('#type_id_' + v).prop('checked', true);
                    jQuery('#type_id_' + v).parent().addClass('selected');
                });
            } else {
                jQuery('#type-ids-selector').find('input').each(function () {
                    jQuery(this).prop('checked', true);
                    jQuery(this).parent().addClass('selected');
                });
            }
        },
        /**
         * Check if all product types are selected
         * @returns {Boolean}
         */
        isAllProductTypesSelected: function () {
            var all = true;
            jQuery(document).find('.filter_product_type').each(function () {
                if (jQuery(this).prop('checked') === false) {
                    all = false; }
            });
            return all;
        },
        /**
         * Update product types selection
         * @returns {undefined}
         */
        updateProductTypes: function () {
            var values = new Array();
            jQuery('.filter_product_type').each(function (i) {
                if (jQuery(this).prop('checked')) {
                    values.push(jQuery(this).attr('identifier'));
                }
            });
            jQuery('#simplegoogleshopping_type_ids').val(values.join());
            SimpleGoogleShopping.filters.updateUnSelectLinksProductTypes();
        },
        /**
         * Load the selected atribute set
         * @returns {undefined}
         */
        loadAttributeSets: function () {
            var values = jQuery('#simplegoogleshopping_attribute_sets').val();
            if (jQuery('#simplegoogleshopping_attribute_sets').val() === "") {
                jQuery('#simplegoogleshopping_attribute_sets').val('*');
                values = '*';
            }
            if (values != '*') {
                values = values.split(',');
                values.each(function (v) {
                    jQuery('#attribute_set_' + v).prop('checked', true);
                    jQuery('#attribute_set_' + v).parent().addClass('selected');
                });
            } else {
                jQuery('#attribute-sets-selector').find('input').each(function () {
                    jQuery(this).prop('checked', true);
                    jQuery(this).parent().addClass('selected');
                });
            }
        },
        /**
         * Update attribute sets selection
         * @returns {undefined}
         */
        updateAttributeSets: function () {
            var values = new Array();
            var all = true;
            jQuery('.filter_attribute_set').each(function (i) {
                if (jQuery(this).prop('checked')) {
                    values.push(jQuery(this).attr('identifier'));
                } else {
                    all = false;
                }
            });
            if (all) {
                jQuery('#simplegoogleshopping_attribute_sets').val('*');
            } else {
                jQuery('#simplegoogleshopping_attribute_sets').val(values.join());
            }
            SimpleGoogleShopping.filters.updateUnSelectLinksAttributeSets();
        },
        /**
         * Check if all attribute sets are selected
         * @returns {Boolean}
         */
        isAllAttributeSetsSelected: function () {
            var all = true;
            jQuery(document).find('.filter_attribute_set').each(function () {
                if (jQuery(this).prop('checked') === false) {
                    all = false; }
            });
            return all;
        },
        /**
         * Load the selected product visibilities
         * @returns {undefined}
         */
        loadProductVisibilities: function () {
            var values = jQuery('#simplegoogleshopping_visibility').val();
            if (jQuery('#simplegoogleshopping_visibility').val() === '') {
                jQuery('#simplegoogleshopping_visibility').val('*');
                values = '*';
            }
            if (values !== '*') {
                values = values.split(',');
                values.each(function (v) {
                    jQuery('#visibility_' + v).prop('checked', true);
                    jQuery('#visibility_' + v).parent().addClass('selected');
                });
            } else {
                jQuery('#visibility-selector').find('input').each(function () {
                    jQuery(this).prop('checked', true);
                    jQuery(this).parent().addClass('selected');
                });
            }
        },
        /**
         * Update visibilities selection
         * @returns {undefined}
         */
        updateProductVisibilities: function () {
            var values = new Array();
            //var all = true;
            jQuery('.filter_visibility').each(function (i) {
                if (jQuery(this).prop('checked')) {
                    values.push(jQuery(this).attr('identifier'));
                }/* else {
                 all = false;
                 }*/
            });
            /*if (all)
             jQuery('#simplegoogleshopping_visibility').val('*');
             else*/
            jQuery('#simplegoogleshopping_visibility').val(values.join());
            SimpleGoogleShopping.filters.updateUnSelectLinksProductVisibilities();
        },
        /**
         * Check if all product visibilities are selected
         * @returns {Boolean}
         */
        isAllProductVisibilitiesSelected: function () {
            var all = true;
            jQuery(document).find('.filter_visibility').each(function () {
                if (jQuery(this).prop('checked') === false) {
                    all = false; }
            });
            return all;
        },
        /**
         * Check if we need to display 'Select All' or 'Unselect All' for each kind of filters
         * @returns {undefined}
         */
        updateUnSelectLinks: function () {
            SimpleGoogleShopping.filters.updateUnSelectLinksProductTypes();
            SimpleGoogleShopping.filters.updateUnSelectLinksAttributeSets();
            SimpleGoogleShopping.filters.updateUnSelectLinksProductVisibilities();
        },
        /**
         * Check if we need to display 'Select All' or 'Unselect All' for product types
         * @returns {undefined}
         */
        updateUnSelectLinksProductTypes: function () {
            if (SimpleGoogleShopping.filters.isAllProductTypesSelected()) {
                jQuery('#type-ids-selector').find('.select-all').removeClass('visible');
                jQuery('#type-ids-selector').find('.unselect-all').addClass('visible');
            } else {
                jQuery('#type-ids-selector').find('.select-all').addClass('visible');
                jQuery('#type-ids-selector').find('.unselect-all').removeClass('visible');
            }
        },
        /**
         * Check if we need to display 'Select All' or 'Unselect All' for attributes sets
         * @returns {undefined}
         */
        updateUnSelectLinksAttributeSets: function () {
            if (SimpleGoogleShopping.filters.isAllAttributeSetsSelected()) {
                jQuery('#attribute-sets-selector').find('.select-all').removeClass('visible');
                jQuery('#attribute-sets-selector').find('.unselect-all').addClass('visible');
            } else {
                jQuery('#attribute-sets-selector').find('.select-all').addClass('visible');
                jQuery('#attribute-sets-selector').find('.unselect-all').removeClass('visible');
            }
        },
        /**
         * Check if we need to display 'Select All' or 'Unselect All' for product visibilities
         * @returns {undefined}
         */
        updateUnSelectLinksProductVisibilities: function () {
            if (SimpleGoogleShopping.filters.isAllProductVisibilitiesSelected()) {
                jQuery('#visibility-selector').find('.select-all').removeClass('visible');
                jQuery('#visibility-selector').find('.unselect-all').addClass('visible');
            } else {
                jQuery('#visibility-selector').find('.select-all').addClass('visible');
                jQuery('#visibility-selector').find('.unselect-all').removeClass('visible');
            }
        },
        /**
         * Load the selected advanced filters
         * @returns {undefined}
         */
        loadAdvancedFilters: function () {
            var filters = jQuery.parseJSON(jQuery('#simplegoogleshopping_attributes').val());
            if (filters === null) {
                filters = new Array();
                jQuery('#simplegoogleshopping_attributes').val(JSON.stringify(filters));
            }
            var counter = 0;
            while (filters[counter]) {
                filter = filters[counter];
                jQuery('#attribute_' + counter).prop('checked', filter.checked);

                jQuery('#name_attribute_' + counter).val(filter.code);
                jQuery('#value_attribute_' + counter).val(filter.value);
                jQuery('#condition_attribute_' + counter).val(filter.condition);
                if (filter.statement) {
                    jQuery('#statement_attribute_' + counter).val(filter.statement);
                }

                SimpleGoogleShopping.filters.updateRow(counter, filter.code);

                jQuery('#name_attribute_' + counter).prop('disabled', !filter.checked);
                jQuery('#condition_attribute_' + counter).prop('disabled', !filter.checked);
                jQuery('#value_attribute_' + counter).prop('disabled', !filter.checked);
                jQuery('#pre_value_attribute_' + counter).prop('disabled', !filter.checked);
                jQuery('#statement_attribute_' + counter).prop('disabled', !filter.checked);


                jQuery('#pre_value_attribute_' + counter).val(filter.value);

                counter++;
            }
        },
        /**
         * Update the advanced filters json string
         * @returns {undefined}
         */
        updateAdvancedFilters: function () {
            var newval = {};
            var counter = 0;
            jQuery('.advanced_filters').each(function () {
                var checkbox = jQuery(this).find('#attribute_' + counter).prop('checked');
                // is the row activated
                if (checkbox) {
                    jQuery('#name_attribute_' + counter).prop('disabled', false);
                    jQuery('#condition_attribute_' + counter).prop('disabled', false);
                    jQuery('#value_attribute_' + counter).prop('disabled', false);
                    jQuery('#pre_value_attribute_' + counter).prop('disabled', false);
                    jQuery('#statement_attribute_' + counter).prop('disabled', false);
                } else {
                    jQuery('#name_attribute_' + counter).prop('disabled', true);
                    jQuery('#condition_attribute_' + counter).prop('disabled', true);
                    jQuery('#value_attribute_' + counter).prop('disabled', true);
                    jQuery('#pre_value_attribute_' + counter).prop('disabled', true);
                    jQuery('#statement_attribute_' + counter).prop('disabled', true);
                }
                var statement = jQuery(this).find('#statement_attribute_' + counter).val();
                var name = jQuery(this).find('#name_attribute_' + counter).val();
                var condition = jQuery(this).find('#condition_attribute_' + counter).val();
                var pre_value = jQuery(this).find('#pre_value_attribute_' + counter).val();
                var value = jQuery(this).find('#value_attribute_' + counter).val();
                if (attribute_codes[name] && attribute_codes[name].length > 0) {
                    value = pre_value;
                }
                var val = {checked: checkbox, code: name, statement: statement, condition: condition, value: value};
                newval[counter] = val;
                counter++;
            });
            jQuery('#simplegoogleshopping_attributes').val(JSON.stringify(newval));
        },
        /**
         * Update an advanced filter row (display custom value or not, display multi select, ...)
         * @param {type} id
         * @param {type} attribute_code
         * @returns {undefined}
         */
        updateRow: function (id, attribute_code) {
            if (attribute_codes[attribute_code] && attribute_codes[attribute_code].length > 0) {
                // enable multi select or dropdown
                jQuery('#pre_value_attribute_' + id).prop('disabled', false);

                // full the multi select / dropdown
                jQuery('#pre_value_attribute_' + id).html("");
                attribute_codes[attribute_code].each(function (elt) {

                    jQuery('#pre_value_attribute_' + id).append(jQuery('<option>', {
                        value: elt.value,
                        text: elt.label
                    }));
                });
                jQuery('#pre_value_attribute_' + id).val(attribute_codes[attribute_code][0].value);


                // if "in/not in", then multiselect
                if (jQuery('#condition_attribute_' + id).val() === "in" || jQuery('#condition_attribute_' + id).val() === "nin") {
                    jQuery('#pre_value_attribute_' + id).attr('size', '5');
                    jQuery('#pre_value_attribute_' + id).prop('multiple', true);
                    jQuery('#name_attribute_' + id).parent().parent().parent().parent().addClass('multiple-value').removeClass('one-value').removeClass('dddw');
                    jQuery('#value_attribute_' + id).css('display', 'none');
                } else if (jQuery('#condition_attribute_' + id).val() === "null" || jQuery('#condition_attribute_' + id).val() === "notnull") {
                    jQuery('#name_attribute_' + id).parent().parent().parent().parent().removeClass('multiple-value').addClass('one-value').removeClass('dddw');
                    jQuery('#value_attribute_' + id).css('display', 'none');
                } else { // else, dropdown
                    jQuery('#pre_value_attribute_' + id).prop('size', '1');
                    jQuery('#pre_value_attribute_' + id).prop('multiple', false);
                    jQuery('#name_attribute_' + id).parent().parent().parent().parent().removeClass('multiple-value').addClass('one-value').addClass('dddw');
                    jQuery('#value_attribute_' + id).css('display', 'none');
                }
            } else {
                jQuery('#name_attribute_' + id).parent().parent().parent().parent().removeClass('multiple-value').addClass('one-value').removeClass('dddw');
                jQuery('#pre_value_attribute_' + id).prop('disabled', true);
                if (jQuery('#condition_attribute_' + id).val() === "null" || jQuery('#condition_attribute_' + id).val() === "notnull") {
                    jQuery('#value_attribute_' + id).css('display', 'none');
                } else {
                    jQuery('#value_attribute_' + id).css('display', 'inline');
                }
            }
        },
        /**
         * Click on select all link
         * @param {type} elt
         * @returns {undefined}
         */
        selectAll: function (elt) {
            var fieldset = elt.parents('.fieldset')[0];
            jQuery(fieldset).find('input[type=checkbox]').each(function () {
                jQuery(this).prop('checked', true);
                jQuery(this).parent().addClass('selected');
            });
            SimpleGoogleShopping.filters.updateProductTypes();
            SimpleGoogleShopping.filters.updateProductVisibilities();
            SimpleGoogleShopping.filters.updateAttributeSets();
            elt.removeClass('visible');
            jQuery(fieldset).find('.unselect-all').addClass('visible');
        },
        /**
         * Click on unselect all link
         * @param {type} elt
         * @returns {undefined}
         */
        unselectAll: function (elt) {
            var fieldset = elt.parents('.fieldset')[0];
            jQuery(fieldset).find('input[type=checkbox]').each(function () {
                jQuery(this).prop('checked', false);
                jQuery(this).parent().removeClass('selected');
            });
            SimpleGoogleShopping.filters.updateProductTypes();
            SimpleGoogleShopping.filters.updateProductVisibilities();
            SimpleGoogleShopping.filters.updateAttributeSets();
            elt.removeClass('visible');
            jQuery(fieldset).find('.select-all').addClass('visible');
        }
    },
    /**
     * All about Preview/Library boxes
     */
    boxes: {
        report: false,
        library: false,
        preview: false,
        init: function () {
            /* maxter box */
            jQuery('<div/>', {
                id: 'master-box',
                class: 'master-box'
            }).appendTo('#html-body');

            /* preview tag */
            jQuery('<div/>', {
                id: 'preview-tag',
                class: 'preview-tag box-tag'
            }).appendTo('#html-body');
            jQuery('<div/>', {
                text: jQuery.mage.__('Preview')
            }).appendTo('#preview-tag');

            /* library tag */
            jQuery('<div/>', {
                id: 'library-tag',
                class: 'library-tag box-tag'
            }).appendTo('#html-body');
            jQuery('<div/>', {
                text: jQuery.mage.__('Library')
            }).appendTo('#library-tag');

            /* report tag */
            jQuery('<div/>', {
                id: 'report-tag',
                class: 'report-tag box-tag'
            }).appendTo('#html-body');
            jQuery('<div/>', {
                text: jQuery.mage.__('Report')
            }).appendTo('#report-tag');


            /* preview tab */
            jQuery('<div/>', {// preview master box
                id: 'preview-master-box',
                class: 'preview-master-box visible'
            }).appendTo('#master-box');
            jQuery('<span/>', {// refresh button
                id: 'preview-refresh-btn',
                class: 'preview-refresh-btn',
                html: '<span class="preview-refresh-btn-icon"> </span> <span>' + jQuery.mage.__('Refresh the preview') + '</span>'
            }).appendTo('#preview-master-box');


            jQuery('<textarea/>', {// preview content
                id: 'preview-area',
                class: 'preview-area'
            }).appendTo('#preview-master-box');

            jQuery('<div/>', {// loader
                id: 'preview-box-loader',
                class: 'box-loader',
                html: '<div class="ajax-loader"></load>'
            }).appendTo('#preview-master-box');


            /* library tab */
            jQuery('<div/>', {// library master box
                id: 'library-master-box',
                class: 'library-master-box visible'
            }).appendTo('#master-box');

            jQuery('<div/>', {// loader
                id: 'library-box-loader',
                class: 'box-loader',
                html: '<div class="ajax-loader"></load>'
            }).appendTo('#library-master-box');

            jQuery('<div/>', {// library content
                id: 'library-area',
                class: 'library-area'
            }).appendTo('#library-master-box');

            /* report tab */
            jQuery('<div/>', {// library master box
                id: 'report-master-box',
                class: 'report-master-box visible'
            }).appendTo('#master-box');
            jQuery('<div/>', {// loader
                id: 'report-box-loader',
                class: 'box-loader',
                html: '<div class="ajax-loader"></load>'
            }).appendTo('#report-master-box');
            jQuery('<span/>', {// refresh button
                id: 'report-refresh-btn',
                class: 'report-refresh-btn',
                html: '<span class="report-refresh-btn-icon"> </span> <span>' + jQuery.mage.__('Refresh the report') + '</span>'
            }).appendTo('#report-master-box');
            jQuery('<div/>', {// library content
                id: 'report-area',
                class: 'report-area'
            }).appendTo('#report-master-box');
        },
        /**
         * Close the box
         * @returns {undefined}
         */
        close: function () {
            jQuery('.box-tag').each(function () {
                jQuery(this).removeClass('opened');
                jQuery(this).removeClass('selected');
            });
            jQuery('.master-box').removeClass('opened');
            jQuery('#library-master-box').removeClass('visible');
            jQuery('#preview-master-box').removeClass('visible');
            jQuery('#report-master-box').removeClass('visible');
        },
        /**
         * Open the preview box when no box opened
         * @returns {undefined}
         */
        openPreview: function () {
            jQuery("#preview-tag").addClass('selected');
            // translates tags
            jQuery('.box-tag').each(function () {
                jQuery(this).addClass('opened');
            });
            // translates main box
            jQuery('.master-box').addClass('opened');
            // on affiche le preview
            jQuery('#library-master-box').removeClass('visible');
            jQuery('#preview-master-box').addClass('visible');
            jQuery('#report-master-box').removeClass('visible');
        },
        /**
         * Open the library box when no box opened
         * @returns {undefined}
         */
        openLibrary: function () {
            jQuery("#library-tag").addClass('selected');
            // translate tags
            jQuery('.box-tag').each(function () {
                jQuery(this).addClass('opened');
            });
            // translates main box
            jQuery('.master-box').addClass('opened');
            // on affiche le preview
            jQuery('#library-master-box').addClass('visible');
            jQuery('#preview-master-box').removeClass('visible');
            jQuery('#report-master-box').removeClass('visible');
        },
        /**
         * Open the report box when no box opened
         * @returns {undefined}
         */
        openReport: function () {
            jQuery("#report-tag").addClass('selected');
            // translate tags
            jQuery('.box-tag').each(function () {
                jQuery(this).addClass('opened');
            });
            // translates main box
            jQuery('.master-box').addClass('opened');
            // on affiche le preview
            jQuery('#report-master-box').addClass('visible');
            jQuery('#preview-master-box').removeClass('visible');
            jQuery('#library-master-box').removeClass('visible');
        },
        /**
         * Switch to the preview box
         * @returns {undefined}
         */
        switchToPreview: function () {
            jQuery('.box-tag').each(function () {
                jQuery(this).removeClass('selected');
            });
            jQuery("#preview-tag").addClass('selected');
            jQuery('#library-master-box').removeClass('visible');
            jQuery('#preview-master-box').addClass('visible');
            jQuery('#report-master-box').removeClass('visible');
        },
        /**
         * Switch to the library box
         * @returns {undefined}
         */
        switchToLibrary: function () {
            jQuery('.box-tag').each(function () {
                jQuery(this).removeClass('selected');
            });
            jQuery("#library-tag").addClass('selected');
            jQuery('#library-master-box').addClass('visible');
            jQuery('#preview-master-box').removeClass('visible');
            jQuery('#report-master-box').removeClass('visible');
        },
        /**
         * Switch to the report box
         * @returns {undefined}
         */
        switchToReport: function () {
            jQuery('.box-tag').each(function () {
                jQuery(this).removeClass('selected');
            });
            jQuery("#report-tag").addClass('selected');
            jQuery('#report-master-box').addClass('visible');
            jQuery('#preview-master-box').removeClass('visible');
            jQuery('#library-master-box').removeClass('visible');
        },
        /*
         * 
         * @returns {undefined}
         */
        hideLoaders: function () {
            jQuery(".box-loader").css("display", "none");
        },
        showLoader: function (name) {
            jQuery("#" + name + "-box-loader").css("display", "block");
        },
        /**
         * Refresh the preview
         * @returns {undefined}
         */
        refreshPreview: function () {
            if (!jQuery(this).hasClass('selected') && jQuery(this).hasClass('opened')) { // panneau ouvert sur library
                SimpleGoogleShopping.boxes.switchToPreview();
            } else if (jQuery(this).hasClass('selected') && jQuery(this).hasClass('opened')) { // panneau ouvert sur preview
                SimpleGoogleShopping.boxes.close();
            } else { // panneau non ouvert
                SimpleGoogleShopping.boxes.openPreview();
            }
            var requestUrl = jQuery('#sample_url').val();
            CodeMirrorPreview.setValue("");

            SimpleGoogleShopping.boxes.showLoader("preview");
            if (typeof request != "undefined") {
                request.abort();
            }
            request = jQuery.ajax({
                url: requestUrl,
                type: 'POST',
                showLoader: false,
                data: {
                    real_time_preview: true,
                    simplegoogleshopping_id: jQuery('#simplegoogleshopping_id').val(),
                    store_id: jQuery('#store_id').val(),
                    simplegoogleshopping_url: jQuery('#simplegoogleshopping_url').val(),
                    simplegoogleshopping_title: jQuery('#simplegoogleshopping_title').val(),
                    simplegoogleshopping_xmlitempattern: CodeMirrorPattern.getValue(),
                    simplegoogleshopping_description: jQuery('#simplegoogleshopping_description').val(),
                    simplegoogleshopping_categories: jQuery('#simplegoogleshopping_categories').val(),
                    simplegoogleshopping_category_filter: jQuery('#simplegoogleshopping_category_filter').val(),
                    simplegoogleshopping_category_type: jQuery('#simplegoogleshopping_category_type').val(),
                    simplegoogleshopping_type_ids: jQuery('#simplegoogleshopping_type_ids').val(),
                    simplegoogleshopping_visibility: jQuery('#simplegoogleshopping_visibility').val(),
                    simplegoogleshopping_attributes: jQuery('#simplegoogleshopping_attributes').val(),
                    simplegoogleshopping_attribute_sets: jQuery('#simplegoogleshopping_attribute_sets').val()
                },
                success: function (data) {
//                    if (data.indexOf("ajaxExpired") !== -1) {
//                        data = "Expired Session";
//                    }
                    if (!data.data) {
                        CodeMirrorPreview.setValue(data);
                    } else {
                        CodeMirrorPreview.setValue(data.data);
                    }
                    SimpleGoogleShopping.boxes.hideLoaders();
                    SimpleGoogleShopping.boxes.preview = true;
                }
            });
        },
        /**
         * Refresh the report
         * @returns {undefined}
         */
        refreshReport: function () {
            if (!jQuery(this).hasClass('selected') && jQuery(this).hasClass('opened')) { // panneau ouvert sur library
                SimpleGoogleShopping.boxes.switchToReport();
            } else if (jQuery(this).hasClass('selected') && jQuery(this).hasClass('opened')) { // panneau ouvert sur preview
                SimpleGoogleShopping.boxes.close();
            } else { // panneau non ouvert
                SimpleGoogleShopping.boxes.openReport();
            }
            var requestUrl = jQuery('#samplereport_url').val();
            SimpleGoogleShopping.boxes.showLoader("report");
            if (typeof request != "undefined") {
                request.abort();
            }
            request = jQuery.ajax({
                url: requestUrl,
                type: 'POST',
                showLoader: false,
                data: {
                    real_time_preview: true,
                    simplegoogleshopping_id: jQuery('#simplegoogleshopping_id').val(),
                    store_id: jQuery('#store_id').val(),
                    simplegoogleshopping_url: jQuery('#simplegoogleshopping_url').val(),
                    simplegoogleshopping_title: jQuery('#simplegoogleshopping_title').val(),
                    simplegoogleshopping_xmlitempattern: CodeMirrorPattern.getValue(),
                    simplegoogleshopping_description: jQuery('#simplegoogleshopping_description').val(),
                    simplegoogleshopping_categories: jQuery('#simplegoogleshopping_categories').val(),
                    category_filter: jQuery('#simplegoogleshopping_category_filter').val(),
                    simplegoogleshopping_type_ids: jQuery('#simplegoogleshopping_type_ids').val(),
                    simplegoogleshopping_visibility: jQuery('#simplegoogleshopping_visibility').val(),
                    simplegoogleshopping_attributes: jQuery('#simplegoogleshopping_attributes').val(),
                    simplegoogleshopping_attribute_sets: jQuery('#simplegoogleshopping_attribute_sets').val()
                },
                success: function (data) {
                    if (data.indexOf("ajaxExpired") !== -1) {
                        data = "Expired Session";
                    }

                    jQuery('#report-area').html(data);
                    SimpleGoogleShopping.boxes.report = true;
                    SimpleGoogleShopping.boxes.hideLoaders();
                }
            });
        },
        /**
         * Initialize the library boxe
         * @returns {undefined}
         */
        loadLibrary: function () {
            var requestUrl = jQuery('#library_url').val();
            SimpleGoogleShopping.boxes.showLoader("library");
            if (typeof request != "undefined") {
                request.abort();
            }
            request = jQuery.ajax({
                url: requestUrl,
                type: 'GET',
                showLoader: false,
                success: function (data) {
                    jQuery('#library-area').html(data);
                    SimpleGoogleShopping.boxes.hideLoaders();
                    SimpleGoogleShopping.boxes.library = true;
                }
            });
        },
        /**
         * Load a sample of product for an attribute in the library boxe
         * @param {type} elt
         * @returns {undefined}
         */
        loadLibrarySamples: function (elt) {
            var requestUrl = jQuery('#library_sample_url').val();
            var code = elt.attr('att_code');
            var store_id = jQuery('#store_id').val();


            if (elt.find('span').hasClass('opened')) {
                elt.find('span').addClass('closed').removeClass('opened');
                elt.parent().next().find('td').html("");
                elt.parent().next().removeClass('visible');
                return;
            }
            SimpleGoogleShopping.boxes.showLoader("library");
            if (typeof request != "undefined") {
                request.abort();
            }
            request = jQuery.ajax({
                url: requestUrl,
                data: {
                    code: code,
                    store_id: store_id
                },
                type: 'GET',
                showLoader: false,
                success: function (data) {
                    elt.parent().next().addClass('visible');

                    var html = "<table class='inner-attribute'>";
                    if (data.length > 0) {
                        data.each(function (elt) {
                            html += "<tr><td class='name'><b>" + elt.name + "</b><br/>" + elt.sku + "</td><td class='values'>" + elt.attribute + "<td></tr>";
                        });
                        html += "</table>";
                    } else {
                        html = jQuery.mage.__("No product found.");
                    }
                    elt.find('span').addClass('opened').removeClass('closed');
                    elt.parent().next().find('td').html(html);
                    SimpleGoogleShopping.boxes.hideLoaders();
                }
            });
        }
    },
    /**
     * All about cron tasks
     */
    cron: {
        /**
         * Load the selected days and hours
         */
        loadExpr: function () {
            if (jQuery('#cron_expr').val() == "") {
                jQuery('#cron_expr').val("{}");
            }
            var val = jQuery.parseJSON(jQuery('#cron_expr').val());
            if (val !== null) {
                val.days.each(function (elt) {
                    jQuery('#d-' + elt).parent().addClass('selected');
                    jQuery('#d-' + elt).prop('checked', true);
                });
                val.hours.each(function (elt) {
                    var hour = elt.replace(':', '');
                    jQuery('#h-' + hour).parent().addClass('selected');
                    jQuery('#h-' + hour).prop('checked', true);
                });
            }
        },
        /**
         * Update the json representation of the cron schedule
         */
        updateExpr: function () {
            var days = new Array();
            var hours = new Array();
            jQuery('.cron-box.day').each(function () {
                if (jQuery(this).prop('checked') === true) {
                    days.push(jQuery(this).attr('value'));
                }
            });
            jQuery('.cron-box.hour').each(function () {
                if (jQuery(this).prop('checked') === true) {
                    hours.push(jQuery(this).attr('value'));
                }
            });

            jQuery('#cron_expr').val(JSON.stringify({days: days, hours: hours}));
        }
    }
};

window.onload = function () {


    require(["jquery", "mage/mage", "mage/translate"], function ($) {
        $(function () {

            /* ========= Config ========================= */

            /* template editor */

            CodeMirrorPattern = CodeMirror.fromTextArea(document.getElementById('simplegoogleshopping_xmlitempattern'), {
                matchBrackets: true,
                mode: "application/x-httpd-php",
                indentUnit: 2,
                indentWithTabs: false,
                lineWrapping: true,
                lineNumbers: true,
                styleActiveLine: true
            });


            // to be sure that the good value will be well stored in db
            CodeMirrorPattern.on('blur', function () {
                jQuery('#simplegoogleshopping_xmlitempattern').val(CodeMirrorPattern.getValue());
            });


            /* ========= Filters ========================= */

            /* select product types */
            jQuery(document).on("click", ".filter_product_type", function (evt) {
                var elt = jQuery(this);
                elt.parent().toggleClass('selected');
                SimpleGoogleShopping.filters.updateProductTypes();
            });
            SimpleGoogleShopping.filters.loadProductTypes();

            /* select attribute sets */
            jQuery(document).on("click", ".filter_attribute_set", function (evt) {
                var elt = jQuery(this);
                elt.parent().toggleClass('selected');
                SimpleGoogleShopping.filters.updateAttributeSets();
            });
            SimpleGoogleShopping.filters.loadAttributeSets();

            /* select product visibilities */
            jQuery(document).on("click", ".filter_visibility", function (evt) {
                var elt = jQuery(this);
                elt.parent().toggleClass('selected');
                SimpleGoogleShopping.filters.updateProductVisibilities();
            });

            SimpleGoogleShopping.filters.loadProductVisibilities();

            /* un/select all */
            jQuery(document).on("click", ".select-all", function (evt) {
                var elt = jQuery(this);
                SimpleGoogleShopping.filters.selectAll(elt);
            });
            jQuery(document).on("click", ".unselect-all", function (evt) {
                var elt = jQuery(this);
                SimpleGoogleShopping.filters.unselectAll(elt);
            });

            SimpleGoogleShopping.filters.updateUnSelectLinks();

            /* select advanced filters */

            // change attribute select
            jQuery(document).on('change', '.name-attribute,.condition-attribute', function (evt) {
                var id = jQuery(this).attr('identifier');
                var attribute_code = jQuery('#name_attribute_' + id).val();
                SimpleGoogleShopping.filters.updateRow(id, attribute_code);

            });

            jQuery(document).on('change', '.checked-attribute,.statement-attribute,.name-attribute,.condition-attribute,.value-attribute,.pre-value-attribute', function (evt) {
                SimpleGoogleShopping.filters.updateAdvancedFilters();
            });



            SimpleGoogleShopping.filters.loadAdvancedFilters();


            /* ========= Categories ====================== */

            /* opening/closing treeview */
            jQuery(document).on("click", ".tv-switcher", function (evt) {
                var elt = jQuery(evt.target);
                // click on treeview expand/collapse
                if (elt.hasClass('closed')) {
                    elt.removeClass('closed');
                    elt.addClass('opened');
                    elt.parent().parent().find('> ul').each(function () {
                        jQuery(this).removeClass('closed');
                        jQuery(this).addClass('opened');
                    });
                } else if (elt.hasClass('opened')) {
                    elt.addClass('closed');
                    elt.removeClass('opened');
                    elt.parent().parent().find('> ul').each(function () {
                        jQuery(this).removeClass('opened');
                        jQuery(this).addClass('closed');
                    });
                }
            });

            // click on category select
            jQuery(document).on("click", ".category", function (evt) {
                jQuery(this).parent().toggleClass('selected');
                SimpleGoogleShopping.categories.selectChildren(jQuery(this));
                SimpleGoogleShopping.categories.updateSelection();
            });

            // change categories filter value
            jQuery(document).on("click", ".category_filter", function (evt) {
                jQuery("#simplegoogleshopping_category_filter").val(jQuery(this).val());
            });

            // change categories type value
            jQuery(document).on("click", ".category_type", function (evt) {
                jQuery("#simplegoogleshopping_category_type").val(jQuery(this).val());
            });

            /* change mapping */
            jQuery(document).on("change", ".mapping", function () {
                SimpleGoogleShopping.categories.updateSelection();
            });

            /* initialize dropdown mapping */
            SimpleGoogleShopping.categories.initAutoComplete();

            // change the taxonomy file
            jQuery(document).on('change', '#feed_taxonomy', function () {
                SimpleGoogleShopping.categories.updateAutoComplete();
            });

            /* initialize end keyboard shortcut */
            jQuery(document).on("keyup", ".mapping", function (event) {
                if (event.key === "End") {
                    SimpleGoogleShopping.categories.updateChildrenMapping(jQuery(this));
                }
            });

            // load selected categories
            SimpleGoogleShopping.categories.loadCategories();
            // load the categories filter
            SimpleGoogleShopping.categories.loadCategoriesFilter();


            /* ========= Preview + Library + Report ================== */

            SimpleGoogleShopping.boxes.init();

            /* click on preview tag */
            jQuery(document).on('click', '.preview-tag.box-tag', function () {
                if (!jQuery(this).hasClass('selected') && jQuery(this).hasClass('opened')) { // panneau ouvert sur library
                    SimpleGoogleShopping.boxes.switchToPreview();
                } else if (jQuery(this).hasClass('selected') && jQuery(this).hasClass('opened')) { // panneau ouvert sur preview
                    SimpleGoogleShopping.boxes.close();
                } else { // panneau non ouvert
                    SimpleGoogleShopping.boxes.openPreview();
                }
            });

            /* click on library tag */
            jQuery(document).on('click', '.library-tag.box-tag', function () {
                if (!jQuery(this).hasClass('selected') && jQuery(this).hasClass('opened')) { // panneau ouvert sur preview
                    SimpleGoogleShopping.boxes.switchToLibrary();
                } else if (jQuery(this).hasClass('selected') && jQuery(this).hasClass('opened')) { // panneau ouvert sur library
                    SimpleGoogleShopping.boxes.close();
                } else { // panneau non ouvert
                    SimpleGoogleShopping.boxes.openLibrary();
                }
            });

            /* click on report tag */
            jQuery(document).on('click', '.report-tag.box-tag', function () {
                if (!jQuery(this).hasClass('selected') && jQuery(this).hasClass('opened')) { // panneau ouvert sur preview
                    SimpleGoogleShopping.boxes.switchToReport();
                } else if (jQuery(this).hasClass('selected') && jQuery(this).hasClass('opened')) { // panneau ouvert sur library
                    SimpleGoogleShopping.boxes.close();
                } else { // panneau non ouvert
                    SimpleGoogleShopping.boxes.openReport();
                }
            });

            /* initialize the preview box with CodeMirror */
            CodeMirrorPreview = CodeMirror.fromTextArea(document.getElementById('preview-area'), {
                matchBrackets: true,
                mode: "application/x-httpd-php",
                indentUnit: 2,
                indentWithTabs: false,
                lineWrapping: false,
                lineNumbers: true,
                styleActiveLine: true,
                readOnly: true
            });

            /* click on refresh preview */
            jQuery(document).on('click', '.preview-refresh-btn', function () {
                SimpleGoogleShopping.boxes.switchToPreview();
                SimpleGoogleShopping.boxes.refreshPreview();
            });


            /* click on an attribute load sample */
            jQuery(document).on('click', '.load-attr-sample', function () {
                SimpleGoogleShopping.boxes.loadLibrarySamples(jQuery(this));
            });


            /* click on refresh report */
            jQuery(document).on('click', '.report-refresh-btn', function () {
                SimpleGoogleShopping.boxes.refreshReport();
            });

            /* Click on one tag */
            jQuery(document).on("click", '.box-tag', function () {
                if (jQuery(this).hasClass("preview-tag") && !SimpleGoogleShopping.boxes.preview) {
                    SimpleGoogleShopping.boxes.refreshPreview();
                }
                if (jQuery(this).hasClass("library-tag") && !SimpleGoogleShopping.boxes.library) {
                    SimpleGoogleShopping.boxes.loadLibrary();
                }
                if (jQuery(this).hasClass("report-tag") && !SimpleGoogleShopping.boxes.report) {
                    SimpleGoogleShopping.boxes.refreshReport();
                }
            });

            /* ========= Cron tasks  ================== */

            jQuery(document).on('change', '.cron-box', function () {
                jQuery(this).parent().toggleClass('selected');
                SimpleGoogleShopping.cron.updateExpr();
            });
            SimpleGoogleShopping.cron.loadExpr();

            CodeMirrorPattern.refresh();

        });
    });
};