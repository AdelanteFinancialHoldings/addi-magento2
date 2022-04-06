define(
    [
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'mage/url',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/model/payment/additional-validators',
        'mage/storage',
        'mage/validation',
        'domReady!',
        'mage/translate'
    ],
    function ($,Component, url,fullScreenLoader,additionalValidators,storage) {
        'use strict';
        return Component.extend({
            redirectAfterPlaceOrder: false,

            defaults: {
                template: 'Addi_Payment/payment/addi',
                documentNumber:''
            },
            /** @inheritdoc */
            initObservable: function () {
                this._super()
                    .observe('documentNumber');

                return this;
            },
            getMailingAddress: function () {
                return window.checkoutConfig.payment.checkmo.mailingAddress;
            },
            getInstructions: function () {
                return window.checkoutConfig.payment.addi.instructions;
            },
            getImage: function () {
                return window.checkoutConfig.payment.addi.image;
            },
            getAddiDiscount: function () {
                return window.checkoutConfig.payment.addi.discount;
            },
            getLabel: function () {
                return window.checkoutConfig.payment.addi.label;
            },
            showDiscountLabel:function(){
                return (parseInt(window.checkoutConfig.payment.addi.discount)>0);
            },
            getLabelDiscount: function () {
                let label = window.checkoutConfig.payment.addi.label_discount;
                return label.replace('%1', this.getAddiDiscount());
            },
            /**
             * @return {Object}
             */
            getData: function () {
                return {
                    'method': this.item.method,
                    'additional_data': {
                        'document_number': $('#document_number').val(),
                    }
                };
            },
            /**
             * @return {jQuery}
             */
            validate: function () {
                var form = '#co-payment-form';

                return $(form).validation() && $(form).validation('isValid');
            },

            afterPlaceOrder: function () {
                fullScreenLoader.startLoader();

                window.location.replace(url.build(window.checkoutConfig.payment.addi.redirect_pay));
            }
        });
    }
);
