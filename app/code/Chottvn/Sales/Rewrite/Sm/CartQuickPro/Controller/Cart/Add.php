<?php
/**
 * Copyright © (c) Cho Truc Tuyen 2020 All rights reserved.
 * See COPYING.txt for license details.
 */
 
namespace Chottvn\Sales\Rewrite\Sm\CartQuickPro\Controller\Cart;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Add extends \Sm\CartQuickPro\Controller\Cart\Add
{
    /**
     * Initialize product instance from request data
     *
     * @return \Magento\Catalog\Model\Product|false
     */
    protected function _initProduct()
    {
        $productId = (int)$this->getRequest()->getParam('product');
        if ($productId) {
            $storeId = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getId();
            try {
                return $this->productRepository->getById($productId, false, $storeId);
            } catch (NoSuchEntityException $e) {
                return false;
            }
        }
        return false;
    }

    /**
     * Add product to shopping cart action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        $result = [];
        if (!$this->_formKeyValidator->validate($this->getRequest())) {
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }
        
        $params = $this->getRequest()->getParams();
        try {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            if (isset($params['qty'])) {
                $filter = new \Zend_Filter_LocalizedToNormalized(
                    ['locale' => $this->_objectManager->get('Magento\Framework\Locale\ResolverInterface')->getLocale()]
                );
                $params['qty'] = $filter->filter($params['qty']);
            }

            $product = $this->_initProduct();
            $productIdConfigSelected = null;
            /**
             * Check product availability
             */
            if (!$product) {
                return $this->goBack();
            }

            $params['qty'] = isset($params['qty']) ? (int)$params['qty'] : 1;

            // --- Qty product main
            // $qtyProductMain = $this->getRequest()->getParam('input-qty') ? (int) $this->getRequest()->getParam('input-qty'): 1;    
            $qtyProductMain = $params['qty'];
            // --- Qty products gift
            $qtyProductsGiftParam = $this->getRequest()->getParam('qty-gift');     
            $qtyProductsGift = array();
            if ($qtyProductsGiftParam) {
                $qtyProductsGift = explode(",",$qtyProductsGiftParam);
            }             

            // Check stock - main_product
            if ($product->getTypeId() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
                $configurableProductModel = $objectManager->create('Magento\ConfigurableProduct\Model\Product\Type\Configurable');
                $configurableSelectedAttributes = $this->getRequest()->getParam('super_attribute');
                $simpleProductSelected = $configurableProductModel->getProductByAttributes($configurableSelectedAttributes, $product);
                $simpleProductSelectedId = $simpleProductSelected->getId();
                $productIdConfigSelected = $simpleProductSelectedId;
                $simpleProductObj = $objectManager->create('Magento\Catalog\Model\Product')->load($simpleProductSelectedId);
                //$this->writeLog('SimpleProductSelected: '.$simpleProductObj->getName());
                $defaultStockQty = $this->checkDefaultStock($simpleProductObj, $qtyProductMain);
            } else {
                $defaultStockQty = $this->checkDefaultStock($product, $qtyProductMain);
            }
            if (!empty($defaultStockQty)) {
                $this->writeLog('DefaultStockQtyMain: ');
                $this->writeLog($defaultStockQty);
                return $this->_jsonResponse($defaultStockQty);
            }

            // Parse params
            // -- Product Name short
            $productNameShort = $product->getNameShort();
            // -- Related products
            $relatedProductIdsParam = $this->getRequest()->getParam('related_product');
            $relatedProductIds = array();
            if(!empty($relatedProductIdsParam)){
                $relatedProductIds = explode(',', $relatedProductIdsParam);
            }
            // -- SKUs selected gift
            $skusGiftParam = $this->getRequest()->getParam('sku-gift');            
            $skusGift = array();
            if ($skusGiftParam) {
                $skusGift = explode(",",$skusGiftParam);
            }
            // --- Rule Ids
            $rulesIdGiftParam = $this->getRequest()->getParam('rulesid-gift');
            $cartPromoRuleIds = array();
            if ($rulesIdGiftParam) {
                $cartPromoRuleIds = explode(",",$rulesIdGiftParam);
            }            

            // Prepare Helper
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            // $quoteItemCollectionMainProductFactory = $objectManager->create('Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory');
            // $blockRules = $objectManager->create('Chottvn\Frontend\Block\Rules');
            $quoteFactory = $objectManager->create('Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory');
            
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
                        $ruleId = $cartPromoRuleIds[$idx];
                        $defaultStockQty = $this->checkDefaultStockPromo($productGiftObj, $qtyGift, $ruleId);
                        if (!empty($defaultStockQty)) {
                            $this->writeLog('DefaultStockQtyGift: ');
                            $this->writeLog($defaultStockQty);
                            return $this->_jsonResponse($defaultStockQty);
                        } else {
                            $productId = $productGiftObj->getId();
                            array_push($promoProductIds, $productId);
                        }
                       
                    }                    
                }
            }            
            $productIdsAll = array_merge([$product->getId()], $promoProductIds);

            // Prepare promo tracking fields
            $cartPromoItemIdsTuple = ''; // Product Ids String

            if (count($promoProductIds) > 0) {
                $cartPromoItemIdsTuple = implode(',', $promoProductIds);
            }
            $this->writeLog('Quote: '.$quoteId.' >> cartPromoItemIds: '.$cartPromoItemIdsTuple);
            if(empty($cartPromoItemIdsTuple)){ // If empty gift
                // Add main product
                $this->cart->addProduct($product, $params);
                // -- Add related products
                if (!empty($relatedProductIds)) {
                    $this->cart->addProductsByIds($relatedProductIds);
                }
                $this->cart->save();

                // Update simple product, related to configurable product
                // if (!empty($productIdConfigSelected)) {
                //     $quoteItemsSimple = $quoteFactory->create()->addFieldToFilter('product_id', $productIdConfigSelected)
                //                                                 ->addFieldToFilter('product_type', array('eq' => 'simple'))
                //                                                 ->addFieldToFilter('parent_item_id', array('notnull' => true));

                //     if (count($quoteItemsSimple->getData()) > 0) {
                //         foreach ($quoteItemsSimple as $item) {
                //             if (!empty($item->getParentItemId())) {
                //                 $productSimple = $quote->getItemById($item->getId());
                //                 if (!$productSimple) {
                //                     continue;
                //                 }
                //                 $productSimple->setQty((int)$qtyProductMain);
                //                 $productSimple->save();
                //             }
                //         }
                //     }
                // }
            }else{ // Has gift
                // Proceed to add items to carts
                if ($this->checkProductMainExists($quoteId, $product->getId(), $cartPromoItemIdsTuple, $productIdConfigSelected) )
                { // Tuple (product-gifts) exists >> Update quantity only    

                    $quoteItemIdMainProduct = $this->getQuoteItemIdMainProduct($quoteId, $quoteItems, $product->getId(), $productIdConfigSelected, $cartPromoItemIdsTuple);
                    $this->writeLog("QuoteIdMain: ".$quoteItemIdMainProduct);
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
                }else{ // Tuple (product-gifts) not exists >> Add new
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
                    if (!empty($relatedProductIds)) {
                        $this->cart->addProductsByIds($relatedProductIds);
                    }
                    $this->cart->save();                  
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


            /**
             * @todo remove wishlist observer \Magento\Wishlist\Observer\AddToCart
             */
            $this->_eventManager->dispatch(
                'checkout_cart_add_product_complete',
                ['product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse()]
            );           
            if (!$this->_checkoutSession->getNoCartRedirect(true)) {
                if (!$this->cart->getQuote()->getHasError()) {
                    $message = __(
                        'You added %1 to your shopping cart.',
                        $productNameShort
                    );
                    $this->messageManager->addSuccessMessage($message);
                    $result['success'] = true;
                    $result['messages'] =  $message;
                    if (isset($params['isCheckoutPage'])){
                        $_layout  = $this->_objectManager->get('Magento\Framework\View\LayoutInterface');
                        $_layout->getUpdate()->load([ 'cartquickpro_checkout_cart_index', 'checkout_cart_item_renderers','checkout_item_price_renderers']);
                        $_layout->generateXml();
                        $_output = $_layout->getOutput();
                        $result['content'] = $_output;
                        $result['isPageCheckoutContent'] =  true;
                    }
                }
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->writeLog($e);
            $productId = $product->getId();
            
            // Get salable stock for product     
            if ($product->getTypeId() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {                     
                $productSku = $simpleProductObj->getSku(); 
            }else{
                $productSku = $product->getSku();
            }

            $salableStockQty = $this-> getSaleableQtyBySku($productSku);

            // added product to cart
            $items = $this->cartHelper->getQuote()->getItems();

            $qtyProductMainCurrentInCart = 0;
            if($items){
                foreach ($items as $item){
                    if($productId == $item->getProductId()){
                        $qtyProductMainCurrentInCart += $item->getQty();
                    }
                }
            }
            
            // "The requested qty is not available","Số lượng yêu cầu không có sẵn"
            switch ($e->getMessage()) {
                case 'The requested qty is not available':
                case 'Số lượng yêu cầu không có sẵn':
                case 'Sản phẩm bạn vừa thêm không có sẵn.':
                    $actual_number_in_cart = $qtyProductMainCurrentInCart - $qtyProductMain;
                    $actual_number_in_cart = $actual_number_in_cart > 0 ? $actual_number_in_cart : 0;

                    if($actual_number_in_cart == 0 && $salableStockQty == 0){
                        $result['messages'] = $e->getMessage();
                        $result['error_code'] = 'contact-us';
                        $result['success'] = false;
                        $result['html_input'] = '<a href="'.$product->getProductUrl(false).'\'#stockalert\'" title="'.$product->getName().'"><button title="'.__('Contact when stock is available').'" class="stock unavailable btn-action btn-cart"><span>'.__('Contact when stock is available').'</span></button></a>';
                    }else{
                        $result['messages'] = $e->getMessage();
                        $result['error_code'] = 'out-of-stock';
                        $result['success'] = false;
                        $result['html_input'] = __('Not enough required amount.');
                    }

                    $result['actual_number_in_cart'] = $actual_number_in_cart;
                    $result['salable_stock_qty'] = $salableStockQty;
                    $result['action_page'] = 'add_to_cart';
                    $result['product_id'] = $productId;
                    break;
                
                default:
                    if ($this->_checkoutSession->getUseNotice(true)) {
                        $this->messageManager->addNotice(
                            $this->_objectManager->get('Magento\Framework\Escaper')->escapeHtml($e->getMessage())
                        );
                        $result['messages'] =  $this->_objectManager->get('Magento\Framework\Escaper')->escapeHtml($e->getMessage());
                        $result['success'] = true;
                    } else {
                        $messages = array_unique(explode("\n", $e->getMessage()));
                        foreach ($messages as $message) {
                            $this->messageManager->addError(
                                $this->_objectManager->get('Magento\Framework\Escaper')->escapeHtml($message)
                            );
                        }
                        
                        $result['messages'] = join(', ', $messages);
                        $result['success'] = false;
                    }
                    break;

                    $storeManager = $this->_objectManager->get('\Magento\Store\Model\StoreManagerInterface');
                    $baseUrl= $storeManager->getStore()->getBaseUrl();
                    $url = $baseUrl.'cartquickpro/catalog_product/options/id/'.$params['product'];
                    if (!$url) {
                        $cartUrl = $this->_objectManager->get('Magento\Checkout\Helper\Cart')->getCartUrl();
                        $url = $this->_redirect->getRedirectUrl($cartUrl);
                    }
                    
                    $result['url']  =  $url;
            }
            // print_r(get_class_methods($e));exit;

        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('We can\'t add this item to your shopping cart right now.'));
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
            $result['messages'] =  __('We can\'t add this item to your shopping cart right now.');
            $result['success'] = false;
        }
        $result['isAddToCartBtn'] = (!isset($params['isCheckoutPage']) && $this->cart->getItemsCount()) ? true : false ;
        
        return $this->_jsonResponse($result);
    }

    public function has_dupes($array) {
        return count($array) !== count(array_unique($array));
    }

    protected function _jsonResponse($result)
    {
        return $this->getResponse()->representJson(
            $this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode($result)
        );
    }

    /**
     * Resolve response
     *
     * @param string $backUrl
     * @param \Magento\Catalog\Model\Product $product
     * @return $this|\Magento\Framework\Controller\Result\Redirect
     */
    protected function goBack($backUrl = null, $product = null)
    {
        if (!$this->getRequest()->isAjax()) {
            return parent::_goBack($backUrl);
        }

        $result = [];

        if ($backUrl || $backUrl = $this->getBackUrl()) {
            $result['backUrl'] = $backUrl;
        } else {
            if ($product && !$product->getIsSalable()) {
                $result['product'] = [
                    'statusText' => __('Out of stock')
                ];
            }
        }

        $this->getResponse()->representJson(
            $this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode($result)
        );
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
        //$this->writeLog('getDefaultStockCustom: '.$defaultStockCustom);
        $sumQtyCurrentInQuoteItem = $product->sumQtyCurrentInQuoteItem(); // sum qty cua product hien tai trong table quote_item (real time)
        //$this->writeLog('sumQtyCurrentInQuoteItem: '.$sumQtyCurrentInQuoteItem);
        $defaultStockQty = $defaultStockCustom - $sumQtyCurrentInQuoteItem;

        if ($defaultStockCustom == 0 || $defaultStockQty == 0) {
            // $result['messages'] =  __('Out of stock');
            $result['messages'] = 'Chỉ còn ' . $defaultStockCustom . ' sản phẩm';
            $result['error_code'] = 'out-of-stock';
            $result['success'] = false;

            return $result;
            // return $this->_jsonResponse($result);
        }
        $defaultStock = $defaultStockCustom - ($sumQtyCurrentInQuoteItem + $requestQty);
        if ($defaultStock < 0) {

            $result['messages'] = 'Chỉ còn ' . $defaultStockCustom . ' sản phẩm';
            $result['error_code'] = 'contact-us';
            $result['success'] = false;

            return $result;
            // return $this->_jsonResponse($result);
        }
        return $result;
    }

    public function checkDefaultStockPromo($product, $requestQty, $ruleId)
    {
        $result = array();
        $defaultStockCustom = $product->getDefaultStockCustom(); // so luong ton hien tai (real time)
        $sumQtyCurrentInQuoteItem = $product->sumQtyCurrentInQuoteItem(); // sum qty cua product hien tai trong table quote_item (real time)
        $defaultStockQty = $defaultStockCustom - $sumQtyCurrentInQuoteItem;

        if ($defaultStockCustom == 0 || $defaultStockQty == 0) {
            $result['messages'] =  'Sản phẩm quà tặng '.$product->getNameShort(). ' chỉ còn ' . $defaultStockCustom . ' sản phẩm';
            // $result['messages'] =  'Sản phẩm quà tặng '.$product->getNameShort(). ' tạm hết hàng.';
            $result['error_code'] = 'out-of-stock-promo';
            $result['rule_id'] = $ruleId;
            $result['product_id'] = $product->getId();
            $result['success'] = false;

            return $result;
        }
        $defaultStock = $defaultStockCustom - ($sumQtyCurrentInQuoteItem + $requestQty);
        if ($defaultStock < 0) {
            // $result['messages'] =  'Sản phẩm quà tặng '.$product->getNameShort(). ' chỉ còn ' . $defaultStockQty . ' sản phẩm';
            $result['messages'] =  'Sản phẩm quà tặng '.$product->getNameShort(). ' chỉ còn ' . $defaultStockCustom . ' sản phẩm';
            $result['error_code'] = 'contact-us-promo';
            $result['rule_id'] = $ruleId;
            $result['product_id'] = $product->getId();
            $result['success'] = false;

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

    /*
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
    }*/

    /*
    private function isQuoteItemCurrentMainProduct($quoteItem, $productId, $productIdConfigSelected, $cartPromoItemIdsTuple ) {        
        if (empty($productIdConfigSelected)){ // Simple Product
            return ($quoteItem->getCartPromoItemIds() == $cartPromoItemIdsTuple)
                && ($quoteItem->getProductId() == $productId );
        }else{ // Configurable Product

        }
    }*/

    /*
    private function isQuoteItemCurrentGiftProduct($quoteItem, $productId, $productIdConfigSelected, $cartPromoItemIdsTuple ) {
        if (empty($productIdConfigSelected)){ // Simple Product
            return ($quoteItem->getCartPromoItemIds() == $cartPromoItemIdsTuple)
                && $quoteItem->getProductId() != $product->getId();
        }else{ // Configurable Product
            
        }        
    } */

    /*private function checkProductGiftExists($quoteId, $productIdParent, $cartPromoItemIdsTuple){
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $connection = $objectManager->get('\Magento\Framework\App\ResourceConnection');
        $conn = $connection->getConnection();
        $isExisted = false;
        // Check main product
        $sqlQuery = "
            SELECT *
            FROM quote_item
            WHERE quote_id = $quoteId
              AND cart_promo_parent_id = $productIdParent
              AND cart_promo_item_ids = $cartPromoItemIdsTuple
              AND cart_promo_options = 'ampromo_items'
        ";
        $binds = [];
        $data  = $conn->fetchRow($sqlQuery, $binds);          
        $isExisted = empty($data) ? false : true;
        return $isExisted;
    }*/

    /**
    * @param $info
    * @param $type  [error, warning, info]
    * @return 
    */
    private function writeLog($info, $type = "info") {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/sm_cqp_cart_add.log');
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
