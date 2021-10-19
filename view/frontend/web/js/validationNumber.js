define([
    'jquery',
    'jquery/ui',
    'jquery/validate',
    'mage/translate'
], function($){
    'use strict';
    return function() {
        $.validator.addMethod(
            "validationAddi",
            function(value) {
                var validator = this;
                if(window.hasOwnProperty('checkoutConfig')){
                    var country = window.checkoutConfig.payment.addi.country;
                    if(country == 'CO'){
                        let ok = /^\d+$/.test(value);
                        if(!ok){
                            validator.validateMessage =
                             $.mage.__('Please enter a valid number in this field.');
                            return false;
                        }
                    }else{
                        let ok = /^[\d\-.]+$/.test(value);
                        if(!ok){
                            validator.validateMessage =
                                $.mage.__('Please use only Numbers, Hyphens or Dots.');
                            return false;
                        }
                    }
                }
                return true;
            },
            $.mage.__('Please use only Numbers, Hyphens o Dots')
        );
    }
});
