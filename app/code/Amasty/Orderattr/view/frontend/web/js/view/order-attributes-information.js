/*jshint browser:true jquery:true*/
define(
    [
        'jquery',
        'uiRegistry',
        'underscore',
        'uiComponent',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/step-navigator'
    ],
    function($, registry, _, Component, quote, stepNavigator) {
        'use strict';

        return Component.extend({
            defaults: {
                deps : 'amastyCheckoutProvider',
                template: 'Amasty_Orderattr/order-attributes-information',
                hideEmpty: true,
                collectPlaces: []
            },

            isVisible: function() {
                return !quote.isVirtual() && this.isPaymentStepVisible();
            },
            isPaymentStepVisible: function () {
                var steps = stepNavigator.steps();

                if (!_.isUndefined(_.where(steps, {'code' : 'payment'})[0])) {
                    return _.where(steps, {'code' : 'payment'})[0].isVisible();
                }

                return false;
            },

            getOrderAttributes: function () {
                var attributes = [];

                _.each(this.collectPlaces, function(place) {
                    var container = registry.filter('index = ' + place);

                    if (container.length) {
                        _.each(container[0].elems(), function(elem) {
                            if (elem.visible()) {
                                var item = this.getAttributeDataFromElement(elem);
                                if (item) {
                                    attributes.push(item);
                                }
                            }
                        }.bind(this) );
                    }
                }.bind(this));

                return attributes;
            },

            getAttributeDataFromElement: function (elem) {
                var item = [];

                switch (elem.dataType) {
                    case 'boolean':
                        if (!this.hideEmpty || (this.hideEmpty && elem.value() != "-1")) {
                            item['value'] = elem.indexedOptions[elem.value()].label;
                        }
                        break;
                    case 'html':
                            return false;
                        break;
                    case 'select':
                    case 'radios':
                        if (!this.hideEmpty || (this.hideEmpty && elem.value() != "")) {
                            item['value'] = elem.indexedOptions[elem.value()].label;
                        }
                        break;
                    case 'checkboxes':
                    case 'multiselect':
                        if (!this.hideEmpty || (this.hideEmpty && elem.value() != "")) {
                            if (typeof elem.value() === 'object') {
                                item['value'] = '';
                                _.each(elem.value(), function (e) {
                                    item['value'] += ', ' + elem.indexedOptions[e].label;
                                });
                                item['value'] = item['value'].substr(2);
                            } else {
                                item['value'] = elem.indexedOptions[elem.value()].label;
                            }
                        }
                        break;
                    default:
                        if (!this.hideEmpty || (this.hideEmpty && elem.value() != "")) {
                            item['value'] = elem.value();
                        }
                        break;
                }

                if ('value' in item) {
                    item['label'] = elem.label;
                } else {
                    item = false;
                }

                return item;
            }
        });
    }
);
