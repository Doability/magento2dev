define([
    'jquery',
    'ko',
    'uiComponent',
    'Oscprofessionals_Vatexempt/js/action/set-vatexempt-code',
    'Oscprofessionals_Vatexempt/js/action/cancel-vatexempt'
], function ($, ko, Component, setVatexemptCodeAction, cancelVatexemptAction) {
    'use strict';

    var quoteData = window.checkoutConfig.quoteData;
    var vatpernameCode = ko.observable(null);
    var vatcommentCode = ko.observable(null);
    if (quoteData) {
        vatpernameCode(quoteData.vatpername);
        vatcommentCode(quoteData.vatcomment);
    }
    var isApplied = ko.observable(vatpernameCode() != null);
    var isLoading = ko.observable(false);
    return Component.extend({
        defaults: {
            template: 'Oscprofessionals_Vatexempt/checkout/vatexempt-form'
        },
        vatpernameCode: vatpernameCode,
        vatcommentCode: vatcommentCode,
        /**
         * Applied flag
         */
        isApplied: isApplied,
        isLoading: isLoading,
        /**
         * Coupon code application procedure
         */
        apply: function() {
            var form = '#vatexempt-form',
                formDataArray = $(form).serializeArray(),
                vatFormData = {};

            if (this.validate(form)) {
                formDataArray.forEach(function (entry) {
                    vatFormData[entry.name] = entry.value;
                })
                isLoading(true);
                setVatexemptCodeAction(vatFormData, isApplied, isLoading);
            }
        },
        /**
         * Cancel using coupon
         */
        cancel: function() {
            if (this.validate()) {
                isLoading(true);
                vatpernameCode('');
                vatcommentCode('');
                cancelVatexemptAction(isApplied, isLoading);
            }
        },
        /**
         * Coupon form validation
         *
         * @returns {boolean}
         */
        validate: function(form) {
            return $(form).validation() && $(form).validation('isValid');
        }
    });
});
