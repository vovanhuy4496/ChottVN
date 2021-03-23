<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Chottvn\Sales\Rewrite\Magento\Checkout\Controller\Cart;
use Magento\Checkout\Model\Cart\RequestQuantityProcessor;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Action\HttpGetActionInterface;

/**
 * Post update shopping cart.
 */
class UpdatePost extends \Magento\Checkout\Controller\Cart\UpdatePost
{
    /**
     * @var RequestQuantityProcessor
     */
    private $quantityProcessor;
        /**
     * @var \Magento\Quote\Api\Data\AddressInterface
     */
    protected $address;

    /**
     * @var \Magento\Checkout\Api\ShippingInformationManagementInterface
     */
    protected $shippingInformationManagement;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $coreSession;
    
    /**
     * @var \Magento\Checkout\Api\Data\ShippingInformationInterface
     */
    protected $shippingInformation;
    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param \Magento\Checkout\Model\Cart $cart
     * @param RequestQuantityProcessor $quantityProcessor
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Framework\Session\SessionManagerInterface $coreSession,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Quote\Api\Data\AddressInterface $address,
        \Magento\Checkout\Api\ShippingInformationManagementInterface $shippingInformationManagement,
        \Magento\Checkout\Api\Data\ShippingInformationInterface $shippingInformation,
        RequestQuantityProcessor $quantityProcessor = null
    ) {
        parent::__construct(
            $context,
            $scopeConfig,
            $checkoutSession,
            $storeManager,
            $formKeyValidator,
            $cart
        );
        $this->customerSession = $customerSession;
        $this->coreSession = $coreSession;
        $this->address = $address;
        $this->shippingInformationManagement = $shippingInformationManagement;
        $this->shippingInformation = $shippingInformation;
        $this->quantityProcessor = $quantityProcessor ?: $this->_objectManager->get(RequestQuantityProcessor::class);
    }
    /**
     * Empty customer's shopping cart
     *
     * @return void
     */
    protected function _emptyShoppingCart()
    {
        try {
            $this->cart->truncate()->save();
            $this->_checkoutSession->clear();
        } catch (\Magento\Framework\Exception\LocalizedException $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
        } catch (\Exception $exception) {
            $this->messageManager->addExceptionMessage($exception, __('We can\'t update the shopping cart.'));
        }
    }

   
    /**
     * Update customer's shopping cart
     *
     * @return void
     */
    protected function _updateShoppingCart()
    {
        //echo '<pre>';print_r($this->getRequest()->getParams());echo '</pre>';exit;
        // $this->saveShippingInformation();
        // echo '<pre>';print_r($this->cart->getQuote()->getShippingAddress()->getData('country_id'));echo '</pre>';
        // echo '<pre>';print_r($this->cart->getQuote()->getShippingAddress()->getData('region_id'));echo '</pre>';
        // echo '<pre>';print_r($this->cart->getQuote()->getShippingAddress()->getData('city_id'));echo '</pre>';
        // echo '<pre>';print_r($this->cart->getQuote()->getShippingAddress()->getData('township_id'));echo '</pre>';
        // echo '<pre>';print_r($this->cart->getQuote()->getShippingAddress()->getData('postcode'));echo '</pre>';
        // exit;
        try {
            $cartData = $this->getRequest()->getParam('cart');
            $cart = $this->_objectManager->get('Magento\Checkout\Model\Cart');
            $productRepository = $this->_objectManager->get('Magento\Catalog\Api\ProductRepositoryInterface');
            $quoteFactory = $this->_objectManager->create('Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory');
            $quote = $cart->getQuote();
            if (is_array($cartData)) {
                $is_out_stock_product_main = false;
                $arrDefaultStockProductMain = array();
                foreach($cartData as $key => $value){
                    // Main
                    $quoteItemsMain = $quoteFactory->create()->addFieldToFilter('item_id', $key);
                    if (count($quoteItemsMain->getData()) > 0) {
                        foreach ($quoteItemsMain as $item) {
                            if(!isset($arrDefaultStockProductMain[$item->getSku()])){
                                $arrDefaultStockProductMain[$item->getSku()][] = (int)$value['qty'];
                            }else{
                                $arrDefaultStockProductMain[$item->getSku()][0] += (int)$value['qty'];
                            }
                        }
                    } 
                    // Gift
                    $quoteItemsGift = $quoteFactory->create()->addFieldToFilter('cart_promo_parent_item_id', $key);
                    if (count($quoteItemsGift->getData()) > 0) {
                        foreach ($quoteItemsGift as $item) {
                            // $this->writeLog('update $item->getId(): '.$item->getId());
                            $updateQtyPromo = (int)$value['qty'] * (int)$item->getCartPromoQty();
                            $qtyProduct = (int)$item->getQty();
                             // check default stock sp qtang dc chon
                            if (!empty($item->getCartPromoIds())) {
                                $product = $productRepository->get($item->getSku());
                                $defaultStockQty = true;
                                $defaultStockQty = $this->checkDefaultStockPromo($product, (int)$updateQtyPromo, $qtyProduct);
                                if (!$defaultStockQty) {
                                    return false;
                                }
                            }
                            $itemUpdate = $quote->getItemById($item->getId());
                            if (!$itemUpdate) {
                                continue;
                            }
                            $itemUpdate->setQty($updateQtyPromo);
                            $itemUpdate->save();
                        }
                    } 

                }
                if(count($arrDefaultStockProductMain) > 0){
                    foreach($arrDefaultStockProductMain as $key => $value){
                        $product = $productRepository->get($key);
                        $defaultStockMain = (int)$product->getDefaultStockCustom(); 
                        if($defaultStockMain < (int) $value[0]){
                            $is_out_stock_product_main = true;
                            break;
                        }
                    }
                }
              
                if(!$is_out_stock_product_main){
                    if (!$this->cart->getCustomerSession()->getCustomerId() && $this->cart->getQuote()->getCustomerId()) {
                        $this->cart->getQuote()->setCustomerId(null);
                    }
                    $cartData = $this->quantityProcessor->process($cartData);
                    $cartData = $this->cart->suggestItemsQty($cartData);
                    $this->cart->updateItems($cartData)->save();
                }else{
                    return false;
                }

                // update qty product config
                $productMainOfConfig = $quoteFactory->create()->addFieldToFilter('quote_id', array('eq' => $quote->getId()))
                                            ->addFieldToFilter('parent_item_id', array('notnull' => true));
                if (!empty($productMainOfConfig->getData()) && count($productMainOfConfig->getData()) > 0) {
                    // update qty product chinh cua product config
                    foreach ($productMainOfConfig as $item) {
                        if (!empty($item->getParentItemId())) {
                            $productConfig = $quote->getItemById($item->getParentItemId());
                            if (!$productConfig) {
                                continue;
                            }
                            if (!empty($productConfig->getQty())) {
                                $updateQty = $productConfig->getQty();
                                $productSimple = $quote->getItemById($item->getId());
                                $productSimple->setQty((int)$updateQty);
                                $productSimple->save();
                            }
                        }
                    }
                }
            }
            
            // save address shipping
            $this->saveShippingInformation();
           
           
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->writeLog($e);
            $this->messageManager->addErrorMessage(
                $this->_objectManager->get(\Magento\Framework\Escaper::class)->escapeHtml($e->getMessage())
            );
        } catch (\Exception $e) {
            $this->writeLog($e);
            $this->messageManager->addExceptionMessage($e, __('We can\'t update the shopping cart.'));
            $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
        }
    }

    public function checkDefaultStockPromo($product, $requestQty, $qtyProduct)
    {
        try {
            $defaultStockCustom = (int)$product->getDefaultStockCustom(); // so luong ton hien tai (real time)
            $sumQtyCurrentInQuoteItem = (int) $product->sumQtyCurrentInQuoteItem(); // sum qty cua product hien tai trong table quote_item (real time)
            $defaultStockQty = $defaultStockCustom - $sumQtyCurrentInQuoteItem;

            $defaultStock = (int) $defaultStockCustom - ((int) $sumQtyCurrentInQuoteItem + (int) $requestQty - (int) $qtyProduct);
            if ((int) $defaultStock < 0) {
                return false;
            }

        } catch (\Exception $e) {
            $this->writeLog($e);
            $this->messageManager->addExceptionMessage($e, __('We can\'t update the shopping cart.'));
            $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
        }
        return true;
    }

    /**
     * Save shipping information
     * 
     * @author: Tuan Nguyen
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function saveShippingInformation()
    {
        if ($this->_checkoutSession->getQuote()) {
            $cartId = $this->_checkoutSession->getQuote()->getId();
            $cartSkuArray = $this->getCartItemsSkus();
            $requestParams = $this->getRequest()->getParams();

            $customerSession = $this->customerSession->getCustomerData();

            if($customerSession){
                // get address-customer
                if(isset($requestParams['address-customer']) && $requestParams['type_select_address'] == 'no'){
                    $address_customer = json_decode($requestParams['address-customer']);
                    $requestParams['region_id'] = $address_customer->region_id;
                    $requestParams['region'] = $address_customer->region;
                    $requestParams['country_id'] = $address_customer->country_id;
                    $requestParams['city_id'] = $address_customer->city_id;
                    $requestParams['city'] = $address_customer->city;
                    $requestParams['township_id'] = $address_customer->township_id;
                    $requestParams['township'] = $address_customer->township;
                    $requestParams['street'] = $address_customer->street;
                }
            }

            $region_id = (isset($requestParams['region_id']) && $requestParams['region_id']) ? $requestParams['region_id']:'';
            $city_id = (isset($requestParams['city_id']) && $requestParams['city_id']) ? $requestParams['city_id']:'';

            if (
                $cartSkuArray && 
                (
                    ($region_id && $city_id) || 
                    isset($requestParams['address-customer'])
                )
            ) {
                $shippingAddress = $this->getShippingAddressInformation($requestParams);
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
    public function getShippingAddressInformation($requestParams) {
        $address_shipping = array();
        $price_quote_shipping_address_session = array();
        $customerSession = $this->customerSession->getCustomerData();
        
        if($customerSession){
            // get current price_quote_shipping_address
            $current_price_quote_shipping_address = $this->coreSession->getCartShippingAddress();

            $price_quote_shipping_address_session['type'] = $current_price_quote_shipping_address['type'] ? $current_price_quote_shipping_address['type'] : 'customer';
            $price_quote_shipping_address_session['action'] = $requestParams['type_select_address'];
            $price_quote_shipping_address_session['shipping-address'] = $current_price_quote_shipping_address['shipping-address'] ? $current_price_quote_shipping_address['shipping-address']:array();
            $price_quote_shipping_address_session['shipping-address-current'] = array();

            // check action page
            $requestParams['firstname'] = $customerSession->getFirstname();
            switch ($requestParams['type_select_address']) {
                case 'option':
                    // get address-customer
                    $address_customer = json_decode($requestParams['address-customer']);
                    $requestParams['region_id'] = $address_customer->region_id;
                    $requestParams['region'] = $address_customer->region;
                    $requestParams['country_id'] = $address_customer->country_id;
                    $requestParams['city_id'] = $address_customer->city_id;
                    $requestParams['city'] = $address_customer->city;
                    $requestParams['township_id'] = $address_customer->township_id;
                    $requestParams['township'] = $address_customer->township;
                    $requestParams['street'] = $address_customer->street;
                    break;

                default:
                    break;
            }

            // store address
            $address = array();

            $address['region_id'] = isset($requestParams['region_id']) ? $requestParams['region_id']:'';
            $region_name = '';
            if($address['region_id']){
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $region = $objectManager->create('Magento\Directory\Model\Region')
                                    ->load($address['region_id']);
                $region_name = $region->getName();
            }

            $address['region'] = $region_name;
            $address['country_id'] = isset($requestParams['country_id']) ? $requestParams['country_id']:'';
            $address['city_id'] = isset($requestParams['city_id']) ? $requestParams['city_id']:'';
            $address['city'] = isset($requestParams['city']) ? $requestParams['city']:'';
            $address['township_id'] = isset($requestParams['township_id']) ? $requestParams['township_id']:'';
            $address['township'] = isset($requestParams['township']) ? $requestParams['township']:'';
            $address['street'] = isset($requestParams['street']) ? $requestParams['street']:'';
            $address['firstname'] = isset($requestParams['firstname']) ? $requestParams['firstname']:'Customer';
            $address['full_address_tmp'] = $requestParams['street'].$requestParams['township_id'].$requestParams['city_id'].$requestParams['region_id'];

            // create full-address
            if ($address['street']) {
                $tmp_fulladdress['street'] = $address['street'];
            }
            if ($address['township']) {
                $tmp_fulladdress['township'] = $address['township'];
            }
            if ($address['city']) {
                $tmp_fulladdress['city'] = $address['city'];
            }
            if ($address['region']) {
                $tmp_fulladdress['region'] = $address['region'];
            }

            // store full-address to array
            $address['full_address'] = implode(', ', $tmp_fulladdress);

            $price_quote_shipping_address_session['shipping-address-current'] = $address;

            // check add request shipping to add to shipping-address
            $flag_insert = 1;
            foreach ($price_quote_shipping_address_session['shipping-address'] as $value) {
                if($address['region_id'] == $value['region_id'] && $address['city_id'] == $value['city_id'] && $address['township_id'] == $value['township_id'] && $address['street'] == $value['street']){
                    $flag_insert = 0;
                    break;
                }
            }
            // echo $flag_insert;exit;

            if($flag_insert){
                $price_quote_shipping_address_session['shipping-address'][] = $price_quote_shipping_address_session['shipping-address-current'];
            }

        }else{
            // guest
            $price_quote_shipping_address_session['type'] = 'guest';
            $price_quote_shipping_address_session['action'] = $requestParams['type_select_address'];
            $price_quote_shipping_address_session['shipping-address'] = array();
            $price_quote_shipping_address_session['shipping-address-current'] = array();


            // store address
            $address = array();
            $address['region_id'] = isset($requestParams['region_id']) ? $requestParams['region_id']:'';
            $address['region'] = isset($requestParams['region']) ? $requestParams['region']:'';
            $address['country_id'] = isset($requestParams['country_id']) ? $requestParams['country_id']:'';
            $address['city_id'] = isset($requestParams['city_id']) ? $requestParams['city_id']:'';
            $address['city'] = isset($requestParams['city']) ? $requestParams['city']:'';
            $address['township_id'] = isset($requestParams['township_id']) ? $requestParams['township_id']:'';
            $address['township'] = isset($requestParams['township']) ? $requestParams['township']:'';
            $address['street'] = isset($requestParams['street']) ? $requestParams['street']:'';
            $address['firstname'] = isset($requestParams['firstname']) ? $requestParams['firstname']:'guest';
            $address['full_address'] = $requestParams['street'].$requestParams['township_id'].$requestParams['city_id'].$requestParams['region_id'];
            $price_quote_shipping_address_session['shipping-address-current'] = $address;
            
        }
        // echo '<pre>';print_r($price_quote_shipping_address_session);echo '</pre>';exit;
        // store address to session
        if($price_quote_shipping_address_session){
            $this->coreSession->setCartShippingAddress($price_quote_shipping_address_session);
        }
        

        // check data before set shipping address
        if($requestParams['region_id'] && $requestParams['city_id']){
            $shippingAddress = $this->prepareShippingAddress($price_quote_shipping_address_session['shipping-address-current']);
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
        $first_name = $requestParams['firstname'] ? $requestParams['firstname']:'Guest';
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
        // foreach(get_class_methods($this->address) as $item){
        //     $this->writeLog($item);
        // }
        // $this->writeLog($city);
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
    /**
     * Update shopping cart data action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        if (!$this->_formKeyValidator->validate($this->getRequest())) {
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }

        $updateAction = (string)$this->getRequest()->getParam('update_cart_action');

        switch ($updateAction) {
            case 'empty_cart':
                $this->_emptyShoppingCart();
                break;
            case 'update_qty':
                $this->_updateShoppingCart();
                break;
            default:
                $this->_updateShoppingCart();
        }

        return $this->_goBack();
    }

    /**
     * Set back redirect url to response
     *
     * @param null|string $backUrl
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    protected function _goBack($backUrl = null)
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($backUrl || $backUrl = $this->getBackUrl($this->_redirect->getRefererUrl())) {
            $resultRedirect->setUrl($backUrl);
        }

        return $resultRedirect;
    }

    /**
     * Get resolved back url
     *
     * @param string|null $defaultUrl
     * @return mixed|null|string
     */
    protected function getBackUrl($defaultUrl = null)
    {
        $returnUrl = $this->getRequest()->getParam('return_url');
        if ($returnUrl && $this->_isInternalUrl($returnUrl)) {
            $this->messageManager->getMessages()->clear();
            return $returnUrl;
        }

        return $defaultUrl;
    }
    /**
	* @param $info
	* @param $type  [error, warning, info]
	* @return 
	*/
	private function writeLog($info, $type = "info"){
		$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/checkout-cart.log');
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
