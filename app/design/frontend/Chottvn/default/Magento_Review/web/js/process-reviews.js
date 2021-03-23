/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery'
], function($) {
    'use strict';

    /**
     * @param {String} url
     * @param {*} fromPages
     */
    function processReviews(url, fromPages) {
        $.ajax({
            url: url,
            cache: true,
            dataType: 'html',
            showLoader: false,
            loaderContext: $('.product.data.items')
        }).done(function(data) {
            $('#product-review-container').html(data).trigger('contentUpdated');
            $('[data-role="product-review"] .pages a').each(function(index, element) {
                $(element).click(function(event) { //eslint-disable-line max-nested-callbacks
                    processReviews($(element).attr('href'), true);
                    event.preventDefault();
                });
            });
        }).complete(function() {
            if (fromPages == true) { //eslint-disable-line eqeqeq
                $('html, body').animate({
                    scrollTop: ($('#flag-review').offset().top - 150)
                }, 300);
            }
        });
    }

    return function(config) {
        /*var reviewTab = $(config.reviewsTabSelector),
            requiredReviewTabRole = 'tab';

        if (reviewTab.attr('role') === requiredReviewTabRole && reviewTab.hasClass('active')) {
            processReviews(config.productReviewUrl, location.hash === '#reviews');
        } else {
            reviewTab.one('beforeOpen', function () {
                processReviews(config.productReviewUrl);
            });
        }*/
        var fromPages = false;
        if(location.hash === '#reviews' || location.hash === '#flag-review' || location.hash === '#review-form'){
            fromPages = true;
            processReviews(config.productReviewUrl, fromPages);
        }else{
            processReviews(config.productReviewUrl);
        }
        
        // $(function() {
        //     $('.product-info-main .reviews-actions a').click(function(event) {
        //         // Make sure this.hash has a value before overriding default behavior
        //         if (this.hash !== "") {
        //             // Prevent default anchor click behavior
        //             event.preventDefault();

        //             // Store hash
        //             var flag = $('#flag-review');

        //             // Using jQuery's animate() method to add smooth page scroll
        //             // The optional number (800) specifies the number of milliseconds it takes to scroll to the specified area
        //             $('html, body').animate({
        //                 scrollTop: flag.offset().top
        //             }, 800, function() {

        //                 // Add hash (#) to URL when done scrolling (default click behavior)
        //                 // window.location.hash = hash;
        //             });
        //         } // End if
        //         // var anchor, addReviewBlock;

        //         // event.preventDefault();
        //         // anchor = $(this).attr('href').replace(/^.*?(#|$)/, '');
        //         // addReviewBlock = $('#reviews #' + anchor);

        //         // if (addReviewBlock.length) {
        //         //     $('.product.data.items [data-role="content"]').each(function(index) { //eslint-disable-line
        //         //         if (this.id == 'reviews') { //eslint-disable-line eqeqeq
        //         //             $('.product.data.items').tabs('activate', index);
        //         //         }
        //         //     });
        //         //     $('html, body').animate({
        //         //         scrollTop: addReviewBlock.offset().top - 50
        //         //     }, 300);
        //         // }

        //     });
        // });
    };
});