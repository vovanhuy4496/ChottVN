define([
    'jquery',
    'Magento_Ui/js/modal/modal',
    'Amasty_Base/vendor/slick/slick.min',
    'Amasty_AdvancedReview/vendor/fancybox/jquery.fancybox.min',
    'mage/translate'
], function ($) {
    'use strict';

    $.widget('mage.amReview', {
        options: {
            slidesToShow: 3,
            slidesToScroll: 3,
            centerMode: false,
            variableWidth: false,
            responsive: [
                {
                    breakpoint: 460,
                    settings: {
                        slidesToShow: 2,
                        slidesToScroll: 2,
                    }
                },
                {
                    breakpoint: 360,
                    settings: {
                        slidesToShow: 1,
                        slidesToScroll: 1,
                    }
                }
            ],
            selectors: {
                imageContainer: '[data-amreview-js="review-images"]',
                sliderItem: '[data-amreview-js="slider-item"]',
                readMore: '[data-amreview-js="readmore"]',
                reviewTextLess: '[data-amreview-js="text-less"]',
                reviewTextLong: '[data-amreview-js="text-long"]',
                hide: 'hidden',
                active: '-active'
            }
        },

        _create: function () {
            var self = this;

            self.element.on('click', self.options.selectors.readMore, function (e) {
                e.preventDefault();
                self.toggleDescr(this);
            });

            $('[data-amreview-js="show-more"]').on('click', function () {
                $('[data-amreview-js="percent"]').toggle();
                $('[data-amreview-js="summary-details"]').toggle();
            });

            // Fix problem with slick init
            $('#tab-label-reviews').on('click', function () {
                $('.amreview-images.slick-initialized').slick('setPosition');
            });

            this.initSlider();
        },

        initSlider: function () {
            var self = this,
                slidesToShow = $(window).width() > 768 ? self.options.slidesToShow : 1,
                $imageContainer = self.element.find(self.options.selectors.imageContainer);

            if ($imageContainer.length) {
                $.each($imageContainer, function () {
                    var $element = $(this);

                    $element.find('a').fancybox({
                        loop: true,
                        toolbar: false,
                        baseClass: 'amrev-fancybox-zoom'
                    });

                    if ($element.find(self.options.selectors.sliderItem).length > slidesToShow && self.options.slidesToShow) {
                        $element.slick(self.options);
                        $element.slick('resize');
                    }
                });
            }
        },

        toggleDescr: function (button) {
            var options = this.options,
                buttonText = $(button).text() ===  $.mage.__('Show more') ? $.mage.__('Show less') : $.mage.__('Show more'),
                container = $(button).parent(),
                lessDescr = container.find(options.selectors.reviewTextLess),
                longDescr = container.find(options.selectors.reviewTextLong);

            lessDescr.toggleClass(options.selectors.active);
            longDescr.toggleClass(options.selectors.hide);
            $(button).toggleClass(options.selectors.active).text(buttonText);
        }
    });

    return $.mage.amReview;
});
