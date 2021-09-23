define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'addi',
                component: 'Addi_Payment/js/view/payment/method-renderer/addi-method'
            }
        );
        return Component.extend({});
    }
);
