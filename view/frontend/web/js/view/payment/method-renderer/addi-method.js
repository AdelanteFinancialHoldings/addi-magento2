define(
    [
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/model/payment/additional-validators',
        'mage/storage',
        'mage/validation',
        'domReady!'
    ],
    function ($,Component,fullScreenLoader,additionalValidators,storage) {
        'use strict';
        return Component.extend({
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
            }
        });
    }
);