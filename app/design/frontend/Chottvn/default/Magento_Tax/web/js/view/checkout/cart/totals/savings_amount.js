/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */

define([
    'Magento_Tax/js/view/checkout/summary/savings_amount'
], function(Component) {
    'use strict';

    return Component.extend({
        /**
         * @override
         */
        isDisplayed: function() {
            return true;
        }
    });
});