<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Chottvn\Sales\Rewrite\Magento\Checkout\Block\Cart;

/**
 * Block on checkout/cart/index page to display a pager on the  cart items grid
 * The pager will be displayed if items quantity in the shopping cart > than number from
 * Store->Configuration->Sales->Checkout->Shopping Cart->Number of items to display pager and
 * custom_items weren't set to cart block
 *
 * @api
 * @since 100.1.7
 */
class Grid extends \Magento\Checkout\Block\Cart\Grid
{
    /**
     * Config settings path to determine when pager on checkout/cart/index will be visible
     */
    const XPATH_CONFIG_NUMBER_ITEMS_TO_DISPLAY_PAGER = 'checkout/cart/number_items_to_display_pager';

    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote\Item\Collection
     */
    private $itemsCollection;

    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory
     *
     */
    private $itemCollectionFactory;

    /**
     * @var \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface
     */
    private $joinAttributeProcessor;

    /**
     * Is display pager on shopping cart page
     *
     * @var bool
     */
    private $isPagerDisplayed;
     /**
     * @var \Magento\Checkout\Api\ShippingInformationManagementInterface
     */
    protected $shippingInformationManagement;

    /**
     * @var \Magento\Checkout\Api\Data\ShippingInformationInterface
     */
    protected $shippingInformation;
    /**
     * @var \Magento\Quote\Api\Data\AddressInterface
     */
    protected $address;
     /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $coreSession;

    protected $customerSession;
    /**
     * Grid constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Catalog\Model\ResourceModel\Url $catalogUrlBuilder
     * @param \Magento\Checkout\Helper\Cart $cartHelper
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory $itemCollectionFactory
     * @param \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $joinProcessor
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Catalog\Model\ResourceModel\Url $catalogUrlBuilder,
        \Magento\Checkout\Helper\Cart $cartHelper,
        \Magento\Quote\Api\Data\AddressInterface $address,
        \Magento\Framework\Session\SessionManagerInterface $coreSession,
        \Magento\Checkout\Api\ShippingInformationManagementInterface $shippingInformationManagement,
        \Magento\Checkout\Api\Data\ShippingInformationInterface $shippingInformation,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory $itemCollectionFactory,
        \Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface $joinProcessor,
        array $data = []
    ) {
        $this->coreSession = $coreSession;
        $this->shippingInformationManagement = $shippingInformationManagement;
        $this->shippingInformation = $shippingInformation;
        $this->address = $address;
        $this->customerSession = $customerSession;
        // $this->itemCollectionFactory = $itemCollectionFactory;
        // $this->joinAttributeProcessor = $joinProcessor;
        parent::__construct(
            $context,
            $customerSession,
            $checkoutSession,
            $catalogUrlBuilder,
            $cartHelper,
            $httpContext,
            $itemCollectionFactory,
            $joinProcessor,
            $data
        );
    }

    /**
     * Prepare Quote Item Product URLs
     * When we don't have custom_items, items URLs will be collected for Collection limited by pager
     * Pager limit on checkout/cart/index is determined by configuration
     * Configuration path is Store->Configuration->Sales->Checkout->Shopping Cart->Number of items to display pager
     *
     * @return void
     * @since 100.1.7
     */
    protected function _construct()
    {
        if (!$this->isPagerDisplayedOnPage()) {
            parent::_construct();
        }
        if ($this->hasData('template')) {
            $this->setTemplate($this->getData('template'));
        }
    }

    /**
     * {@inheritdoc}
     * @since 100.1.7
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->isPagerDisplayedOnPage()) {
            $availableLimit = (int)$this->_scopeConfig->getValue(
                self::XPATH_CONFIG_NUMBER_ITEMS_TO_DISPLAY_PAGER,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            $itemsCollection = $this->getItemsForGrid();
            /** @var  \Magento\Theme\Block\Html\Pager $pager */
            $pager = $this->getLayout()->createBlock(\Magento\Theme\Block\Html\Pager::class);
            $pager->setAvailableLimit([$availableLimit => $availableLimit])->setCollection($itemsCollection);
            $this->setChild('pager', $pager);
            $itemsCollection->load();
            $this->prepareItemUrls();
        }
        $cart_shipping_address = $this->coreSession->getCartShippingAddress();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $customerSession = $this->customerSession->getCustomerData();
      
        // if($this->_checkoutSession->getQuote()){

            if($this->customerSession->isLoggedIn()){
                if(($cart_shipping_address && $cart_shipping_address['type'] != 'customer') || empty($cart_shipping_address)){
                    $cart_shipping_address_session['type'] = 'customer';
                    $cart_shipping_address_session['action'] = 'option';
                    $cart_shipping_address_session['shipping-address'] = array();
                    $cart_shipping_address_session['shipping-address-current'] = array();

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

                    $cart_shipping_address_session['shipping-address'] = $customer_address;

                    // save to session
                    if($cart_shipping_address_session){
                        $this->coreSession->setCartShippingAddress($cart_shipping_address_session);
                    }
                }

                // save address shipping
                if($cart_shipping_address && $cart_shipping_address['shipping-address-current'] && $cart_shipping_address['type'] == 'customer'){
                    $shipping_address_session = $this->coreSession->getCartShippingAddress();
                    $this->saveShippingInformation($shipping_address_session);
                }
            }else{
                // save address shipping
                if($cart_shipping_address && $cart_shipping_address['shipping-address-current']){
                    $shipping_address_session = $this->coreSession->getCartShippingAddress();
                    $this->saveShippingInformation($shipping_address_session);
                }
            }

     
        return $this;
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
     * Prepare quote items collection for pager
     *
     * @return \Magento\Quote\Model\ResourceModel\Quote\Item\Collection
     * @since 100.1.7
     */
    public function getItemsForGrid()
    {
        if (!$this->itemsCollection) {
            /** @var \Magento\Quote\Model\ResourceModel\Quote\Item\Collection $itemCollection */
            $itemCollection = $this->itemCollectionFactory->create();
           
            $itemCollection->setQuote($this->getQuote());
            $itemCollection->addFieldToFilter('parent_item_id', ['null' => true]);
            $this->joinAttributeProcessor->process($itemCollection);

            $this->itemsCollection = $itemCollection;
        }
  
        return $this->itemsCollection;
    }
  
    /**
     * {@inheritdoc}
     * @since 100.1.7
     */
    public function getItems()
    {
        if (!$this->isPagerDisplayedOnPage()) {
            return parent::getItems();
        }
        return $this->getItemsForGrid()->getItems();
    }
    /**
     * @codeCoverageIgnore
     * @return int
     */
    public function getItemsCountCTT($quoteId)
    {
        $items_collection = $this->getQuote()->getItemsCollection();
        $items_collection->getSelect()
        ->reset(\Zend_Db_Select::COLUMNS)
        ->where('main_table.quote_id = ?',$quoteId)
        ->where('main_table.cart_promo_option IS NULL')
        //->where('main_table.free_shipping = ?', '0') // Can kiem tra lai cho nay, Huan chi muon count sp khong phai qua tang
        ->where('main_table.product_type != ?', 'configurable')
        ->columns("sum(main_table.qty) as sum");
      
        $sum = 0;
        if(isset($items_collection->getData()[0]['sum'])){
            $sum = (int) $items_collection->getData()[0]['sum'];
        }

        return $sum;
    }

    /**
     * Verify if display pager on shopping cart
     * If cart block has custom_items and items qty in the shopping cart<limit from stores configuration
     *
     * @return bool
     */
    private function isPagerDisplayedOnPage()
    {
       
        if (!$this->isPagerDisplayed) {
            $availableLimit = (int)$this->_scopeConfig->getValue(
                self::XPATH_CONFIG_NUMBER_ITEMS_TO_DISPLAY_PAGER,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            $this->isPagerDisplayed = !$this->getCustomItems() && $availableLimit < $this->getItemsCount();
        }
        return $this->isPagerDisplayed;
    }
        /**
     * Get active quote
     *
     * @return Quote
     */
    public function getQuote()
    {
        /*if (null === $this->_quote) {
            $this->_quote = $this->_checkoutSession->getQuote();
        }*/
        $this->_quote = $this->_checkoutSession->getQuote();
        return $this->_quote;        
    }

    /**
     * Get active quote
     *
     * @return Quote
     */
    public function getShippingAddress()
    {
        return $this->getQuote()->getShippingAddress();
    }
      /**
	* @param $info
	* @param $type  [error, warning, info]
	* @return 
	*/
	private function writeLog($info, $type = "info"){
		$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/grid-cart.log');
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
