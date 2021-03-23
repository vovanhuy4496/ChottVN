/*
* Amasty Advanced review widget type slide initialization
*/

define([
    'jquery',
    'Amasty_Base/vendor/slick/slick.min',
], function ($) {
    'use strict';

    $.widget('mage.amReviewSlider', {
        options: {
            slidesToShow: 1,
            slidesToScroll: 1,
            infinite: true,
            responsive: [
                {
                    breakpoint: 768,
                    settings: {
                        arrows: false,
                    }
                }
            ],
            selectors: {
                sliderItem: '[data-amreview-js="slider-item"]',
                readMore: '[data-amreview-js="readmore"]',
                active: '-active'
            }
        },

        _create: function () {
            var self = this;
            self.element.slick(this.options);

            self.element.on('beforeChange', function (event, slick, currentSlideIdx) {
                self.collapseItem(slick, currentSlideIdx);
            });
        },

        /*
        * Collapsing review card to default size on slide
        */
        collapseItem: function (slick, currentSlideIdx) {
            var options = this.options,
                currenSlide = $(slick.$slides[currentSlideIdx]),
                readMoreButton = currenSlide.find(options.selectors.readMore);

            if (readMoreButton.hasClass(options.selectors.active)) {
                readMoreButton.click();
            }
        }
    });

    return $.mage.amReviewSlider;
});
