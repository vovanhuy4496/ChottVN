require([
    'jquery', // jquery Library
    'mage/translate',
    'jquery/ui', // Jquery UI Library
    'jquery/validate', // Jquery Validation Library
], function($, $t) {
    $(document).ready(function() {
        var loginForm = $("#login-form");
        var formValidate = $("#form-validate");
        var orderTrackingForm = $("#oar-widget-orders-and-returns-form");

        loginForm.on('submit', function(event) {
            if (loginForm.validation('isValid') === false) {
                var validator = loginForm.validate();
                $.each(validator.errorMap, function(index, value) {
                    $.toaster({ priority: 'danger', message: $t(value) });
                });
            }
        });

        formValidate.on('submit', function(event) {
            if (formValidate.validation('isValid') === false) {
                var validator = formValidate.validate();
                $.each(validator.errorMap, function(index, value) {
                    $.toaster({ priority: 'danger', message: $t(value) });
                });
            }
        });

        orderTrackingForm.on('submit', function(event) {
            if (orderTrackingForm.validation('isValid') === false) {
                var validator = orderTrackingForm.validate();
                $.each(validator.errorMap, function(index, value) {
                    $.toaster({ priority: 'danger', message: $t(value) });
                });
            }
        });
    });

    $(document).ajaxComplete(function(event, xhr, options) {
        // Lazy Image Amasty      
        if (typeof $("img[data-amsrc]").lazy === 'function') {
            $("img[data-amsrc]").lazy({ "attribute": "data-amsrc" });
        }
    });

    $(window).on('load', function() {
        // Lazy Image
        if (typeof $("img[data-amsrc]").lazy === 'function') {
            $("img[data-amsrc]").lazy({ "attribute": "data-amsrc" });
        }
    });
});