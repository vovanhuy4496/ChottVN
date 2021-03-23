define(
    [
        'jquery',
        'underscore',
        'uiComponent',
        'ko',
        'uiRegistry',
        'Magento_Checkout/js/action/select-billing-address',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Checkout/js/model/checkout-data-resolver',
        'Amasty_Checkout/js/action/is-equal-ignore-functions',
        'Amasty_Checkout/js/model/payment-validators/shipping-validator',
        'Amasty_Checkout/js/model/address-form-state',
        'Magento_Checkout/js/model/shipping-service',
        'Magento_Checkout/js/model/shipping-rate-registry',
        'Magento_Checkout/js/model/shipping-rate-processor/customer-address',
        'Magento_Checkout/js/model/shipping-rate-processor/new-address',
    ],
    function(
        $,
        _,
        Component,
        ko,
        registry,
        selectBillingAddress,
        quote,
        paymentValidatorRegistry,
        checkoutDataResolver,
        isEqualIgnoreFunctions,
        shippingValidator,
        addressFormState,
        shippingService,
        rateRegistry,
        customerAddressProcessor,
        newAddressProcessor
    ) {
        'use strict';
        var detectMobile = window.detectMobile;
        var detectTablet = window.detectTablet;

        return Component.extend({
            defaults: {
                mappingBlockName: {
                    block_customer_info: 'checkout.steps.shipping-step.shippingAddress',
                    block_vat_invoice_required: 'checkout.steps.shipping-step.shippingAddress',
                    block_shipping_address: 'checkout.steps.shipping-step.shippingAddress',
                    block_shipping_method: 'checkout.steps.shipping-step.shippingAddress',
                    block_delivery: 'checkout.steps.shipping-step.amcheckout-delivery-date',
                    block_payment_method: 'billing-step',
                    block_order_summary: 'sidebar'
                },
                orderedBlocks: {},
                shippingRates: shippingService.getShippingRates(),
            },
            detectMobile: detectMobile,
            detectTablet: detectTablet,

            /** @inheritdoc */
            initialize: function() {
                this._super();
                var blockInfo = window.checkoutConfig.quoteData.block_info;

                var block_customer_info = { sort_order: "5", value: "Customer Info" };
                var block_vat_invoice_required = { sort_order: "6", value: "Invoice information" };
                blockInfo = this.addToObject(blockInfo, 'block_customer_info', block_customer_info);
                blockInfo = this.addToObject(blockInfo, 'block_vat_invoice_required', block_vat_invoice_required);

                this.replaceEqualityComparer();

                $.each(blockInfo, function(key, item) {
                    var sortOrder = 0;

                    if (item.hasOwnProperty('sort_order')) {
                        sortOrder = item.sort_order;
                    }

                    while (this.orderedBlocks.hasOwnProperty(sortOrder)) {
                        sortOrder++;
                    }

                    this.orderedBlocks[sortOrder] = {
                        blockCode: this.mappingBlockName[key],
                        blockKey: key
                    };
                }.bind(this));

                $(function() {
                    $('body').on("click", '#place-order-trigger', function() {
                        $(".payment-method._active").find('.action.primary.checkout').trigger('click');
                    });
                    // $('body').on("click", '#vat_invoice_required_ctt', function() {
                    //     // if checked 'Yêu cầu xuất hóa đơn'
                    //     var checked_vat = $('input[name="vat_invoice_required_ctt"]').is(':checked');
                    //     if (checked_vat) {
                    //         $('div#check-vat-invoice-required').show();
                    //         $('input[name="vat_invoice_required"]').prop("checked", true);
                    //         $('input[name="vat_invoice_required"]').trigger('click');
                    //     } else {
                    //         $('div#check-vat-invoice-required').hide();
                    //         $('input[name="vat_invoice_required"]').prop("checked", false);
                    //         $('input[name="vat_invoice_required"]').trigger('click');
                    //     }
                    // });
                    // $('body').on("keyup", 'input[name^="vat_company_ctt"]', function() {
                    //     $('input[name="vat_company"]').val($('input[name="vat_company_ctt"]').val()).trigger("change");
                    // });
                    // $('body').on("keyup", 'input[name^="vat_address_ctt"]', function() {
                    //     $('input[name="vat_address"]').val($('input[name="vat_address_ctt"]').val()).trigger("change");
                    // });
                    // $('body').on("keyup", 'input[name^="vat_number_ctt"]', function() {
                    //     $('input[name="vat_number"]').val($('input[name="vat_number_ctt"]').val()).trigger("change");
                    // });
                    // $('body').on("keyup", 'input[name^="vat_contact_name_ctt"]', function() {
                    //     $('input[name="vat_contact_name"]').val($('input[name="vat_contact_name_ctt"]').val()).trigger("change");
                    // });
                    // $('body').on("keyup", 'input[name^="vat_contact_phone_number_ctt"]', function() {
                    //     $('input[name="vat_contact_phone_number"]').val($('input[name="vat_contact_phone_number_ctt"]').val()).trigger("change");
                    // });
                    // $('body').on("keyup", 'input[name^="vat_contact_email_ctt"]', function() {
                    //     $('input[name="vat_contact_email"]').val($('input[name="vat_contact_email_ctt"]').val()).trigger("change");
                    // });
                });
            },

            addToObject: function(obj, key, value, index) {
                // Create a temp object and index variable
                var temp = {};
                var i = 0;

                // Loop through the original object
                for (var prop in obj) {
                    if (obj.hasOwnProperty(prop)) {

                        // If the indexes match, add the new item
                        if (i === index && key && value) {
                            temp[key] = value;
                        }

                        // Add the current item in the loop to the temp obj
                        temp[prop] = obj[prop];

                        // Increase the count
                        i++;

                    }
                }

                // If no index, add to the end
                if (!index && key && value) {
                    temp[key] = value;
                }

                return temp;
            },

            getSortedBlock: function(index) {
                var orderedBlock = this.orderedBlocks[index];

                if (orderedBlock.blockCode === 'billing-step') {
                    return this.getChild('steps').getChild('billing-step');
                } else if (orderedBlock.blockCode === 'sidebar') {
                    return this.getChild('sidebar');
                }

                var requestComponent = this.requestComponent(orderedBlock.blockCode);

                if (orderedBlock.blockKey === 'block_shipping_address' && requestComponent()) {
                    requestComponent().template = 'Amasty_Checkout/onepage/shipping/address';
                } else if (orderedBlock.blockKey === 'block_shipping_method' && requestComponent()) {
                    requestComponent().template = 'Amasty_Checkout/onepage/shipping/methods';
                } else if (orderedBlock.blockKey === "block_customer_info" && requestComponent()) {
                    requestComponent().template = 'Amasty_Checkout/onepage/shipping/customer-info';
                } else if (orderedBlock.blockKey === "block_vat_invoice_required" && requestComponent()) {
                    requestComponent().template = 'Amasty_Checkout/onepage/shipping/vat-invoice-required';
                }

                return requestComponent;
            },

            getBlockIndex: function(index) {
                if (this.orderedBlocks[index].blockCode === 'sidebar') {
                    return '-summary';
                }
            },

            initObservable: function() {
                this._super().observe({
                    isAmazonLoggedIn: null
                });

                if (!quote.isVirtual()) {
                    quote.shippingAddress.subscribe(this.shippingAddressObserver.bind(this));
                    paymentValidatorRegistry.registerValidator(shippingValidator);
                }

                registry.get('checkout.steps.billing-step.payment', function(component) {
                    //initialize payment information
                    component.isVisible(true);
                    component.navigate();
                });

                this.shippingRates.subscribe(function(rates) {
                    if (rates != undefined) {
                        if (rates.length < 2) {
                            $("#opc-shipping_method").parent().addClass("hidden");
                        } else {
                            $("#opc-shipping_method").parent().removeClass("hidden");
                        }

                    }
                });

                return this;
            },

            shippingAddressObserver: function(address) {
                if (!address) {
                    return;
                }

                this.isAccountLoggedInAmazon();

                this.setShippingToBilling(address);
            },

            /**
             * fix default "My billing and shipping address are the same" checkbox behaviour
             *
             * @param {object|null} address
             */
            setShippingToBilling: function(address) {
                if (!address) {
                    return;
                }

                if (!address.canUseForBilling()) {
                    checkoutDataResolver.resolveBillingAddress();

                    return;
                }

                if (_.isNull(address.street) || _.isUndefined(address.street)) {
                    // fix: some payments (paypal) takes street.0 without checking
                    address.street = [];
                }

                if (addressFormState.isBillingSameAsShipping()) {
                    selectBillingAddress(address);
                }
            },

            /**
             * Used in templates
             *
             * @param {string} name
             * @returns {observable}
             */
            requestComponent: function(name) {
                var observable = ko.observable();

                registry.get(name, function(summary) {
                    observable(summary);
                }.bind(this));

                return observable;
            },

            /**
             * Set customer Amazon logged in status and hide billing address if customer logged in Amazon
             */
            isAccountLoggedInAmazon: function() {
                if (require.defined('Amazon_Payment/js/model/storage')) {
                    if (this.isAmazonLoggedIn()) {
                        $('.checkout-billing-address').hide();
                    } else {
                        require(['Amazon_Payment/js/model/storage'], function(amazonStorage) {
                            amazonStorage.isAmazonAccountLoggedIn.subscribe(function(isLoggedIn) {
                                this.isAmazonLoggedIn(isLoggedIn);
                            }, this);
                            this.isAmazonLoggedIn(amazonStorage.isAmazonAccountLoggedIn())
                        }.bind(this));
                    }
                }
            },

            /**
             * Main observables equality comparer replacement
             * @returns {void}
             */
            replaceEqualityComparer: function() {
                quote.shippingAddress.equalityComparer = isEqualIgnoreFunctions;
                quote.billingAddress.equalityComparer = isEqualIgnoreFunctions;
                quote.shippingMethod.equalityComparer = isEqualIgnoreFunctions;
                quote.paymentMethod.equalityComparer = isEqualIgnoreFunctions;
            },

            getDetectMobile: function() {
                // neu la mobile
                if (this.detectMobile > 0 && this.detectTablet == 0) {
                    return true;
                }
                return null;
            },

            /**
             * @return {*}
             */
            isShowProductPromo: function() {
                var productPromoData = this.getProductPromo();
                return productPromoData.length > 0;
            },

            /**
             * @return {*}
             */
            getProductPromo: function() {
                var data = window.getProductPromo;
                var decodeData = _.unescape(data);
                return decodeData;
            }
        });
    });