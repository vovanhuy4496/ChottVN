/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'jquery',
    'mage/url',
    'Magento_Ui/js/model/messageList',
    'mage/translate'
], function($, url, globalMessageList, $t) {
    'use strict';

    return {
        /**
         * @param {Object} response
         * @param {Object} messageContainer
         */
        process: function(response, messageContainer) {
            var error;

            messageContainer = messageContainer || globalMessageList;

            if (response.status == 401) { //eslint-disable-line eqeqeq
                window.location.replace(url.build('customer/account/login/'));
            } else {
                try {
                    error = JSON.parse(response.responseText);
                } catch (exception) {
                    error = {
                        message: $t('Something went wrong with your request. Please try again later.')
                    };
                }
                var message = error.message;
                console.log(error);

                if (error.parameters) {
                    // ghi log de check loi
                    console.log(error);
                } else {
                    if (message !== 'Something went wrong with your request. Please try again later.') {
                        $.toaster({ title: $t('Notice'), priority: 'danger', message: message });
                    }
                }
            }
        }
    };
});