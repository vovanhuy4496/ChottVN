var config = {
    map: {
        '*': {
            'Magento_Checkout/template/form/element/email.html': 
            	'Chottvn_SigninPhoneNumber/template/form/element/email.html'
	    	//,'Magento_Checkout/js/action/place-order': 
            	//'Chottvn_SigninPhoneNumber/js/action/place-order'
        }
  	},
  	config: {
        mixins: {
        	'Magento_Checkout/js/action/place-order': {
                'Chottvn_SigninPhoneNumber/js/action/place-order-mixin': true
            },
        }
    }
};