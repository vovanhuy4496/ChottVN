define([
    'jquery',
    'underscore',
    'uiComponent',
    'Magento_Checkout/js/model/quote',
    'Amasty_Conditions/js/action/recollect-totals',
    'Magento_Checkout/js/model/shipping-service',
    'Magento_Checkout/js/model/shipping-rate-processor/new-address',
    'Magento_SalesRule/js/view/payment/discount',
    'rjsResolver'
], function ($, _, Component, quote, recollect, shippingService, shippingProcessor, discount, resolver) {
    'use strict';

    return Component.extend({
        previousShippingMethodData: {},
        billingAddressCountry: null,
        city: null,
        street: null,
        isPageLoaded: false,
        initialize: function () {
            this._insertPolyfills();
            this._super();

            resolver(function() {
                this.isPageLoaded = true;
            }.bind(this));

            discount().isApplied.subscribe(function () {
                recollect(true);
            });

            quote.shippingAddress.subscribe(function (newShippingAddress) {
                // while page is loading do not recollect, should be recollected after shipping rates
                // for avoid extra requests to server
                if (this.isPageLoaded && this._isNeededRecollectShipping(newShippingAddress, this.city, this.street)) {
                    this.city = newShippingAddress.city;
                    this.street = newShippingAddress.street;
                    if (newShippingAddress) {
                        recollect();
                    }
                }
            }.bind(this));

            quote.billingAddress.subscribe(function (newBillAddress) {
                if (this._isNeededRecollectBilling(newBillAddress, this.billingAddressCountry)) {
                    this.billingAddressCountry = newBillAddress.countryId;
                    if (!this._isVirtualQuote()
                        && (quote.shippingAddress() && newBillAddress.countryId !== quote.shippingAddress().countryId)
                    ) {
                        shippingProcessor.getRates(quote.shippingAddress());
                    }
                    recollect();
                }
            }.bind(this));

            //for invalid shipping address update
            shippingService.getShippingRates().subscribe(function (rates) {
                if (!this._isVirtualQuote()) {
                    recollect();
                }
            }.bind(this));

            quote.paymentMethod.subscribe(function (newMethodData) {
                recollect();
            }, this);

            quote.shippingMethod.subscribe(this.storeOldMethod, this, "beforeChange");
            quote.shippingMethod.subscribe(this.recollectOnShippingMethod, this);

            return this;
        },


        /**
         * Store before change shipping method, because sometimes shipping methods updates always (not by change)
         *
         * @param {Object} oldMethod
         */
        storeOldMethod: function (oldMethod) {
            this.previousShippingMethodData = oldMethod;
        },

        recollectOnShippingMethod: function (newMethodData) {
            if (!_.isEqual(this.previousShippingMethodData, newMethodData)) {
                recollect();
            }
        },

        _isVirtualQuote: function () {
            return quote.isVirtual()
                || window.checkoutConfig.activeCarriers && window.checkoutConfig.activeCarriers.length === 0;
        },

        _isNeededRecollectShipping: function (newShippingAddress, city, street) {
            return !this._isVirtualQuote()
                && (
                    newShippingAddress
                    && (newShippingAddress.city || newShippingAddress.street)
                    && (newShippingAddress.city != city || !_.isEqual(newShippingAddress.street, street)));
        },

        _isNeededRecollectBilling: function (newBillAddress, billingAddressCountry) {
            return this.isPageLoaded
                && newBillAddress
                && newBillAddress.countryId
                && newBillAddress.countryId != billingAddressCountry
        },

        _insertPolyfills: function () {
            if (typeof Object.assign != 'function') {
                // Must be writable: true, enumerable: false, configurable: true
                Object.defineProperty(Object, "assign", {
                    value: function assign(target, varArgs) { // .length of function is 2
                        'use strict';
                        if (target == null) { // TypeError if undefined or null
                            throw new TypeError('Cannot convert undefined or null to object');
                        }

                        var to = Object(target);

                        for (var index = 1; index < arguments.length; index++) {
                            var nextSource = arguments[index];

                            if (nextSource != null) { // Skip over if undefined or null
                                for (var nextKey in nextSource) {
                                    // Avoid bugs when hasOwnProperty is shadowed
                                    if (Object.prototype.hasOwnProperty.call(nextSource, nextKey)) {
                                        to[nextKey] = nextSource[nextKey];
                                    }
                                }
                            }
                        }
                        return to;
                    },
                    writable: true,
                    configurable: true
                });
            }
        }
    });
});
