var config = {
    map: {
        "*": {
            "amrevloader": "Amasty_AdvancedReview/js/components/amrev-loader",
            "amReview": "Amasty_AdvancedReview/js/amReview",
            "amReviewSlider": "Amasty_AdvancedReview/js/widget/amReviewSlider",
            "amProductReviews": "Amasty_AdvancedReview/js/widget/amProductReviews"
        }
    },
    config: {
        mixins: {
            'Magento_Review/js/view/review': {
                'Amasty_AdvancedReview/js/view/review': true
            }
        }
    }
};