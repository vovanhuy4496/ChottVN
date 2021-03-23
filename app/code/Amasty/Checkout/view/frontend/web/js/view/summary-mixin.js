define(['mage/translate'], function($t) {
    'use strict';

    return function(Component) {
        return Component.extend({
            getNameSummary: function() {
                return $t('Order summary');
                // return window.checkoutConfig.quoteData.block_info.block_order_summary['value'];
            }
        });
    }
});