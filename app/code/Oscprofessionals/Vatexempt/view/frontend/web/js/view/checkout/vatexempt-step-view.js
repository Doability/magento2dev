define(
    [
        'ko',
        'uiComponent',
        'underscore',
		'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/step-navigator'
    ],
    function (
        ko,
        Component,
        _,
		quote,
        stepNavigator
    ) {
        'use strict';
        /**
        *
        * mystep - is the name of the component's .html template,
        * my_module - is the name of the your module directory.
        *
        */
		
		var quoteitems = quote.getItems(),
			vatexemptDataJs = window.vatexemptConfig,
			isVatexempt = false;
		if(vatexemptDataJs.isVatexemptEnabled){
			for(var index in quoteitems){
				if(quoteitems[index].vat_exempt == 1){
						isVatexempt = true;
					}			
			}			
		}	
        return Component.extend({
            defaults: {
                template: 'Oscprofessionals_Vatexempt/checkout/vatexemptstep'
            },

            //add here your logic to display step,
            isVisible: ko.observable(false),

            initialize: function () {
                this._super();
                // register your step
				console.log("text "+isVatexempt);
				if(isVatexempt){
					stepNavigator.registerStep(
						'vatexempt',                    
						null,
						'Vat Exempt',
						this.isVisible,
						_.bind(this.navigate, this),
						15
					);
				};

                return this;
            },

            /**
                        * The navigate() method is responsible for navigation between checkout step
                        * during checkout. You can add custom logic, for example some conditions
                        * for switching to your custom step
                        */
            navigate: function () {
                var self = this;
                //getPaymentInformation().done(function () {
                    self.isVisible(true);
               // });

            },


            navigateToNextStep: function () {
                stepNavigator.next();
            }
        });
    }
);