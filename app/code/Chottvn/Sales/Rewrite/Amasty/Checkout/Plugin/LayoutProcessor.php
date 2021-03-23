<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace  Chottvn\Sales\Rewrite\Amasty\Checkout\Plugin;

use Amasty\Checkout\Helper\Onepage;
use Amasty\Checkout\Model\Config;

/**
 * Class LayoutProcessor
 */
class LayoutProcessor extends \Amasty\Checkout\Plugin\LayoutProcessor
{
    const PREFIX_DROPDOWN_OPTIONS = 'customer/address/prefix_options';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var array
     */
    protected $orderFixes = [];

    /**
     * @var Onepage
     */
    private $onepageHelper;

    /**
     * @var Config
     */
    private $checkoutConfig;

    public function __construct(
        Onepage $onepageHelper,
        Config $checkoutConfig,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->onepageHelper = $onepageHelper;
        $this->checkoutConfig = $checkoutConfig;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param $field
     * @param $order
     */
    public function setOrder($field, $order)
    {
        $this->orderFixes[$field] = $order;
    }

    /**
     * @param \Magento\Checkout\Block\Checkout\LayoutProcessor $subject
     * @param array $result
     * @return array
     */
    public function afterProcess(
        \Magento\Checkout\Block\Checkout\LayoutProcessor $subject,
        $result
    ) {
        // $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;

        // $others_prefix = $this->scopeConfig->getValue(self::PREFIX_DROPDOWN_OPTIONS, $storeScope);
        // $others_prefix = explode(';', $others_prefix);
        // $prefix = [];

        // foreach($others_prefix as $value => $item) {
        //     $prefix[$value]['value'] = $item;
        //     $prefix[$value]['label'] = $item;
        // }

        if ($this->checkoutConfig->isEnabled()) {
            $layoutRoot = &$result['components']['checkout']['children']['steps']['children']['shipping-step']
                           ['children']['shippingAddress']['children'];
            $layoutRoot['customer-email']['component'] = 'Amasty_Checkout/js/view/form/element/email';

            foreach ($this->orderFixes as $code => $order) {
                if ($code == 'city') {
                    $layoutRoot['shipping-address-fieldset']['children']['city']['validation']['required-entry'] = false;
                    // $this->writeLog($layoutRoot['shipping-address-fieldset']['children']['city']);
                }
                $layoutRoot['shipping-address-fieldset']['children'][$code]['sortOrder'] = $order;
            }
        }

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customerSession = $objectManager->create('Magento\Customer\Model\Session');
        // $phoneAttribute = '';
        $affiliate_code = '';
        $disabledAffiliateCode = false;
        if ($customerSession->isLoggedIn()) {
            $customerRepository = $objectManager->get('Magento\Customer\Api\CustomerRepositoryInterface');
            $customer = $customerRepository->getById($customerSession->getData('customer_id'));

            if (!empty($customer->getCustomAttribute('affiliate_status')) && 
                ($customer->getCustomAttribute('affiliate_status')->getValue() == 'activated' || 
                $customer->getCustomAttribute('affiliate_status')->getValue() == 'freezed') && 
                $customerSession->getData('customer_group_id') == 4 &&
                !empty($customer->getCustomAttribute('affiliate_code')->getValue())) {
                $affiliate_code = $customer->getCustomAttribute('affiliate_code')->getValue();
                $disabledAffiliateCode = true;
            }
            // $phoneAttribute = $objectManager->create('Chottvn\SigninPhoneNumber\Rewrite\Magento\Customer\Block\Form\Edit');
            // $getPhoneNumber = $phoneAttribute->getPhoneNumber();
        }

        // $order = $customerSession->isLoggedIn() ? 'others-receive-products' : 'shipping-address-fieldset';
        // $order = $customerSession->isLoggedIn() ? 'customer-info' : 'shipping-address-fieldset';
        // $position_email = $customerSession->isLoggedIn() ? 5 : 31;

        // firstname_ctt, sortOrder = 1
        $result['components']['checkout']['children']['steps']['children']['shipping-step']
        ['children']['shippingAddress']['children']['customer-info']['children']['firstname_ctt'] = [
            'component' => 'Magento_Ui/js/form/element/abstract',
            'config' => [
                'customScope' => 'shippingAddress',
                'customEntry' => null,
                'template' => 'ui/form/field',
                'elementTmpl' => 'ui/form/element/input',
                'options' => [],
                'id' => 'firstname_ctt'
            ],
            'dataScope' => 'shippingAddress.firstname_ctt',
            'label' => __('Full name of buyer'),
            'provider' => 'checkoutProvider',
            'visible' => true,
            'options' => [],
            'filterBy' => null,
            'validation' => [
                'required-entry' => true,
            ],
            'sortOrder' => 1,
            'customEntry' => null,
            'id' => 'firstname_ctt'
        ];

        // telephone_ctt, sortOrder = 2
        $result['components']['checkout']['children']['steps']['children']['shipping-step']
        ['children']['shippingAddress']['children']['customer-info']['children']['telephone_ctt'] = [
            'component' => 'Magento_Ui/js/form/element/abstract',
            'config' => [
                'customScope' => 'shippingAddress',
                'customEntry' => null,
                'template' => 'ui/form/field',
                'elementTmpl' => 'ui/form/element/input',
                'options' => [],
                'id' => 'telephone_ctt'
            ],
            'dataScope' => 'shippingAddress.telephone_ctt',
            'label' => __('Telephone'),
            'provider' => 'checkoutProvider',
            'visible' => true,
            'options' => [],
            'filterBy' => null,
            'validation' => [
                'required-entry' => true,
                'validate-phone-VN' => true
            ],
            'sortOrder' => 2,
            'customEntry' => null,
            'id' => 'telephone_ctt'
        ];

        // email_ctt, sortOrder = 3
        $result['components']['checkout']['children']['steps']['children']['shipping-step']
        ['children']['shippingAddress']['children']['customer-info']['children']['email_ctt'] = [
            'component' => 'Magento_Ui/js/form/element/abstract',
            'config' => [
                'customScope' => 'shippingAddress',
                'customEntry' => null,
                'template' => 'ui/form/field',
                'elementTmpl' => 'ui/form/element/input',
                'options' => [],
                'id' => 'email_ctt'
            ],
            'dataScope' => 'shippingAddress.email_ctt',
            'label' => __('Email'),
            'provider' => 'checkoutProvider',
            'visible' => true,
            'options' => [],
            'filterBy' => null,
            'validation' => [
                'validate-email' => true
            ],
            'sortOrder' => 3,
            'customEntry' => null,
            'id' => 'email_ctt'
        ];

        // others_receive_products, sortOrder = 4
        $result['components']['checkout']['children']['steps']['children']['shipping-step']
        ['children']['shippingAddress']['children']['customer-info']['children']['others_receive_products'] = [
            'component' => 'Magento_Ui/js/form/element/abstract',
            'config' => [
                'customScope' => 'shippingAddress',
                'customEntry' => null,
                'template' => 'ui/form/field',
                'elementTmpl' => 'ui/form/element/checkbox_others_receive_products',
                'options' => [
                ],
                'id' => 'others_receive_products',
            ],
            'dataScope' => 'shippingAddress.others_receive_products',
            'label' => __('Others receive the products'),
            'provider' => 'checkoutProvider',
            'visible' => true,
            'options' => [],
            'filterBy' => null,
            'validation' => [
            ],
            'sortOrder' => 4,
            'customEntry' => null,
            'id' => 'others_receive_products'
        ];

        // others_fullname, sortOrder = 5
        $result['components']['checkout']['children']['steps']['children']['shipping-step']
        ['children']['shippingAddress']['children']['delivery-to-other-customer']['children']['others_fullname'] = [
            'component' => 'Magento_Ui/js/form/element/abstract',
            'config' => [
                'customScope' => 'shippingAddress',
                'customEntry' => null,
                'template' => 'ui/form/field',
                'elementTmpl' => 'ui/form/element/input',
                'options' => [
                ],
                'id' => 'others_fullname',
            ],
            'dataScope' => 'shippingAddress.others_fullname',
            'label' => __('Full name of receiver'),
            'provider' => 'checkoutProvider',
            'visible' => false,
            'options' => [],
            'filterBy' => null,
            'validation' => [
                'required-entry-if-checked' => true,
            ],
            'sortOrder' => 5,
            'customEntry' => null,
            'id' => 'others_fullname'
        ];

        // others_telephone, sortOrder = 6
        $result['components']['checkout']['children']['steps']['children']['shipping-step']
        ['children']['shippingAddress']['children']['delivery-to-other-customer']['children']['others_telephone'] = [
            'component' => 'Magento_Ui/js/form/element/abstract',
            'config' => [
                'customScope' => 'shippingAddress',
                'customEntry' => null,
                'template' => 'ui/form/field',
                'elementTmpl' => 'ui/form/element/input',
                'options' => [
                ],
                'id' => 'others_telephone'
            ],
            'dataScope' => 'shippingAddress.others_telephone',
            'label' => __('Telephone'),
            'provider' => 'checkoutProvider',
            'visible' => false,
            'options' => [],
            'filterBy' => null,
            'validation' => [
                'required-entry-if-checked' => true,
                'validate-phone-VN-others-receive' => true
            ],
            'sortOrder' => 6,
            'customEntry' => null,
            'id' => 'others_telephone'
        ];

        // others_email, sortOrder = 7
        $result['components']['checkout']['children']['steps']['children']['shipping-step']
        ['children']['shippingAddress']['children']['delivery-to-other-customer']['children']['others_email'] = [
            'component' => 'Magento_Ui/js/form/element/abstract',
            'config' => [
                'customScope' => 'shippingAddress',
                'customEntry' => null,
                'template' => 'ui/form/field',
                'elementTmpl' => 'ui/form/element/input',
                'options' => [
                ],
                'id' => 'others_email'
            ],
            'dataScope' => 'shippingAddress.others_email',
            'label' => __('Email'),
            'provider' => 'checkoutProvider',
            'visible' => true,
            'options' => [],
            'validation' => [
                'validate-email-others-receive' => true
            ],
            'filterBy' => null,
            'sortOrder' => 7,
            'customEntry' => null,
            'id' => 'others_email'
        ];

        // vat_company_ctt, sortOrder = 1
        $result['components']['checkout']['children']['steps']['children']['shipping-step']
        ['children']['shippingAddress']['children']['vat-invoice-required']['children']['vat_company_ctt'] = [
            'component' => 'Magento_Ui/js/form/element/abstract',
            'config' => [
                'customScope' => 'shippingAddress',
                'customEntry' => null,
                'template' => 'ui/form/field',
                'elementTmpl' => 'ui/form/element/input',
                'options' => [
                ],
                'id' => 'vat_company_ctt'
            ],
            'dataScope' => 'shippingAddress.vat_company_ctt',
            'label' => __('Company name'),
            'provider' => 'checkoutProvider',
            'visible' => true,
            'options' => [],
            'validation' => [
                // 'required-entry-if-checked-vat' => true,
            ],
            'filterBy' => null,
            'sortOrder' => 1,
            'customEntry' => null,
            'id' => 'vat_company_ctt'
        ];

        // vat_address_ctt, sortOrder = 2
        $result['components']['checkout']['children']['steps']['children']['shipping-step']
        ['children']['shippingAddress']['children']['vat-invoice-required']['children']['vat_address_ctt'] = [
            'component' => 'Magento_Ui/js/form/element/abstract',
            'config' => [
                'customScope' => 'shippingAddress',
                'customEntry' => null,
                'template' => 'ui/form/field',
                'elementTmpl' => 'ui/form/element/input',
                'options' => [
                ],
                'id' => 'vat_address_ctt'
            ],
            'dataScope' => 'shippingAddress.vat_address_ctt',
            'label' => __('Company address'),
            'provider' => 'checkoutProvider',
            'visible' => true,
            'options' => [],
            'validation' => [
                // 'required-entry-if-checked-vat' => true,
            ],
            'filterBy' => null,
            'sortOrder' => 2,
            'customEntry' => null,
            'id' => 'vat_address_ctt'
        ];

        // vat_number_ctt, sortOrder = 3
        $result['components']['checkout']['children']['steps']['children']['shipping-step']
        ['children']['shippingAddress']['children']['vat-invoice-required']['children']['vat_number_ctt'] = [
            'component' => 'Magento_Ui/js/form/element/abstract',
            'config' => [
                'customScope' => 'shippingAddress',
                'customEntry' => null,
                'template' => 'ui/form/field',
                'elementTmpl' => 'ui/form/element/input',
                'options' => [
                ],
                'id' => 'vat_number_ctt'
            ],
            'dataScope' => 'shippingAddress.vat_number_ctt',
            'label' => __('VAT code'),
            'provider' => 'checkoutProvider',
            'visible' => true,
            'options' => [],
            'validation' => [
                // 'required-entry-if-checked-vat' => true,
                // 'validate-tax' => true
            ],
            'filterBy' => null,
            'sortOrder' => 3,
            'customEntry' => null,
            'id' => 'vat_number_ctt'
        ];

        // vat_contact_information_ctt, sortOrder = 4
        $result['components']['checkout']['children']['steps']['children']['shipping-step']
        ['children']['shippingAddress']['children']['vat-invoice-required']['children']['vat_contact_information_ctt'] = [
            'component' => 'Magento_Ui/js/form/element/abstract',
            'config' => [
                'customScope' => 'shippingAddress',
                'customEntry' => null,
                'template' => 'ui/form/field',
                'elementTmpl' => 'ui/form/element/span',
                'options' => [
                ],
                'id' => 'vat_contact_information_ctt'
            ],
            'dataScope' => 'shippingAddress.vat_contact_information_ctt',
            'provider' => 'checkoutProvider',
            'visible' => true,
            'options' => [],
            'filterBy' => null,
            'sortOrder' => 4,
            'customEntry' => null,
            'id' => 'vat_contact_information_ctt',
            'value' => __('Contact information')
        ];

        // vat_contact_name_ctt, sortOrder = 5
        $result['components']['checkout']['children']['steps']['children']['shipping-step']
        ['children']['shippingAddress']['children']['vat-invoice-required']['children']['vat_contact_name_ctt'] = [
            'component' => 'Magento_Ui/js/form/element/abstract',
            'config' => [
                'customScope' => 'shippingAddress',
                'customEntry' => null,
                'template' => 'ui/form/field',
                'elementTmpl' => 'ui/form/element/input',
                'options' => [
                ],
                'id' => 'vat_contact_name_ctt'
            ],
            'dataScope' => 'shippingAddress.vat_contact_name_ctt',
            'label' => __('Full name and contact person'),
            'provider' => 'checkoutProvider',
            'visible' => true,
            'options' => [],
            'validation' => [
                // 'required-entry-if-checked-vat' => true,
            ],
            'filterBy' => null,
            'sortOrder' => 5,
            'customEntry' => null,
            'id' => 'vat_contact_name_ctt'
        ];

        // vat_contact_phone_number_ctt, sortOrder = 6
        $result['components']['checkout']['children']['steps']['children']['shipping-step']
        ['children']['shippingAddress']['children']['vat-invoice-required']['children']['vat_contact_phone_number_ctt'] = [
            'component' => 'Magento_Ui/js/form/element/abstract',
            'config' => [
                'customScope' => 'shippingAddress',
                'customEntry' => null,
                'template' => 'ui/form/field',
                'elementTmpl' => 'ui/form/element/input',
                'options' => [
                ],
                'id' => 'vat_contact_phone_number_ctt'
            ],
            'dataScope' => 'shippingAddress.vat_contact_phone_number_ctt',
            'label' => __('Telephone'),
            'provider' => 'checkoutProvider',
            'visible' => true,
            'options' => [],
            'validation' => [
                // 'validate-phone-VN-vat' => true
            ],
            'filterBy' => null,
            'sortOrder' => 6,
            'customEntry' => null,
            'id' => 'vat_contact_phone_number_ctt'
        ];

        // vat_contact_email_ctt, sortOrder = 7
        $result['components']['checkout']['children']['steps']['children']['shipping-step']
        ['children']['shippingAddress']['children']['vat-invoice-required']['children']['vat_contact_email_ctt'] = [
            'component' => 'Magento_Ui/js/form/element/abstract',
            'config' => [
                'customScope' => 'shippingAddress',
                'customEntry' => null,
                'template' => 'ui/form/field',
                'elementTmpl' => 'ui/form/element/input',
                'options' => [
                ],
                'id' => 'vat_contact_email_ctt'
            ],
            'dataScope' => 'shippingAddress.vat_contact_email_ctt',
            'label' => __('Email'),
            'provider' => 'checkoutProvider',
            'visible' => true,
            'options' => [],
            'validation' => [
                // 'required-entry-if-checked-vat' => true,
                // 'validate-email-vat' => true
            ],
            'filterBy' => null,
            'sortOrder' => 7,
            'customEntry' => null,
            'id' => 'vat_contact_email_ctt'
        ];

        // info_required, sortOrder = 8
        $result['components']['checkout']['children']['steps']['children']['shipping-step']
        ['children']['shippingAddress']['children']['vat-invoice-required']['children']['info_required'] = [
            'component' => 'Magento_Ui/js/form/element/abstract',
            'config' => [
                'customScope' => 'shippingAddress',
                'customEntry' => null,
                'template' => 'ui/form/field',
                'elementTmpl' => 'ui/form/element/span',
                'options' => [
                ],
                'id' => 'info_required'
            ],
            'dataScope' => 'shippingAddress.info_required',
            'provider' => 'checkoutProvider',
            'visible' => false,
            'options' => [],
            'filterBy' => null,
            'sortOrder' => 8,
            'customEntry' => null,
            'id' => 'info_required',
            'value' => __('Required Information')
        ];

        // max_delivery_dates
        $result['components']['checkout']['children']['summary-v3']['children']['max-delivery-dates-v3']
        ['children']['max_delivery_dates'] = [
            'component' => 'Magento_Ui/js/form/element/abstract',
            'config' => [
                'customScope' => 'additional',
                'customEntry' => null,
                'template' => 'ui/form/field',
                'elementTmpl' => 'ui/form/element/html',
                'options' => [
                ],
                'id' => 'max_delivery_dates',
            ],
            'dataScope' => 'additional.max_delivery_dates',
            'label' => __('Max Delivery Dates '),
            'provider' => 'checkoutProvider',
            'visible' => false,
            'options' => [],
            'filterBy' => null,
            'value' => '*',
            'sortOrder' => 600,
            'customEntry' => null,
            'id' => 'max_delivery_dates'
        ];

        // fee_shipping_contact
        $result['components']['checkout']['children']['steps']['children']['shipping-step']
        ['children']['shippingAddress']['children']['customer-info']['children']['fee_shipping_contact'] = [
            'component' => 'Magento_Ui/js/form/element/abstract',
            'config' => [
                'customScope' => 'shippingAddress',
                'customEntry' => null,
                'template' => 'ui/form/field',
                'elementTmpl' => 'ui/form/element/input',
                'options' => [
                ],
                'id' => 'fee_shipping_contact',
            ],
            'dataScope' => 'shippingAddress.fee_shipping_contact',
            'provider' => 'checkoutProvider',
            'visible' => false,
            'options' => [],
            'filterBy' => null,
            'validation' => [
            ],
            'sortOrder' => 1,
            'customEntry' => null,
            'value' => 0,
            'id' => 'fee_shipping_contact'
        ];

        // vat_invoice_required_ctt, sortOrder = 1
        $result['components']['checkout']['children']['vat-invoice-required-ctt']['children']['vat_invoice_required_ctt'] = [
            'component' => 'Magento_Ui/js/form/element/abstract',
            'config' => [
                'customScope' => 'shippingAddress',
                'customEntry' => null,
                'template' => 'ui/form/field',
                'elementTmpl' => 'ui/form/element/checkbox_vat_invoice_required_ctt',
                'options' => [],
                'id' => 'vat_invoice_required_ctt'
            ],
            'dataScope' => 'shippingAddress.vat_invoice_required_ctt',
            'label' => __('Request an invoice'),
            'provider' => 'checkoutProvider',
            'visible' => true,
            'options' => [],
            'filterBy' => null,
            'sortOrder' => 1,
            'customEntry' => null,
            'checked' => false,
            'id' => 'vat_invoice_required_ctt'
        ];

        // affiliate_account_code
        $result['components']['checkout']['children']['affiliate-account-code-v3']
        ['children']['affiliate_account_code'] = [
            'component' => 'Magento_Ui/js/form/element/abstract',
            'config' => [
                'customScope' => 'additional',
                'customEntry' => null,
                'template' => 'ui/form/field',
                'elementTmpl' => 'ui/form/element/input',
                'options' => [
                ],
                'id' => 'affiliate_account_code',
            ],
            'dataScope' => 'additional.affiliate_account_code',
            'label' => __('Code of counselor introduced'),
            'provider' => 'checkoutProvider',
            'visible' => true,
            'options' => [],
            'filterBy' => null,
            'value' => $affiliate_code != '' ? $affiliate_code : '',
            'disabled' => $disabledAffiliateCode,
            'sortOrder' => 700,
            'customEntry' => null,
            'id' => 'affiliate_account_code'
        ];

        return $result;
    }

    /**
	* @param $info
	* @param $type  [error, warning, info]
	* @return 
	*/
	private function writeLog($info, $type = "info"){
		$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/LayoutProcessor.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);        	                 
        switch($type){
        	case "error":
        		$logger->err($info);  
        		break;
        	case "warning":
        		$logger->notice($info);  
        		break;
        	case "info":
        		$logger->info($info);  
        		break;
        	default:
        		$logger->info($info);  
        }
	}
}
