<?php
/**
 * Copyright © (c) chotructuyen.vn All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Chottvn\PriceQuote\Controller\Index;

class Index extends \Magento\Framework\App\Action\Action
{

    protected $resultPageFactory;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Quote\Api\Data\AddressInterface
     */
    protected $address;

    /**
     * @var \Magento\Checkout\Api\ShippingInformationManagementInterface
     */
    protected $shippingInformationManagement;

    /**
     * @var \Magento\Checkout\Api\Data\ShippingInformationInterface
     */
    protected $shippingInformation;

    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $coreSession;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context  $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Session\SessionManagerInterface $coreSession,
        \Magento\Checkout\Model\Session $_checkoutSession,
        \Magento\Quote\Api\Data\AddressInterface $address,
        \Magento\Checkout\Api\ShippingInformationManagementInterface $shippingInformationManagement,
        \Magento\Checkout\Api\Data\ShippingInformationInterface $shippingInformation,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->coreSession = $coreSession;
        $this->_checkoutSession = $_checkoutSession;
        $this->address = $address;
        $this->shippingInformationManagement = $shippingInformationManagement;
        $this->shippingInformation = $shippingInformation;
        $this->customerSession = $customerSession;
        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {        
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Price Quote'));
        $price_quote_shipping_address = $this->coreSession->getCartShippingAddress();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $customerSession = $this->customerSession->getCustomerData();
        // if($this->_checkoutSession->getQuote()){

            if($this->customerSession->isLoggedIn()){
                if(($price_quote_shipping_address && $price_quote_shipping_address['type'] != 'customer') || empty($price_quote_shipping_address)){
                    $price_quote_shipping_address_session['type'] = 'customer';
                    $price_quote_shipping_address_session['action'] = 'option';
                    $price_quote_shipping_address_session['shipping-address'] = array();
                    $price_quote_shipping_address_session['shipping-address-current'] = array();

                    $customer_id = $customerSession->getId();
                    $customer_obj = $objectManager->create('Magento\Customer\Model\Customer')->load($customer_id);
                    $customer_address = array();
                    foreach ($customer_obj->getAddresses() as $address) {
                        $array_address = $address->toArray();

                        // load region by model
                        $region_model = $objectManager->create('Magento\Directory\Model\Region')->load($array_address['region_id']);
                        $array_address['region'] = $region_model->getName();

                        // create full-address
                        if ($array_address['street']) {
                            $tmp_fulladdress['street'] = $array_address['street'];
                        }
                        if ($array_address['township']) {
                            $tmp_fulladdress['township'] = $array_address['township'];
                        }
                        if ($array_address['city']) {
                            $tmp_fulladdress['city'] = $array_address['city'];
                        }
                        if ($array_address['region']) {
                            $tmp_fulladdress['region'] = $array_address['region'];
                        }

                        // store full-address to array
                        $array_address['full_address'] = implode(', ', $tmp_fulladdress);
                        $array_address['full_address_tmp'] = $array_address['street'].$array_address['township_id'].$array_address['city_id'].$array_address['region_id'];
                        $customer_address[] = $array_address;
                    }

                    $price_quote_shipping_address_session['shipping-address'] = $customer_address;

                    // save to session
                    if($price_quote_shipping_address_session){
                        $this->coreSession->setCartShippingAddress($price_quote_shipping_address_session);
                    }
                }

                // save address shipping
                if($price_quote_shipping_address && $price_quote_shipping_address['shipping-address-current'] && $price_quote_shipping_address['type'] == 'customer'){
                    $shipping_address_session = $this->coreSession->getCartShippingAddress();
                    $this->saveShippingInformation($shipping_address_session);
                }
            }else{
                // save address shipping
                if($price_quote_shipping_address && $price_quote_shipping_address['shipping-address-current']){
                    $shipping_address_session = $this->coreSession->getCartShippingAddress();
                    $this->saveShippingInformation($shipping_address_session);
                }
            }

        // /}

        
        
        return $resultPage;
    }

    /**
     * Save shipping information
     * 
     * @author: Tuan Nguyen
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function saveShippingInformation($shipping_address_session)
    {
        if ($this->_checkoutSession->getQuote()) {
            $cartId = $this->_checkoutSession->getQuote()->getId();
            $cartSkuArray = $this->getCartItemsSkus();

            if ($cartSkuArray) {
                $shippingAddress = $this->getShippingAddressInformation($shipping_address_session);
                if($shippingAddress){
                    $this->shippingInformationManagement->saveAddressInformation($cartId, $shippingAddress);
                }
            }
        }
    }

    /**
     * get shipping address information
     * 
     * @author: Tuan Nguyen
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function getShippingAddressInformation($shipping_address_session) {
        $current_shipping_address = $shipping_address_session['shipping-address-current'];
        // check data before set shipping address
        if(isset($current_shipping_address['region_id']) && isset($current_shipping_address['city_id'])){
            $shippingAddress = $this->prepareShippingAddress($current_shipping_address);
            $address_shipping = $this->shippingInformation->setShippingAddress($shippingAddress)
                ->setShippingCarrierCode('tablerate')
                ->setShippingMethodCode('bestway');
        }
        
        return $address_shipping;
    }

    /**
     * prepare shipping address from your custom shipping address
     * 
     * @author: Tuan Nguyen
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    protected function prepareShippingAddress($requestParams) {
        $first_name = isset($requestParams['firstname']) ? $requestParams['firstname']:'Guest';
        $last_name = isset($requestParams['lastname']) ? $requestParams['lastname']:'-';
        $street = isset($requestParams['street']) ? trim($requestParams['street']):'';
        $country_id = isset($requestParams['country_id']) ? trim($requestParams['country_id']):'VN';
        $region = isset($requestParams['region']) ? trim($requestParams['region']):'';
        $region_id = isset($requestParams['region_id']) ? (int) trim($requestParams['region_id']):0;
        $city_id = isset($requestParams['city_id']) ? (int) trim($requestParams['city_id']):0;
        $city = isset($requestParams['city']) ? trim($requestParams['city']):'';
        $township_id = isset($requestParams['township_id']) ? (int) trim($requestParams['township_id']):0;
        $township = isset($requestParams['township']) ? trim($requestParams['township']):'';
        $postcode = $this->getPostcodeFromAddress($city_id) ? $this->getPostcodeFromAddress($city_id):0;

        $address = $this->address
            ->setFirstname($first_name)
            ->setLastname($last_name)
            ->setStreet($street)
            ->setCountryId($country_id)
            ->setRegion($region)
            ->setRegionId($region_id)
            ->setCityId($city_id)
            ->setCity($city)
            ->setTownshipId($township_id)
            ->setTownship($township)
            ->setPostcode($postcode)
            ->setSaveInAddressBook(0)
            ->setSameAsBilling(0);
        return $address;
    }

    /**
     * Get PostCode from City Id
     *
     * @param 
     * @return 
     */
    protected function getPostcodeFromAddress($cityid){
        $postCode =  null;

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        try {         
            if(!empty($cityid)){                
                $city = $objectManager->get('\Chottvn\Address\Model\ResourceModel\City\CollectionFactory')->create()->addFieldToFilter(
                    'city_id',
                    ['eq' => $cityid]
                );                
                if ($city){
                    $city = $city->getFirstItem();
                    $postCode = $city->getPostcode();
                }
            }            
        }catch(\Exception $e){

        }              
        return $postCode;
    }

    /**
     * get Cart Items SKus
     *
     * @author: Tuan Nguyen
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function getCartItemsSkus() {
        $cartSkuArray = [];
        $cartItems = $this->_checkoutSession->getQuote()->getAllVisibleItems();
        foreach ($cartItems as $product) {
            $cartSkuArray[] = $product->getSku();
        }
        return $cartSkuArray;
    }
}

