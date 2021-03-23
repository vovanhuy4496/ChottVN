<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Chottvn\PriceQuote\Controller\Cart;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Controller for processing add to cart action.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Add extends \Magento\Checkout\Controller\Cart implements HttpPostActionInterface
{
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param CustomerCart $cart
     * @param ProductRepositoryInterface $productRepository
     * @codeCoverageIgnore
     */
    protected $address;
    protected $shippingInformation;
    protected $shippingInformationManagement;
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        CustomerCart $cart,
        \Magento\Quote\Api\Data\AddressInterface $address,
        \Magento\Checkout\Api\ShippingInformationManagementInterface $shippingInformationManagement,
        \Magento\Checkout\Api\Data\ShippingInformationInterface $shippingInformation,
        ProductRepositoryInterface $productRepository
    ) {
        parent::__construct(
            $context,
            $scopeConfig,
            $checkoutSession,
            $storeManager,
            $formKeyValidator,
            $cart
        );
        $this->address = $address;
        $this->shippingInformationManagement = $shippingInformationManagement;
        $this->shippingInformation = $shippingInformation;
        $this->productRepository = $productRepository;
    }

    /**
     * Add product to shopping cart action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        if (!$this->_formKeyValidator->validate($this->getRequest())) {
            $this->messageManager->addErrorMessage(
                __('Your session has expired')
            );
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }
       
        $requestId = $this->getRequest()->getParam('request_id');
        $formKey = $this->getRequest()->getParam('form_key');
        $itemsRequest = $this->getCollectionItems($requestId)->getData();
        $request = $this->getCollectionRequest($requestId)->getData();
        $is_clean_current_cart = (boolean) $this->getRequest()->getParam('check');

        // attributes options product
        $arrAttributeOptions = array();
        $arrAttributeOptions = $this->getAttributeOptions($itemsRequest);
        // clear cart
        if($is_clean_current_cart){
            $this->_emptyShoppingCart();
        }
        $totalItem = 0;
        $totalQty = 0;
        try {
            if(!empty($itemsRequest) && count($itemsRequest) > 0){
                foreach($itemsRequest as $item){
                    $productType = $item['product_type'];
                    $cartPromoOption = $item['cart_promo_option'];
                    $parentItemId = $item['parent_item_id'];
                    if(!$parentItemId){   
                        // add to cart
                        $productId = $item['product_id'];
                        $product = $this->_initProduct($productId);
                        $this->addToCart($item,$product,$formKey,$arrAttributeOptions);
                        $this->_eventManager->dispatch(
                            'checkout_cart_add_product_complete',
                            ['product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse()]
                        );

                        if ($product && !$product->getIsSalable()) {
                        
                            $message = __(
                                'Out of stock',
                                $product->getName()
                            );
                            $this->messageManager->addErrorMessage($message);
                        }
                    }   
                    // $this->writeLog('#name');
                    // $this->writeLog($item['name']);
                    // $params = array (
                    //     'product' => $item['product_id'],
                    //     'item' => $item['product_id'],
                    //     'form_key' => $formKey,
                    //     'qty' => $item['qty'],
                    // );
    
                    // if (isset($params['qty'])) {
                    //     $filter = new \Zend_Filter_LocalizedToNormalized(
                    //         ['locale' => $this->_objectManager->get(
                    //             \Magento\Framework\Locale\ResolverInterface::class
                    //         )->getLocale()]
                    //     );
                    //     $params['qty'] = $filter->filter($params['qty']);
                    // }
                    // $product = $this->_initProduct($item['product_id']);
                    // /**
                    //  * Check product availability
                    //  */
                    // if (!$product) {
                    //     return $this->goBack();
                    // }
                    // $this->cart->addProduct($product, $params);
                     /**
                     * @todo remove wishlist observer \Magento\Wishlist\Observer\AddToCart
                     */
                }
            }
            
            // $this->cart->save();
            if (!$this->_checkoutSession->getNoCartRedirect(true)) {
                // if (!$this->cart->getQuote()->getHasError()) {
                //     $message = __(
                //         'You have added the product to the cart.'
                //     );
                //     $this->messageManager->addSuccessMessage($message);
                // }   
                // save address shipping
                if(!$is_clean_current_cart){
                    $this->saveShippingInformation($request);
                }
                $this->writeLog("####Add to cart Success");
                return $this->resultRedirectFactory->create()->setPath('checkout');
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->writeLog($e);
            if ($this->_checkoutSession->getUseNotice(true)) {
                $this->messageManager->addNoticeMessage(
                    $this->_objectManager->get(\Magento\Framework\Escaper::class)->escapeHtml($e->getMessage())
                );
            } else {
                $messages = array_unique(explode("\n", $e->getMessage()));
                foreach ($messages as $message) {
                    $this->messageManager->addErrorMessage(
                        $this->_objectManager->get(\Magento\Framework\Escaper::class)->escapeHtml($message)
                    );
                }
            }
            return $this->resultRedirectFactory->create()->setPath('price_quote');
        } catch (\Exception $e) {
            $this->writeLog($e);
            $this->messageManager->addExceptionMessage(
                $e,
                __('We can\'t add this item to your shopping cart right now.')
            );
            $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
            return $this->goBack();
        }
    }

    /**
     * Initialize product instance from request data
     *
     * @return \Magento\Catalog\Model\Product|false
     */
    protected function _initProduct($productid)
    {
        $productId = (int)$productid;
        if ($productId) {
            $storeId = $this->_objectManager->get(
                \Magento\Store\Model\StoreManagerInterface::class
            )->getStore()->getId();
            try {
                return $this->productRepository->getById($productId, false, $storeId);
            } catch (NoSuchEntityException $e) {
                return false;
            }
        }
        return false;
    }
    public function getCollectionItems($request){
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $requestItemsFactory = $objectManager->create('\Chottvn\PriceQuote\Model\ResourceModel\Items\CollectionFactory');
        $collection = $requestItemsFactory->create();
        $collection->addFieldToFilter('main_table.request_id', ['eq' => $request])
        ->addFieldToFilter('main_table.cart_promo_option', array('null' => true));
        $this->writeLog($collection->getSelect()->__toString());
        return $collection;
    }
    public function getCollectionRequest($request){
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $requestFactory = $objectManager->create('\Chottvn\PriceQuote\Model\ResourceModel\Request\CollectionFactory');
        $collection = $requestFactory->create();
        $collection->addFieldToFilter('main_table.request_id', ['eq' => $request]);
        $lastItem = $collection->getLastItem();
        return $lastItem;
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
        } catch (\Magento\Framework\Exception\LocalizedException $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
        } catch (\Exception $exception) {
            $this->messageManager->addExceptionMessage($exception, __('We can\'t update the shopping cart.'));
        }
    }
    /**
     * Add to cart
     *
     * @return void
     */
    protected function addToCart($item,$product,$formKey,$arrAttributeOptions)
    {
        try {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            // if (isset($params['qty'])) {
            //     $filter = new \Zend_Filter_LocalizedToNormalized(
            //         ['locale' => $this->_objectManager->get('Magento\Framework\Locale\ResolverInterface')->getLocale()]
            //     );
            //     $params['qty'] = $filter->filter($params['qty']);
            // }
            
            $productIdConfigSelected = null;
            // Prepare Helper
            $quoteItemCollectionMainProductFactory = $objectManager->create('Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory');
            $blockRules = $objectManager->create('Chottvn\Frontend\Block\Rules');
          
            /**
             * Check product availability
             */
            if (!$product) {
                return $this->goBack();
            }
            
            $params = array (
                'product' => $item['product_id'],
                'item' => $item['product_id'],
                'form_key' => $formKey,
                'qty' => $item['qty'],
            );

            $params['qty'] = isset($params['qty']) ? (int)$params['qty'] : 1;

            // --- Qty product main
            $qtyProductMain = $params['qty'];

            // --- Param Gift
            $qtyProductsGift = array();
            $skusGift = array();
            $cartPromoRuleIds = array();
            $allRuleProduct = $blockRules->getCartRuleIdsConditionSimpleActionByProductCTT($product,'ampromo_items');
            $is_gift_out_of_stock = false;
            if(!empty($allRuleProduct) && count($allRuleProduct) > 0){
                foreach($allRuleProduct as $_item){
                    if(isset($_item['sku'])){
                        foreach($_item['sku'] as  $idx => $sku) {                    
                            $productGiftObj = $this->productRepository->get($sku);
                            if(!empty($productGiftObj)){   
                                // Check defaultStock gift    
                                $qtyGift = $qtyProductMain * (int)$_item['qty'][$idx];
                                $defaultStockQty = $this->checkDefaultStockPromo($productGiftObj, $qtyGift);
                                if (!empty($defaultStockQty)) {
                                    continue;
                                } else {
                                    array_push($qtyProductsGift,(int)$_item['qty'][$idx]);
                                    array_push($skusGift,$sku);
                                    array_push($cartPromoRuleIds,$_item['rule_id'][$idx]);
                                    break;
                                }
                            }                    
                        }
                    }
                }
                if(count($skusGift) == 0){
                    $is_gift_out_of_stock = true;
                    $mainProductName = $item['product_name_short'] ? $item['product_name_short']: $item['name'] ;
                    $message = __('All gift of %1 are out of stock',$mainProductName);
                    return $this->_jsonResponse($message);
                }
            }    
            if($is_gift_out_of_stock == false){
                $configurableProductModel = $objectManager->create('Magento\ConfigurableProduct\Model\Product\Type\Configurable');
                // Check stock - main_product
                if ($product->getTypeId() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
                    if(!empty($arrAttributeOptions) && count($arrAttributeOptions) > 0){
                        foreach($arrAttributeOptions as $_key => $_item){
                            if(isset($item['item_id'])){
                                if($_key == $item['item_id']){
                                    // $this->writeLog($_item);
                                    if(!empty($_item) && count($_item) > 0){
                                        $simpleProductSelected = $configurableProductModel->getProductByAttributes($_item, $product);
                                        $simpleProductSelectedId = $simpleProductSelected->getId();
                                        $productIdConfigSelected = $simpleProductSelectedId;
                                        $simpleProductObj = $objectManager->create('Magento\Catalog\Model\Product')->load($simpleProductSelectedId);
                                        //$this->writeLog('SimpleProductSelected: '.$simpleProductObj->getName());
                                        $defaultStockQty = $this->checkDefaultStock($simpleProductObj, $qtyProductMain);
                                        break;
                                    }
                                }
                            }
                        }
                    }
                } else {
                    $defaultStockQty = $this->checkDefaultStock($product, $qtyProductMain);
                }
                if (!empty($defaultStockQty)) {
                    // $this->writeLog('DefaultStockQtyMain: ');
                    // $this->writeLog($defaultStockQty);
                    return $this->_jsonResponse($defaultStockQty);
                }
    
                // Parse params
                // -- Product Name short
                $productNameShort = $product->getNameShort();
                
                $quote = $this->cart->getQuote();
                $quoteId = $quote->getId();
                $quoteItems = $quote->getAllVisibleItems();
    
                // Get promo product IDs from SKUs
                $promoProductIds = array();            
                if (count($skusGift) > 0) {
                    foreach($skusGift as  $idx => $sku) {                    
                        $productGiftObj = $this->productRepository->get($sku);
                        if(!empty($productGiftObj)){   
                            // Check defaultStock gift    
                            $qtyGift = $qtyProductMain * (int)$qtyProductsGift[$idx];
                            $defaultStockQty = $this->checkDefaultStockPromo($productGiftObj, $qtyGift);
                            // if (!empty($defaultStockQty)) {
                            //     $this->writeLog('DefaultStockQtyGift: ');
                            //     $this->writeLog($defaultStockQty);
                            //     return $this->_jsonResponse($defaultStockQty);
                            // } else {
                                $productId = $productGiftObj->getId();
                                array_push($promoProductIds, $productId);
                            // }
                        }                    
                    }
                }            
                $productIdsAll = array_merge([$product->getId()], $promoProductIds);
    
                // Prepare promo tracking fields
                $cartPromoItemIdsTuple = ''; // Product Ids String
    
                if (count($promoProductIds) > 0) {
                    $cartPromoItemIdsTuple = implode(',', $promoProductIds);
                }
                // add super_attribute for product config
                if(!empty($arrAttributeOptions) && count($arrAttributeOptions) > 0){
                    foreach($arrAttributeOptions as $_key => $_item){
                        if(isset($item['item_id']) && $_key == $item['item_id']){
                            $params['super_attribute'] = $_item;
                            break;
                        }
                    }
                }
                $this->writeLog('Quote: '.$quoteId.' >> cartPromoItemIds: '.$cartPromoItemIdsTuple);
                if(empty($cartPromoItemIdsTuple)){ // If empty gift
                    // Add main product
                    // $this->writeLog('#If empty gift');
                    $this->cart->addProduct($product,$params);
                    // -- Add related products
                    // if (!empty($relatedProductIds)) {
                    //     $this->cart->addProductsByIds($relatedProductIds);
                    // }
                    $this->cart->save();
                }else{ // Has gift
                    // Proceed to add items to carts
                    if ($this->checkProductMainExists($quoteId, $product->getId(), $cartPromoItemIdsTuple, $productIdConfigSelected) ) { 
                        // Tuple (product-gifts) exists >> Update quantity only 
                        // $this->writeLog('#Before Tuple (product-gifts) exists >> Update quantity only');
                        $quoteItemIdMainProduct = $this->getQuoteItemIdMainProduct($quoteId, $quoteItems, $product->getId(), $productIdConfigSelected, $cartPromoItemIdsTuple);
                        foreach ($quoteItems as $quoteItem) {
                            /*
                             * Check: 
                             * - cart_promo_item_ids
                             * - cart_promo_parent_id 
                             *   + if configurable_product >> may be the same
                             *   + if simple_product >> difference
                             * - cart_promo_parent_item_id >> quote_item_id
                             *   + if configurable_product >> quote_item_id of it
                             */
                            if($quoteItem->getCartPromoItemIds() == $cartPromoItemIdsTuple){
                                if ($quoteItem->getId() == $quoteItemIdMainProduct){// Main product
                                    $qtyNew = $qtyProductMain + $quoteItem->getQty();
                                    $quoteItem->setQty($qtyNew);
                                }else{
                                    if($quoteItem->getCartPromoParentItemId() == $quoteItemIdMainProduct){
                                        $qtyNew = ($qtyProductMain * floatval($quoteItem->getCartPromoQty()) ) + $quoteItem->getQty();
                                        $quoteItem->setQty($qtyNew);
                                    }
                                }                    
                            }
                        }                    
                        $this->cart->save();
                        // $this->writeLog('#After Tuple (product-gifts) exists >> Update quantity only');
                    }else{ 
                        // Tuple (product-gifts) not exists >> Add new
                        // $this->writeLog('#Before Tuple (product-gifts) not exists >> Add new');
                        $giftInfo = [
                            "qty_product_main" => $qtyProductMain,
                            "cart_promo_option" => "ampromo_items",
                            "cart_promo_item_ids" => $cartPromoItemIdsTuple,
                            "items" => []
                        ];
                        $giftItems = [];
                        foreach ($promoProductIds as $idx => $promoProductId) {
                            $giftItem = [
                                "product_id" => $promoProductId,
                                "cart_promo_ids" => $cartPromoRuleIds[$idx],
                                "cart_promo_qty" => $qtyProductsGift[$idx]
                            ];
                            array_push($giftItems, $giftItem);
                        }
                        $giftInfo["items"] = $giftItems;
                        $this->cart->addProductWithGift($product, $params, $giftInfo);
                        // if (!empty($relatedProductIds)) {
                        //     $this->cart->addProductsByIds($relatedProductIds);
                        // }
                        $this->cart->save();  
                        // $this->writeLog('#After Tuple (product-gifts) not exists >> Add new');        
                        /* Update parent_item_id
                        if ($this->cartHelper->getItemsCount() !== 0) {
                            $quoteFactory = $objectManager->create('Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory');
                            // update cart_promo_parent_item_id for product promo
                            $mainProduct = $quoteFactory->create()->addFieldToFilter('quote_id', $quoteId)
                                                                ->addFieldToFilter('product_id', $product->getId())
                                                                ->addFieldToFilter('cart_promo_item_ids', $cartPromoItemIdsTuple);
                            $getMainProduct = $mainProduct->getLastItem();
                            $fieldItemId = $getMainProduct->getId();
                            $this->writeLog('item_id product main: '.$fieldItemId);
                            $promoProducts = $quoteFactory->create()->addFieldToFilter('quote_id', $quoteId)
                                                                ->addFieldToFilter('cart_promo_item_ids', $cartPromoItemIdsTuple)
                                                                ->addFieldToFilter('cart_promo_option', array('eq' => 'ampromo_items'));
                            foreach($promoProducts as $item) {
                                $item->setCartPromoParentItemId($fieldItemId);
                                $item->save();
                            }
                        }
                        */
                    }
                }
                // update product configurations
                $this->updateQtyProductConfig($quote);
            }
           
        } catch (\Exception $exception) {
            $this->writeLog('#function addToCart');
            $this->writeLog($exception);
        }
    }  
    /**
    * update Qty Product Config
    *
    * @return void
    */
   protected function updateQtyProductConfig($quote)
   {
       try {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $quoteFactory = $objectManager->create('Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory');
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

       } catch (\Exception $exception) {
            $this->writeLog('#Function updateQtyProductConfig');
           $this->writeLog($exception);
       }
    }
    /**
     * get Attribute Options
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    protected function getAttributeOptions($itemQuotePrice)
    {
        $arrOptionAttribute = array();
        try {
            foreach($itemQuotePrice as $_item){
                $parentItemId = $_item['parent_item_id'];
                $itemId = $_item['item_id'];
                if(!$parentItemId){   
                    $productOptions = $_item['product_options'];
                    if($productOptions){
                        $arrOptionAttribute[$itemId] = json_decode($productOptions,true);
                    }
                }
             }
        } catch (\Exception $exception) {
            $this->writeLog('#function getAttributeOptions');
            $this->writeLog($exception);
        }
        $this->writeLog($arrOptionAttribute);
        return $arrOptionAttribute;
    }

    protected function _jsonResponse($result)
    {
        return $this->messageManager->addErrorMessage($result);
    }
     /**
     * Set back redirect url to response
     *
     * @param null|string $backUrl
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    protected function goBack($backUrl = null)
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($backUrl || $backUrl = $this->getBackUrl($this->_redirect->getRefererUrl())) {
            $resultRedirect->setUrl($backUrl);
        }

        return $resultRedirect;
    }
    /**
     * Returns cart url
     *
     * @return string
     */
    private function getCartUrl()
    {
        return $this->_url->getUrl('checkout/cart', ['_secure' => true]);
    }

    /**
     * Is redirect should be performed after the product was added to cart.
     *
     * @return bool
     */
    private function shouldRedirectToCart()
    {
        return $this->_scopeConfig->isSetFlag(
            'checkout/cart/redirect_to_cart',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
   /**
     * Save shipping information
     * 
     * @author: Tuan Nguyen
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function saveShippingInformation($requestParams)
    {
        if ($this->cart->getQuote()) {
            // $this->writeLog(json_encode($this->cart->getQuote()->getData()));
            $cartId = $this->cart->getQuote()->getId();
            $cartSkuArray = $this->getCartItemsSkus();
            $region_id = $requestParams['region_id'];
            $city_id = $requestParams['city_id'];
           
            if (
                $cartSkuArray &&  ($region_id && $city_id)
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
        $arr = array();
        $shipping_method = $requestParams['shipping_method'] ?  $requestParams['shipping_method'] : 'tablerate_bestway';
        $arr = explode("_", $shipping_method);
        // check data before set shipping address
        if($requestParams['region_id'] && $requestParams['city_id']){
            $shippingAddress = $this->prepareShippingAddress($requestParams);
            $address_shipping = $this->shippingInformation->setShippingAddress($shippingAddress)
            ->setShippingCarrierCode($arr[0])
            ->setShippingMethodCode($arr[1]);
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
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $session = $objectManager->create("Magento\Customer\Model\Session");
        $sessionCustomer = $session->getCustomer();
        $first_name = $sessionCustomer->getFirstname();
        $last_name = $sessionCustomer->getLastname();;
        $street = $requestParams['street'];
        $country_id = $requestParams['country_id'];
        $region = $requestParams['region'];
        $region_id = $requestParams['region_id'];
        $city_id = $requestParams['city_id'];
        $city = $requestParams['city'];
        $township_id = $requestParams['township_id'];
        $township = $requestParams['township'];
        $postcode = $requestParams['postcode'];

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
     * get Cart Items SKus
     *
     * @author: Tuan Nguyen
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function getCartItemsSkus() {
        $cartSkuArray = [];
        $cartItems = $this->cart->getQuote()->getAllVisibleItems();
        foreach ($cartItems as $product) {
            $cartSkuArray[] = $product->getSku();
        }
        return $cartSkuArray;
    }
    public function getSaleableQtyBySku($sku){
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $stockResolver = $objectManager->get('\Magento\InventorySalesApi\Api\StockResolverInterface');
        $productSalableQty = $objectManager->get('\Magento\InventorySalesApi\Api\GetProductSalableQtyInterface');
        $websiteCode = $storeManager->getWebsite()->getCode();
        $stock = $stockResolver->execute(\Magento\InventorySalesApi\Api\Data\SalesChannelInterface::TYPE_WEBSITE, $websiteCode);
        $stockId = $stock->getStockId();      
        $qty =  $productSalableQty->execute($sku, $stockId);
        return $qty > 0 ? $qty : 0;
    }

    public function checkDefaultStock($product, $requestQty)
    {
        $result = array();
        $defaultStockCustom = $product->getDefaultStockCustom(); // so luong ton hien tai (real time)
        //$this->writeLog('getDefaultStockCustom: '.$getDefaultStockCustom);
        $sumQtyCurrentInQuoteItem = $product->sumQtyCurrentInQuoteItem(); // sum qty cua product hien tai trong table quote_item (real time)
        //$this->writeLog('sumQtyCurrentInQuoteItem: '.$sumQtyCurrentInQuoteItem);
        $defaultStockQty = $defaultStockCustom - $sumQtyCurrentInQuoteItem;

        if ($defaultStockCustom == 0 || $defaultStockQty == 0) {
            $result =  __('Sản phẩm '.$product->getNameShort(). ' tạm hết hàng.');
            // $result['error_code'] = 'out-of-stock';
            // $result['success'] = false;
            return $result;
            // return $this->_jsonResponse($result);
        }
        $defaultStock = $defaultStockCustom - ($sumQtyCurrentInQuoteItem + $requestQty);
        if ($defaultStock < 0) {
            $result = __('Chỉ còn ' . $defaultStockQty . ' sản phẩm');
            // $result['error_code'] = 'contact-us';
            // $result['success'] = false;
            return $result;
            // return $this->_jsonResponse($result);
        }
        return $result;
    }

    public function checkDefaultStockPromo($product, $requestQty)
    {
        $result = '';
        $defaultStockCustom = $product->getDefaultStockCustom(); // so luong ton hien tai (real time)
        $sumQtyCurrentInQuoteItem = $product->sumQtyCurrentInQuoteItem(); // sum qty cua product hien tai trong table quote_item (real time)
        $defaultStockQty = $defaultStockCustom - $sumQtyCurrentInQuoteItem;

        if ($defaultStockCustom == 0 || $defaultStockQty == 0) {
            $result =  __('Sản phẩm quà tặng '.$product->getNameShort(). ' tạm hết hàng.');
            // $result['error_code'] = 'out-of-stock-promo';
            // $result['rule_id'] = $ruleId;
            // $result['product_id'] = $product->getId();
            // $result['success'] = false;

            return $result;
        }
        $defaultStock = $defaultStockCustom - ($sumQtyCurrentInQuoteItem + $requestQty);
        if ($defaultStock < 0) {
            $result =  __('Sản phẩm quà tặng '.$product->getNameShort(). ' chỉ còn ' . $defaultStockCustom . ' sản phẩm');
            // $result['error_code'] = 'contact-us-promo';
            // $result['rule_id'] = $ruleId;
            // $result['product_id'] = $product->getId();
            // $result['success'] = false;

            return $result;
        }
        return $result;
    }

    private function checkProductMainExists($quoteId, $productId, $cartPromoItemIdsTuple, $productIdConfigSelected){
        if(empty($quoteId)){
            return false;
        }
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $connection = $objectManager->get('\Magento\Framework\App\ResourceConnection');
        $conn = $connection->getConnection();
        $isExisted = false;
        if(empty($productIdConfigSelected)){
            $sqlQuery = "
                SELECT *
                FROM quote_item
                WHERE quote_id = $quoteId
                  AND product_id = $productId
                  AND cart_promo_item_ids = $cartPromoItemIdsTuple
            ";    
            $this->writeLog($sqlQuery);        
            $binds = [];
            $data  = $conn->fetchRow($sqlQuery, $binds);
            $isExisted = empty($data) ? false : true;
        }else{
            $sqlQuery = "
                SELECT *
                FROM quote_item
                WHERE quote_id = $quoteId
                    AND product_id = $productIdConfigSelected
                    AND parent_item_id IN (
                        SELECT item_id
                        FROM quote_item
                        WHERE quote_id = $quoteId
                          AND product_id = $productId
                          AND cart_promo_item_ids = $cartPromoItemIdsTuple
                            )
            ";
            $this->writeLog($sqlQuery);
            $binds = [];
            $data  = $conn->fetchRow($sqlQuery, $binds);
            $isExisted = empty($data) ? false : true;
        }            
        return $isExisted;
    }

    private function checkProductMainExistsOLD($quoteId, $productId, $cartPromoItemIdsTuple, $productIdConfigSelected){
        if(empty($quoteId)){
            return false;
        }
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $connection = $objectManager->get('\Magento\Framework\App\ResourceConnection');
        $conn = $connection->getConnection();
        $isExisted = false;
        $sqlQuery = "
            SELECT *
            FROM quote_item
            WHERE quote_id = $quoteId
              AND product_id = $productId
              AND cart_promo_item_ids = $cartPromoItemIdsTuple
        ";
        $binds = [];
        $data  = $conn->fetchRow($sqlQuery, $binds);
        $this->writeLog("---1: ");      
        $this->writeLog($sqlQuery);
        if(!empty($productIdConfigSelected)){
            if(empty($data)){
                $isExisted = false;
            }else{                
                // Check config product      
                $itemIdParent = $data['item_id'];
                $sqlQuery = "
                    SELECT *
                    FROM quote_item
                    WHERE quote_id = $quoteId
                        AND product_id = $productIdConfigSelected
                        AND parent_item_id = $itemIdParent
                ";
                $binds = [];
                $data  = $conn->fetchRow($sqlQuery, $binds);
                $this->writeLog("---2: ");  
                $this->writeLog($sqlQuery);
                $isExisted = empty($data) ? false : true;
            }
        }else{
            $isExisted = empty($data) ? false : true;
        }    
        return !empty($data);
    }

    /*
    private function getQuoteItemIdProductMainRecent($quoteId, $productId){
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $connection = $objectManager->get('\Magento\Framework\App\ResourceConnection');
        $conn = $connection->getConnection();

        $sqlQuery = "
            SELECT item_id
            FROM quote_item
            WHERE quote_id = $quoteId
              AND product_id = $productId
              AND cart_promo_item_ids IS NULL 
              AND applied_rule_ids IS NOT NULL
        ";
        $binds = [];
        $data  = $conn->fetchRow($sqlQuery, $binds);
        return $data ? $data["item_id"] : null;
    } 
    */

    private function getQuoteItemId($quoteId, $productId, $cartPromoItemIdsTuple){
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $connection = $objectManager->get('\Magento\Framework\App\ResourceConnection');
        $conn = $connection->getConnection();

        $sqlQuery = "
            SELECT item_id
            FROM quote_item
            WHERE quote_id = $quoteId
              AND product_id = $productId
              AND cart_promo_item_ids = $cartPromoItemIdsTuple
        ";
        $binds = [];
        $data  = $conn->fetchRow($sqlQuery, $binds);
        return $data ? $data["item_id"] : null;
    } 

    private function getQuoteItemsFull($quoteId){
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $connection = $objectManager->get('\Magento\Framework\App\ResourceConnection');
        $conn = $connection->getConnection();

        $sqlQuery = "
            SELECT *
            FROM quote_item
            WHERE quote_id = $quoteId              
        ";
        $binds = [];
        $data  = $conn->fetchAll($sqlQuery, $binds);
        return $data ? $data : [];
    }

    private function getQuoteItemIdMainProduct($quoteId, $quoteItems, $productId, $productIdConfigSelected, $cartPromoItemIdsTuple){
        $quoteItemId = null;        
        
        if (empty($productIdConfigSelected)){ // Simple            
             // Loop again to check tuple
            foreach ($quoteItems as $quoteItem) {
                if ( ($quoteItem->getCartPromoItemIds() == $cartPromoItemIdsTuple) 
                    && ($quoteItem->getProductId() == $productId )
                ){
                    $quoteItemId = $quoteItem->getId();
                }
            }

        }else{ // Configurable
            /*
             * quoteItems does not include selected item ids
             */
            $quoteItemsFull = $this->getQuoteItemsFull($quoteId);            
            $quoteItemIdCandidates = array();
            // Loop to find quote item id
            foreach ($quoteItemsFull as $quoteItem) {                
                if(!empty($quoteItem["parent_item_id"]) ){
                    if($quoteItem["product_id"] == $productIdConfigSelected){
                        array_push($quoteItemIdCandidates, $quoteItem["parent_item_id"]);
                    }
                }            
            }
            // Loop again to check tuple
            foreach ($quoteItems as $quoteItem) {
                if ( ($quoteItem->getCartPromoItemIds() == $cartPromoItemIdsTuple) 
                    && ($quoteItem->getProductId() == $productId )
                    && in_array($quoteItem->getId(), $quoteItemIdCandidates)
                ){
                    $quoteItemId = $quoteItem->getId();
                }
            }
        }        
        return $quoteItemId;
    }

    private function getQuoteItemIdConfigurableMainProduct($quoteItems, $productId, $productIdConfigSelected, $cartPromoItemIdsTuple){
        $quoteItemId = null;
        $quoteItemIdCandidates = array();
        // Loop to find quote item id
        foreach ($quoteItems as $quoteItem) {
            if(!empty($quoteItem->getParentItemId()) ){
                if($quoteItem->getProductId() == $productIdConfigSelected){
                    array_push($quoteItemIdCandidates, $quoteItem->getParentItemId());
                }
            }            
        }
        // Loop again to check tuple
        foreach ($quoteItems as $quoteItem) {
            if ( ($quoteItem->getCartPromoItemIds() == $cartPromoItemIdsTuple) 
                && $quoteItem->getProductId() == $productId 
                && in_array($quoteItem->getId(), $quoteItemIdCandidates)){
                $quoteItemId = $quoteItem->getId();
            }

        }
        return $quoteItemId;
    }

    private function isQuoteItemCurrentMainProduct($quoteItem, $productId, $productIdConfigSelected, $cartPromoItemIdsTuple ) {        
        if (empty($productIdConfigSelected)){ // Simple Product
            return ($quoteItem->getCartPromoItemIds() == $cartPromoItemIdsTuple)
                && ($quoteItem->getProductId() == $productId );
        }else{ // Configurable Product

        }
    } 

    private function isQuoteItemCurrentGiftProduct($quoteItem, $productId, $productIdConfigSelected, $cartPromoItemIdsTuple ) {
        if (empty($productIdConfigSelected)){ // Simple Product
            return ($quoteItem->getCartPromoItemIds() == $cartPromoItemIdsTuple)
                && $quoteItem->getProductId() != $product->getId();
        }else{ // Configurable Product
            
        }        
    }  
    /**
	* @param $info
	* @param $type  [error, warning, info]
	* @return 
	*/
	private function writeLog($info, $type = "info"){
		$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/add.log');
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
