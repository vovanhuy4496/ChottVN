<?php

namespace Chottvn\Address\Plugin\Magento\Checkout\Block\Checkout;

use Chottvn\Address\Helper\Data;

class LayoutProcessor
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Magento\Checkout\Helper\Data
     */
    private $checkoutDataHelper;

    /**
     * @var \Chottvn\Address\Helper\Data
     */
    private $helper;

    /**
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Helper\Data $checkoutDataHelper
     * @param \Chottvn\Address\Helper\Data $helper
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Helper\Data $checkoutDataHelper,
        \Chottvn\Address\Helper\Data $helper
    ) {
        $this->customerSession = $customerSession;
        $this->checkoutDataHelper = $checkoutDataHelper;
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Checkout\Block\Checkout\LayoutProcessor $subject
     * @param array $jsLayout
     * @return array
     */
    public function afterProcess(
        \Magento\Checkout\Block\Checkout\LayoutProcessor $subject,
        array $result
    ) {
        $result = $this->_getShippingFormFields($result);
        $result = $this->_getBillingFormFields($result);
        return $result;
    }

    /**
     * Get shipping form fields
     *
     * @param array $result
     * @return array
     */
    private function _getShippingFormFields($result)
    {
        $shippingFieldset = $result['components']['checkout']['children']['steps']
        ['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset'];
        if (isset($shippingFieldset)) {
            $shippingCustomFields = $this->_getFields('shippingAddress', 'shipping');

            $shippingFields = $shippingFieldset['children'];
            if (isset($shippingFields['street'])) {
                unset($shippingFields['street']['children'][1]['validation']);
                unset($shippingFields['street']['children'][2]['validation']);
            }

            $shippingFields = array_replace_recursive($shippingFields, $shippingCustomFields);

            $shippingFields['city']['sortOrder'] = 108;
            if ($this->helper->getConfigValue(Data::XML_CONFIG_PATH_HIDE_COUNTRY)) {
                $shippingFields['country_id']['visible'] = false;
            }
            
            $result['components']['checkout']['children']['steps']['children']
            ['shipping-step']['children']['shippingAddress']['children']
            ['shipping-address-fieldset']['children'] = $shippingFields;
        }

        return $result;
    }

    /**
     * Get billing form fields
     *
     * @param array $result
     * @return array
     */
    private function _getBillingFormFields($result)
    {
        $billingFieldset = $result['components']['checkout']['children']['steps']
        ['children']['billing-step']['children']['payment']['children'];
        if (isset($billingFieldset)) {
            if ($this->checkoutDataHelper->isDisplayBillingOnPaymentMethodAvailable()) {
                $paymentForms = $billingFieldset['payments-list']['children'];
                foreach ($paymentForms as $paymentMethodForm => $paymentMethodValue) {
                    $paymentMethodCode = str_replace('-form', '', $paymentMethodForm);
                    if (!isset($result['components']['checkout']['children']['steps']['children']['billing-step']
                        ['children']['payment']['children']['payments-list']['children'][$paymentMethodCode . '-form'])
                    ) {
                        continue;
                    }
                    $billingFields = $result['components']['checkout']['children']['steps']['children']
                    ['billing-step']['children']['payment']['children']
                    ['payments-list']['children'][$paymentMethodCode . '-form']['children']['form-fields']['children'];
                    $billingCustomFields = $this->_getFields('billingAddress' . $paymentMethodCode, 'billing');

                    $billingFields = array_replace_recursive($billingFields, $billingCustomFields);

                    $billingFields['city']['sortOrder'] = 108;
                    if ($this->helper->getConfigValue(Data::XML_CONFIG_PATH_HIDE_COUNTRY)) {
                        $billingFields['country_id']['visible'] = false;
                    }

                    $result['components']['checkout']['children']['steps']['children']
                    ['billing-step']['children']['payment']['children']
                    ['payments-list']['children'][$paymentMethodCode . '-form']['children']
                    ['form-fields']['children'] = $billingFields;
                }
            } else {
                $billingFields = $billingFieldset['afterMethods']['children']['billing-address-form']['children']['form-fields']['children'];
                $billingCustomFields = $this->_getFields('billingAddressshared', 'billing');

                $billingFields = array_replace_recursive($billingFields, $billingCustomFields);

                $billingFields['city']['sortOrder'] = 108;
                if ($this->helper->getConfigValue(Data::XML_CONFIG_PATH_HIDE_COUNTRY)) {
                    $billingFields['country_id']['visible'] = false;
                }

                $result['components']['checkout']['children']['steps']['children']
                ['billing-step']['children']['payment']['children']
                ['afterMethods']['children']['billing-address-form']['children']['form-fields']['children'] = $billingFields;
            }
        }
        return $result;
    }

    /**
     * Paser field data
     *
     * @param string $scope
     * @param string $addressType
     * @return array
     */
    private function _getFields($scope, $addressType)
    {
        $fields = [];
        foreach ($this->_getAdditionalFields($addressType) as $field) {
            $fields[$field] = $this->_getField($field, $scope, $addressType);
        }
        return $fields;
    }

    /**
     * Get additional fields
     *
     * @param string $addressType
     * @return array
     */
    private function _getAdditionalFields($addressType = 'shipping')
    {
        if ($addressType == 'shipping') {
            return $this->helper->getExtraCheckoutAddressFields('extra_checkout_shipping_address_fields');
        }
        return  $this->helper->getExtraCheckoutAddressFields('extra_checkout_billing_address_fields');
    }

    /**
     * Get address field configuration
     *
     * @param string $attributeCode
     * @param string $scope
     * @param string $addressType
     * @return array
     */
    private function _getField($attributeCode, $scope, $addressType = 'shipping')
    {
        $target = '${ $.provider }:${ $.parentScope }';
        if ($addressType == 'shipping') {
            $target = 'checkoutProvider:shippingAddress';
        }

        if (!$this->customerSession->isLoggedIn()) {
            $scope .= '.custom_attributes';
        }

        $disableTownship = $this->helper->getConfigValue(Data::XML_CONFIG_PATH_HIDE_TOWNSHIP);
        $field = [];
        if ($attributeCode == 'city_id') {
            $field = [
                'component' => 'Chottvn_Address/js/form/element/city',
                'config' => [
                    'customScope' => $scope,
                    // 'customEntry' => $scope . '.city',
                    'elementTmpl' => 'ui/form/element/select',
                ],
                'validation' => [
                    'required-entry' => true,
                ],
                'filterBy' => [
                    'target' => $target . '.region_id',
                    'field' => 'region_id',
                ],
                'imports' => [
                    'initialOptions' => 'index = checkoutProvider:dictionaries.city_id',
                    'setOptions' => 'index = checkoutProvider:dictionaries.city_id',
                ],
                'deps' => 'checkoutProvider',
                'dataScope' => $scope . '.' . $attributeCode,
                'visible' => false
            ];
        } elseif ($attributeCode == 'township_id') {
            $field = [
                'component' => 'Chottvn_Address/js/form/element/township',
                'config' => [
                    'customScope' => $scope,
                    'customEntry' => $scope . '.township',
                    'elementTmpl' => 'ui/form/element/select',
                ],
                'validation' => [
                    'required-entry' => true,
                ],
                'filterBy' => [
                    'target' => $target . '.city_id',
                    'field' => 'city_id',
                ],
                'imports' => [
                    'initialOptions' => 'index = checkoutProvider:dictionaries.township_id',
                    'setOptions' => 'index = checkoutProvider:dictionaries.township_id',
                ],
                'deps' => 'checkoutProvider',
                'dataScope' => $scope . '.' . $attributeCode,
                'visible' => false,
                'hidden' => (bool) $disableTownship
            ];
        } elseif ($attributeCode == 'township') {
            $field = [
                'component' => 'Magento_Ui/js/form/element/abstract',
                'config' => [
                    'customScope' => $scope,
                ],
                'validation' => [
                    'required-entry' => true,
                ],
                'dataScope' => $scope . '.' . $attributeCode,
                'visible' => true,
                'hidden' => (bool) $disableTownship
            ];
        }
        return $field;
    }
}
