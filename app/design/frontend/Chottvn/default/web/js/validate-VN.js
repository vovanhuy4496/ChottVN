require([
    'jquery', // jquery Library
    'jquery/ui', // Jquery UI Library
    'jquery/validate', // Jquery Validation Library
    'mage/translate' // Magento text translate (Validation message translte as per language)
], function($) {
    $(document).ready(function() {
        var isCartOpen = false;

        $(".minicart-wrapper .action.showcart").on("click", function() {
            var href = $(".minicart-wrapper .action.showcart").attr("href");
            window.location.href = href;
        });

        $('.minicart-wrapper').hover(
            function() {
                // Phuoc add at 20200728 for disable hover icon cart show minicart on pages checkout, cart and price quote
                if ($(".checkout-index-index")[0] || $(".checkout-cart-index")[0] || $(".price_quote-index-index")[0]) {
                    return;
                }

                isCartOpen = true;
                cartOpen();
            },
            function() {
                isCartOpen = false;
                setTimeout(cartClose, 100);
            }
        );
        var cartContainer = $(".minicart-wrapper");

        function cartOpen() {
            // check empty cart , don't show
            if ($('.minicart-wrapper .counter-number span').html() == 0) {
                cartContainer.addClass("empty-products-cart");
            } else {
                cartContainer.removeClass("empty-products-cart");
            }

            // show / hide 
            cartContainer.fadeIn("slow");
            cartContainer.addClass("active");
            $(".action.showcart").addClass("active");
            $(".ui-front.mage-dropdown-dialog").show();

            // 
            if ($('.minicart-wrapper .dectect-mobile').html() == 'mobile') {
                if ($(".toggleButton.show-me-off").hasClass("off") == false) {
                    $('.compare-main .toggleButton').toggleClass('off');
                    $('.compare-main .index-changelog').toggleClass('bottom');
                }
            }
        }

        function cartClose() {
            if (isCartOpen)
                return;
            cartContainer.removeClass("active");
            if ($('.minicart-wrapper .counter-number span').html() == 0) {
                cartContainer.removeClass("empty-products-cart");
            }
            $(".action.showcart").removeClass("active");
            $(".ui-front.mage-dropdown-dialog").hide();
        }

        // Phuoc add at 20200728 for disable hover icon cart show minicart on pages checkout, cart and price quote
        $('a.action.showcart').click(function(e) {
            if ($(".checkout-index-index")[0] || $(".checkout-cart-index")[0] || $(".price_quote-index-index")[0]) {
                e.preventDefault();
                e.stopImmediatePropagation();
            }
        });
    });
    $.validator.addMethod(
        'validate-phone-VN',
        function(v) {
            return $.mage.isEmptyNoTrim(v) || (v.length > 9 &&
                /^(0)\d{9}$|^(84)\d{9}$/.test(v));
        },
        $.mage.__('Vui lòng nhập số điện thoại di động hợp lệ.')
    );
    $.validator.addMethod(
        'validate-bank-VN',
        function(v) {
            return $.mage.isEmptyNoTrim(v) || (v.length < 21 && /^[0-9a-zA-Z]+$/.test(v));
        },
        $.mage.__('Vui lòng nhập số tài khoản hợp lệ')
    );

    // validate date under 18
    $.validator.addMethod(
        'validate-date-under-18',
        function(v) {
            age = getAge(v);
            return $.mage.isEmptyNoTrim(v) || (age >= 18);
        },
        $.mage.__('Độ tuổi của bạn phải lớn hơn hoặc bằng 18 tuổi.')
    );

    function toDate(dateStr) {
        var parts = dateStr.split("/");
        return new Date(parts[2], parts[1] - 1, parts[0]);
    }

    function getAge(dateString) {
        var birthDate = toDate(dateString);
        var today = new Date();
        var age = today.getFullYear() - birthDate.getFullYear();

        var m = today.getMonth() - birthDate.getMonth();

        var d = today.getDate() - birthDate.getDate();

        if (age === 18 && m === 0 && d < 0) {
            age--;
        }
        return age;
    }

    $.validator.addMethod(
        'required-firstname',
        function(value) {
            return !$.mage.isEmptyNoTrim(value);
        }, $.mage.__('Vui lòng nhập Họ tên.')
    );
    $.validator.addMethod(
        'required-phone',
        function(value) {
            return !$.mage.isEmptyNoTrim(value);
        }, $.mage.__('Vui lòng nhập Số điện thoại.')
    );
    $.validator.addMethod(
        'required-new-pass',
        function(value) {
            return !$.mage.isEmptyNoTrim(value);
        }, $.mage.__('Vui lòng nhập Mật khẩu mới.')
    );
    $.validator.addMethod(
        'required-confirm-pass',
        function(value) {
            return !$.mage.isEmptyNoTrim(value);
        }, $.mage.__('Vui lòng nhập Xác nhận Mật khẩu Mới.')
    );
    $.validator.addMethod(
        'required-current-pass',
        function(value) {
            return !$.mage.isEmptyNoTrim(value);
        }, $.mage.__('Vui lòng nhập Mật khẩu hiện tại.')
    );
    $.validator.addMethod(
        'chottvn-validate-password-length',
        function(v) {
            return !$.mage.isEmptyNoTrim(v) && v.length <= 32 && v.length >= 6;
        }, $.mage.__('Vui lòng nhập mật khẩu từ 6 đến 32 ký tự')
    );
});