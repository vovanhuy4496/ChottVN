<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Chottvn\Sales\Rewrite\Magento\Checkout\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Checkout\Model\Cart\CartInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Shopping cart model
 *
 * @api
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @deprecated 100.1.0 Use \Magento\Quote\Model\Quote instead
 * @see \Magento\Quote\Api\Data\CartInterface
 * @since 100.0.2
 */
class Cart extends \Magento\Checkout\Model\Cart
{
    /**
     * @var \Magento\Checkout\Model\Cart\RequestInfoFilterInterface
     */
    private $requestInfoFilter;

    
    /**
     * Getter for RequestInfoFilter
     *
     * @deprecated 100.1.2
     * @return \Magento\Checkout\Model\Cart\RequestInfoFilterInterface
     */
    private function getRequestInfoFilter()
    {
        if ($this->requestInfoFilter === null) {
            $this->requestInfoFilter = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Checkout\Model\Cart\RequestInfoFilterInterface::class);
        }
        return $this->requestInfoFilter;
    }

    /**
     * Get request for product add to cart procedure
     *
     * @param   \Magento\Framework\DataObject|int|array $requestInfo
     * @return  \Magento\Framework\DataObject
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getProductRequest($requestInfo)
    {
        if ($requestInfo instanceof \Magento\Framework\DataObject) {
            $request = $requestInfo;
        } elseif (is_numeric($requestInfo)) {
            $request = new \Magento\Framework\DataObject(['qty' => $requestInfo]);
        } elseif (is_array($requestInfo)) {
            $request = new \Magento\Framework\DataObject($requestInfo);
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('We found an invalid request for adding product to quote.')
            );
        }
        $this->getRequestInfoFilter()->filter($request);

        return $request;
    }

    /**
     * Get request quantity
     *
     * @param Product $product
     * @param \Magento\Framework\DataObject|int|array $request
     * @return int|DataObject
     * ------
     * ISSUES:
     * - addProductNew + configurable_product >>> return null
     */
    private function getQtyRequest($product, $request = 0)
    {
        $request = $this->_getProductRequest($request);
        $stockItem = $this->stockRegistry->getStockItem($product->getId(), $product->getStore()->getWebsiteId());
        $minimumQty = $stockItem->getMinSaleQty();
        //If product quantity is not specified in request and there is set minimal qty for it
        if ($minimumQty
            && $minimumQty > 0
            && !$request->getQty()
        ) {
            $request->setQty($minimumQty);
        }
        $this->writeLog("getQtyRequest: ");
        $this->writeLog(json_encode($request));
        return $request;
    }
    
    /**
     * Add product to shopping cart (quote)
     *
     * @param int|Product $productInfo
     * @param \Magento\Framework\DataObject|int|array $requestInfo
     * @param {Array} $giftInfo
     *  {
            qty_product_main: 
            cart_promo_option:
            cart_promo_item_ids:
            items: [
                {
                    product_id:
                    cart_promo_ids: //Sale rule Ids
                    cart_promo_qty:
                },...
            ]
        }
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function addProductWithGift($productInfo, $requestInfo = null, $giftInfo)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $cartHelper = $objectManager->get('Magento\Checkout\Helper\Cart');
        $product = $this->_getProduct($productInfo);
        $productId = $product->getId();        
        if ($productId) {
            $request = $this->getQtyRequest($product, $requestInfo);
            try {
                $this->_eventManager->dispatch(
                    'checkout_cart_product_add_before',
                    ['info' => $requestInfo, 'product' => $product]
                );
                $result = $this->getQuote()->addProductNew($product, $request);
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->_checkoutSession->setUseNotice(false);
                $result = $e->getMessage();
            }
            // $this->writeLog('added product main: '.$product->getName());
            /**
             * String we can get if prepare process has error
             */
            if (is_string($result)) {
                if ($product->hasOptionsValidationFail()) {
                    $redirectUrl = $product->getUrlModel()->getUrl(
                        $product,
                        ['_query' => ['startcustomization' => 1]]
                    );
                } else {
                    $redirectUrl = $product->getProductUrl();
                }
                $this->_checkoutSession->setRedirectUrl($redirectUrl);
                if ($this->_checkoutSession->getUseNotice() === null) {
                    $this->_checkoutSession->setUseNotice(true);
                }
                throw new \Magento\Framework\Exception\LocalizedException(__($result));
            }
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(__('The product does not exist.'));
        }
        // Add gift info
        try{            
            $giftItems = $giftInfo["items"];
            if(sizeof($giftItems) > 0){
                
                $cartPromoItemIds = $giftInfo['cart_promo_item_ids']; 
                // Set ItemIdsTuple for main product                
                $result->setCartPromoItemIds($cartPromoItemIds);

                if ($cartHelper->getItemsCount() !== 0) {
                    $result->save(); // Save to get ID
                }
                // $this->writeLog('Updated cartPromoItemIds product main');

                // Prepare info
                $qtyProductMain = $giftInfo['qty_product_main'];   
                $cartPromoParentItemId = $result->getId();
                $cartPromoOption = $giftInfo['cart_promo_option'];
                               
                // Add gift items
                foreach ($giftItems as $item) {
                    $productIdGift = $item["product_id"];
                    $cartPromoQty = $item["cart_promo_qty"];
                    $itemQty = $qtyProductMain * (float)$cartPromoQty;
                    $cartPromoIds = $item["cart_promo_ids"]; //
                    $productGift = $this->_getProduct($productIdGift);
                    // Set price
                    $productGift->setPrice(0);
                    $quoteItemGift = $this->getQuote()->addProductNew($productGift);
                    if ($cartHelper->getItemsCount() === 0) {
                        $this->save();
                        $cartPromoParentItemId = $result->getId();
                    }
                    $info_buyRequest = '{"qty":'.$cartPromoQty.',"options":{"ampromo_rule_id":"'.$cartPromoIds.'","discount":{"discount_item":null,"minimal_price":"0"},"minimal_price":"0"}}';
                    //$quoteItemGift->setOptions($options);
                    $quoteItemGift->setQty($itemQty);
                    $quoteItemGift->setCartPromoQty($cartPromoQty);
                    $quoteItemGift->setCartPromoOption($cartPromoOption);
                    $quoteItemGift->setCartPromoParentId($productId);
                    $quoteItemGift->setCartPromoItemIds($cartPromoItemIds);
                    $quoteItemGift->setCartPromoIds($cartPromoIds);
                    $quoteItemGift->setCartPromoParentItemId($cartPromoParentItemId);
                    
                    $options = $quoteItemGift->getOptions();
                    foreach ($options as $option) {
                        $option->setProductId($productIdGift);
                        $option->setValue($info_buyRequest);
                    }                    
                        // $quoteItemGift->setPrice(0);
                        // $quoteItemGift->setBasePrice(0);
                        // $quoteItemGift->setPriceInclTax(0);
                        // $quoteItemGift->setBasePriceInclTax(0);
                    $quoteItemGift->save();
                    // $this->writeLog('added product promo: '.$productGift->getName());
                }
            }
            
        }catch(\Exception $e){
            $this->writeLog($e);
        }

        $this->_eventManager->dispatch(
            'checkout_cart_product_add_after',
            ['quote_item' => $result, 'product' => $product]
        );
        $this->_checkoutSession->setLastAddedProductId($productId);
        return $this;
    }

    /**
     * Add product to shopping cart (quote)
     *
     * @param int|Product $productInfo
     * @param \Magento\Framework\DataObject|int|array $requestInfo
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function addProductNew($productInfo, $requestInfo = null)
    {
        try {
            $product = $this->_getProduct($productInfo);
            $productId = $product->getId();
            // $this->writeLog('override cart productId: '.$productId);
            // $this->writeLog((array)$requestInfo);
    
            if ($productId) {
                $request = $this->getQtyRequest($product, $requestInfo);
                try {
                    $this->_eventManager->dispatch(
                        'checkout_cart_product_add_before',
                        ['info' => $requestInfo, 'product' => $product]
                    );
                    $result = $this->getQuote()->addProductNew($product, $request);
                } catch (\Magento\Framework\Exception\LocalizedException $e) {
                    $this->_checkoutSession->setUseNotice(false);
                    $result = $e->getMessage();
                }
                /**
                 * String we can get if prepare process has error
                 */
                if (is_string($result)) {
                    if ($product->hasOptionsValidationFail()) {
                        $redirectUrl = $product->getUrlModel()->getUrl(
                            $product,
                            ['_query' => ['startcustomization' => 1]]
                        );
                    } else {
                        $redirectUrl = $product->getProductUrl();
                    }
                    $this->_checkoutSession->setRedirectUrl($redirectUrl);
                    if ($this->_checkoutSession->getUseNotice() === null) {
                        $this->_checkoutSession->setUseNotice(true);
                    }
                    throw new \Magento\Framework\Exception\LocalizedException(__($result));
                }
            } else {
                throw new \Magento\Framework\Exception\LocalizedException(__('The product does not exist.'));
            }
    
            $this->_eventManager->dispatch(
                'checkout_cart_product_add_after',
                ['quote_item' => $result, 'product' => $product]
            );
            $this->_checkoutSession->setLastAddedProductId($productId);
            return $this;
        } catch (\Exception $e) {
            $this->writeLog($e);
        }
    }

    /**
     * Adding products to cart by ids
     *
     * @param  int[] $productIds
     * @return $this
     */
    public function addProductsNewByIds($productIds)
    {
        $allAvailable = true;
        $allAdded = true;

        if (!empty($productIds)) {
            foreach ($productIds as $productId) {
                $productId = (int)$productId;
                if (!$productId) {
                    continue;
                }
                $product = $this->_getProduct($productId);
                if ($product->getId() && $product->isVisibleInCatalog()) {
                    $request = $this->getQtyRequest($product);
                    try {
                        $this->getQuote()->addProductNew($product, $request);
                    } catch (\Exception $e) {
                        $allAdded = false;
                    }
                } else {
                    $allAvailable = false;
                }
            }

            if (!$allAvailable) {
                $this->messageManager->addErrorMessage(__("We don't have some of the products you want."));
            }
            if (!$allAdded) {
                $this->messageManager->addErrorMessage(__("We don't have as many of some products as you want."));
            }
        }
        return $this;
    }


    /**
    * @param $info
	* @param $type  [error, warning, info]
	* @return 
	*/
	private function writeLog($info, $type = "info") {
		$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/chottvn_sales_checkout_cart.log');
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
