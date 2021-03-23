var config = {
    config: {
        mixins: {
            'Magento_Checkout/js/action/set-billing-address': {
                'Chottvn_Address/js/action/set-billing-address-mixin': true
            },
            'Magento_Checkout/js/action/set-shipping-information': {
                'Chottvn_Address/js/action/set-shipping-information-mixin': true
            },
            'Magento_Checkout/js/action/create-billing-address': {
                'Chottvn_Address/js/action/create-billing-address-mixin': true
            },
            'Magento_Checkout/js/action/create-shipping-address': {
                'Chottvn_Address/js/action/create-shipping-address-mixin': true
            },
            'Magento_Checkout/js/model/new-customer-address': {
                'Chottvn_Address/js/model/new-customer-address-mixin': true
            },
            'Magento_Checkout/js/action/place-order': {
                'Chottvn_Address/js/action/place-order-mixin': true
            },
            'Magento_Customer/js/model/customer/address': {
                'Chottvn_Address/js/model/customer/address-mixin': true
            },
            'Magento_Checkout/js/view/shipping-address/list': {
                'Chottvn_Address/js/view/shipping-address/list-mixin': true
            },
            'Magento_Checkout/js/view/shipping-address/address-renderer/default': {
                'Chottvn_Address/js/view/shipping-address/address-renderer/default-mixin': true
            },
            'Magento_Checkout/js/view/billing-address': {
                'Chottvn_Address/js/view/billing-address-mixin': true
            },
            'Magento_Checkout/js/action/select-billing-address': {
                'Chottvn_Address/js/action/select-billing-address-mixin': true
            },
            'Temando_Shipping/js/view/checkout/shipping-information/address-renderer/shipping': {
                'Chottvn_Address/js/view/checkout/shipping-information/address-renderer/shipping-mixin': true
            }
        }
    }
};