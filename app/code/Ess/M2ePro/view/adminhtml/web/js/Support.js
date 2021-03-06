define([
    'M2ePro/Common'
], function () {

    window.Support = Class.create(Common, {

        initialize: function()
        {
            this.initFormValidation();

            jQuery.validator.addMethod('M2ePro-validate-email', function(value, el) {
                this.error = Validation.get('validate-email').error;
                return Validation.get('validate-email').test(value,el);
            }, M2ePro.translator.translate('Email is not valid.'));

            $('more_attachments_container').hide();
        },

        // ---------------------------------------

        toggleMoreButton: function()
        {
            $('more_attachments_container').show();
        },

        // ---------------------------------------

        moreAttachments: function()
        {
            var emptyField = false;

            $$('.field-files input').each(function(obj) {
                if (obj.value == '') {
                    emptyField = true;
                }
            });

            if (emptyField) {
                return;
            }

            jQuery('#more_button_container').clone().removeAttr('id').insertAfter('.field-files:last');

            $('more_attachments_container').hide();
        }

        // ---------------------------------------
    });
});