/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'jquery',
    'uiComponent',
    'Magento_Customer/js/customer-data',
    'underscore',
    'jquery/jquery-storageapi'
], function($, Component, customerData, _) {
    'use strict';

    return Component.extend({
        defaults: {
            cookieMessages: [],
            messages: []
        },

        /**
         * Extends Component object by storage observable messages.
         */
        initialize: function() {
            this._super();

            this.cookieMessages = _.unique($.cookieStorage.get('mage-messages'), 'text');
            this.messages = customerData.get('messages').extend({
                disposableCustomerData: 'messages'
            });
            // Phuoc add 20200827 to show toast for all forms, popup
            this.showToastAllForms();

            // Force to clean obsolete messages
            if (!_.isEmpty(this.messages().messages)) {
                customerData.set('messages', {});
            }

            $.cookieStorage.set('mage-messages', '');
        },

        // Phuoc add 20200827 to show toast for all forms, popup
        showToastAllForms: function() {
            if (typeof this.messages().messages !== 'undefined' && !_.isEmpty(this.messages().messages)) {
                this.showToast(this.messages().messages);
            }


            if (typeof this.cookieMessages !== 'undefined' && !_.isEmpty(this.cookieMessages)) {
                this.showToast(this.cookieMessages);
            }
        },

        // Phuoc add 20200901 to show toast based on type
        showToast(messages) {
            $.each(messages, function(index, value) {
                // console.log(value);
                if (value.text && value.text.length > 0) {
                    let priority = 'info';
                    if (value.type == 'error'){ priority = 'danger'; }
                    else if(value.type == 'success'){ priority = 'success'; }
                    else if(value.type == 'warning'){ priority = 'warning'; }
                    
                    $.toaster({ priority: priority, message: value.text });
                }
            });
        }
    });
});