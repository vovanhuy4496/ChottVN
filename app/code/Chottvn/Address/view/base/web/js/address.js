/*jshint jquery:true*/
define([
    "jquery"
], function ($) {
    "use strict";

    $.widget('mage.addressData', {
        options: {
            ajaxUrl: null
        },

        /**
         * Get township data.
         * @private
         */
        _create: function() {
            this.getTownshipData();
        },

        getTownshipData: function() {
            let self = this,
                locale = window.locale,
                townshipData = localStorage.getItem("townshipData"),
                updateTime = localStorage.getItem("updateTime"),
                today = new Date().getTime(),
                updateRange = (24*60*60),
                flag = true;

            if ((today - updateTime) > updateRange) {
                flag = true;
            } else if (localStorage.getItem("updateFlag") != this.options.updateFlag) {
                flag = true;
            }

            if (townshipData == undefined || 
                JSON.parse(townshipData)[locale] == undefined ||
                flag == true
            ) {
                $.getJSON(this.options.ajaxUrl, function(data) {
                    if(Object.keys(data).length > 0) {
                        let result = (townshipData != null) ? JSON.parse(townshipData) : {};
                        result[locale] = data.township;
                        localStorage.setItem("townshipData", JSON.stringify(result));
                        localStorage.setItem("updateTime", today);
                        if (self.options.updateFlag != undefined) {
                            localStorage.setItem("updateFlag", self.options.updateFlag);
                        }

                        // if data township not equals request ajax => update checkout-data shipping = null
                        if(JSON.stringify(result) != townshipData){
                            // console.log('data change');
                            var mage_cache_storage = JSON.parse(localStorage.getItem("mage-cache-storage"));
                            if(mage_cache_storage !== null){
                                if(typeof mage_cache_storage['checkout-data'] !== 'undefined'){
                                    var checkout_data = mage_cache_storage['checkout-data'];
                                    checkout_data['shippingAddressFromData'] = null;
                                    localStorage.setItem("mage-cache-storage", JSON.stringify(mage_cache_storage));
                                    
                                    
                                    location.reload();
                                    return false;
                                    // $('input[name="region"]').val('');
                                    // $('select[name="custom_attributes[city_id]"]').val('');
                                    // $('select[name="custom_attributes[township_id]"]').val('');
                                    // $('input[name="custom_attributes[township]"]').val('');
                                    // $('select[name="city_id"]').val('');
                                    // $('input[name="township"]').val('');
                                    // $('select[name="township_id"]').val('');
                                    // console.log('update data');
                                }
                            }
                        }
                    }
                });
            }
        }
    });

    return $.mage.addressData;
});