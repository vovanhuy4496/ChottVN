<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Chottvn\Sales\Rewrite\Amasty\Checkout\Plugin;

use Amasty\Checkout\Model\Config;
use Amasty\Checkout\Plugin\AttributeMerger;
class CartLayoutProcessor extends \Amasty\Checkout\Plugin\CartLayoutProcessor
{
    /**
     * @var array
     */
    private $orderFields = [];

    /**
     * @var Config
     */
    private $checkoutConfig;

    /**
     * @var AttributeMerger
     */
    private $attributeMergerPlugin;

     /**
     * @var \Chottvn\Address\Helper\Data
     */
    private $helper;


    public function __construct(
        Config $checkoutConfig,
        \Chottvn\Address\Helper\Data $helper,
        AttributeMerger $attributeMergerPlugin
    ) {
        $this->helper = $helper;
        $this->checkoutConfig = $checkoutConfig;
        $this->attributeMergerPlugin = $attributeMergerPlugin;
    }
    private function initOrderFields()
    {
        if (!empty($this->orderFields)) {
            return;
        }

        $fieldConfig = $this->attributeMergerPlugin->getFieldConfig();
        /** @var \Amasty\Checkout\Model\Field $field  */
        foreach ($fieldConfig as $attributeCode => $field) {
            $this->orderFields[$attributeCode] = $field->getData('sort_order');
        }

        if (isset($this->orderFields['region'])) {
            $this->orderFields['region_id'] = $this->orderFields['region'];
        }
    }

    /**
     * @param \Magento\Checkout\Block\Cart\LayoutProcessor $subject
     * @param array $result
     * @return array
     */
    public function afterProcess(
        \Magento\Checkout\Block\Cart\LayoutProcessor $subject,
        $result
    ) {
        $disableTownship = $this->helper->getConfigValue('customer/customer_address/disable_township');
        if ($this->checkoutConfig->isEnabled()) {
            $this->initOrderFields();
            $result['components']['checkoutProvider']
            ['dictionaries']['city_id'] = $this->helper->getCityDataProvider();
            $result['components']['checkoutProvider']
            ['dictionaries']['township_id'] = $this->helper->getTownshipDataProvider();
            $layoutRoot = &$result['components']['block-summary']['children']['block-shipping']
                ['children']['address-fieldsets']['children'];
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $customerSession = $objectManager->create('Magento\Customer\Model\Session');
            $customScope = 'shippingAddress.custom_attributes';
            if ($customerSession->isLoggedIn()) {
                $customScope = 'shippingAddress';
            }
            $city_id = 'city_id';
            $city_id_customField = [
                'component' => 'Chottvn_Address/js/form/element/city',
                'config' => [
                    'customScope' => $customScope,
                    'template' => 'ui/form/field',
                    'elementTmpl' => 'ui/form/element/select',
                    'customEntry' => 'shippingAddress.city_id',
                    'skipValidation' => true,
                ],
                'visible' => false,
                'dataScope' => $customScope.'.city_id',
                'label' => 'City',
                'provider' => 'checkoutProvider',
                'validation' => ['required-entry' => true],
                'sortOrder' => 60,
                'deps' => 'checkoutProvider',
                'id' => 'city_id',
                'options' => [
                    'visible' => false
                ],
                'filterBy' => 
                    [
                        'target' => '${ $.provider }:${ $.parentScope }.region_id',
                        'field' => 'region_id',
                    ],
                'imports' => 
                [
                    'initialOptions' => 'index = checkoutProvider:dictionaries.city_id',
                    'setOptions' => 'index = checkoutProvider:dictionaries.city_id',
                ]
            ];
            $city = 'city';
            $city_customField = [
                'component' => 'Magento_Ui/js/form/element/abstract',
                'config' => [
                    'customScope' => 'shippingAddress',
                    'template' => 'ui/form/field',
                    'elementTmpl' => 'ui/form/element/input'
                ],
                'sortOrder' => 60,
                'validation' => [
                    'required-entry' => true,
                ],
                'label' => 'City',
                'dataScope' => 'shippingAddress.city',
                'provider' => 'checkoutProvider',
                'options' => [
                    'filterBy' => NULL,
                    'customEntry' => NULL,
                    'visible' => false
                ],
                'hidden' => (bool) $disableTownship
            ];
            $township_id = 'township_id';
            $township_id_customField = [
                'component' => 'Chottvn_Address/js/form/element/township',
                'config' => [
                    'customScope' => $customScope,
                    'template' => 'ui/form/field',
                    'elementTmpl' => 'ui/form/element/select',
                    'customEntry' => 'shippingAddress.township_id',
                    'skipValidation' => true,
                ],
                'options' => [
                    'visible' => false
                ],
                'visible' => false,
                'dataScope' => $customScope.'.township_id',
                'label' => 'Township',
                'provider' => 'checkoutProvider',
                'validation' => ['required-entry' => true],
                'sortOrder' => 70,
                'deps' => 'checkoutProvider',
                'id' => 'township_id',
                'filterBy' => 
                    [
                        'target' => '${ $.provider }:${ $.parentScope }.city_id',
                        'field' => 'city_id',
                    ],
                'imports' => 
                [
                    'initialOptions' => 'index = checkoutProvider:dictionaries.township_id',
                    'setOptions' => 'index = checkoutProvider:dictionaries.township_id',
                ]
            ];
            $township = 'township';
            $township_customField = [
                'component' => 'Magento_Ui/js/form/element/abstract',
                'config' => [
                    'customScope' => $customScope,
                    'template' => 'ui/form/field',
                    'elementTmpl' => 'ui/form/element/input'
                ],
                'sortOrder' => 80,
                'validation' => [
                    'required-entry' => true,
                ],
                'label' => 'Township',
                'dataScope' => $customScope.'.township',
                'provider' => 'checkoutProvider',
                'options' => [
                    'filterBy' => NULL,
                    'customEntry' => NULL,
                    'visible' => false
                ],
                'hidden' => (bool) $disableTownship
            ];
            $street = "street[0]";
            $street_customField = [
                'component' => 'Magento_Ui/js/form/element/abstract',
                'config' => [
                    'customScope' => 'shippingAddress',
                    'template' => 'ui/form/field',
                    'elementTmpl' => 'ui/form/element/input'
                ],
                'label' => 'Street',
                'sortOrder' => 120,
                'validation' => [
                    'required-entry' => true,
                ],
                'dataScope' => 'shippingAddress.street.0',
                'provider' => 'checkoutProvider',
                'options' => [
                    'filterBy' => NULL,
                    'customEntry' => NULL,
                    'visible' => true
                ],
            ];
            $layoutRoot[$city] = $city_customField;
            $layoutRoot[$city_id] = $city_id_customField;
            $layoutRoot[$township_id] = $township_id_customField;
            $layoutRoot[$township] = $township_customField;
            $layoutRoot[$street] = $street_customField;
            foreach ($this->orderFields as $code => $order) {
                if (isset($layoutRoot[$code])) {
                    $layoutRoot[$code]['sortOrder'] = $order;
                }
            }
        }
        return $result;
    }
     /**
	* @param $info
	* @param $type  [error, warning, info]
	* @return 
	*/
	private function writeLog($info, $type = "info") {
		$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/cartLayout.log');
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

