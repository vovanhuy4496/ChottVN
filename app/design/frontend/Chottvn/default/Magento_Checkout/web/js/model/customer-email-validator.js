/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 * 
 */

define([
    'jquery',
    'Magento_Customer/js/model/customer',
    'mage/validation'
], function ($, customer) {
    'use strict';

    return {
        /**
         * Validate checkout agreements
         *
         * @returns {Boolean}
         */
        validate: function () {
            var emailValidationResult = customer.isLoggedIn(),
                loginFormSelector = 'form[data-role=email-with-possible-login]';

            if (!customer.isLoggedIn()) {
                $(loginFormSelector).validation();
                // Dan change form login username > login[username]
                //emailValidationResult = Boolean($(loginFormSelector + ' input[name=username]').valid());
                emailValidationResult = Boolean($(loginFormSelector + ' #customer-email').valid());                
            }

            return emailValidationResult;
        }
    };
});
