<?php
/**
 * Copyright © (c) chotructuyen.vn All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Chottvn\PriceQuote\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{

    protected $requestItemsFactory;
    protected $requestFactory;
    /**
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Chottvn\PriceQuote\Model\ResourceModel\Items\CollectionFactory $requestItemsFactory,
        \Chottvn\PriceQuote\Model\ResourceModel\Request\CollectionFactory $requestFactory
    ) {
        $this->requestItemsFactory = $requestItemsFactory;
        $this->requestFactory = $requestFactory;
        parent::__construct($context);
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return true;
    }
    public function getCollection(){
        $collection = $this->requestFactory->create();
        $collection->getSelect()->join(
                ['items'=> $collection->getTable('chottvn_pricequote_request_item')],
            'main_table.request_id = items.request_id'
        )->order('main_table.created_at DESC');
        return $collection;
    }
    public function getCollectionRequest($request){
        $collection = $this->requestFactory->create();
        $collection->addFieldToFilter('main_table.request_id', ['eq' => $request]);
        $lastItem = $collection->getLastItem();
        return $lastItem;
    }
    public function getProductUrl($product)
    {
        return $product->getUrlModel()->getUrl($product);
    }
    public function hasProductUrl($product)
    {
        if ($product->isVisibleInSiteVisibility()) {
            return true;
        } else {
            if ($product->hasUrlDataObject()) {
                $data = $product->getUrlDataObject();
                if (in_array($data->getVisibility(), $product->getVisibleInSiteVisibilities())) {
                    return true;
                }
            }
        }

        return false;
    }
    public function getCustomShippingAmount($requestquote)
    {
        $request = $requestquote->getData('request_id');
        $getShippingAmount = $requestquote->getData('shipping_amount');
        $checkFlagShipping = $requestquote->getData('flag_shipping');
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $checkoutHelper = $objectManager->get('Chottvn\PriceDecimal\Helper\Data');
        if ($checkFlagShipping == 'freeshipping') {
            return __('Free Shipping');
        }
        if ($this->isOverWeight($request) == "over") {
            return __('Price Contact');
        }
        // chua chon address ben checkout
        if (empty($this->getCollectionRequest($request)['region_id'])) {
            return __('Not included');
        }

        if ($getShippingAmount > 0) {
            return $checkoutHelper->formatPrice($getShippingAmount);
        }
        if ($checkFlagShipping == 'accept' && $getShippingAmount == 0) {
            return __('Not included');
        }
        return $checkoutHelper->formatPrice($getShippingAmount);
    }
    public function isOverWeight($request)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $handlingOverWeightFee = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('carriers/tablerate/handling_over_weight_fee');
        $totalWeight = $this->checkWeight($request);
        $items = $this->getCollectionItems($request);
        $flag = false;
        if($items){
            foreach ($items as $item) {
                $productType = $item->getData('product_type');
                $itemQuote = $objectManager->get('Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory');
                $itemQuoteCollection = $itemQuote->create()->addFieldToFilter('item_id',$item->getData('item_id'))->addFieldToFilter('product_type','simple');
                $lastItemQuote = $itemQuoteCollection->getLastItem();
                $parentItemId = $lastItemQuote->getData('parent_item_id');
                if( ($productType == 'simple' || $productType == 'configurable') && !$parentItemId){
                    $weight = (float) $item->getData('weight');
                    if($weight == 0){
                        $flag = true;
                        break;
                    }
                }
            }
        }
        
        if ($totalWeight > $handlingOverWeightFee || $flag == true) {
            return "over";
        }

        return "accept";
    }

    public function getCollectionItems($request){
        $collection = $this->requestItemsFactory->create();
        $collection->addFieldToFilter('main_table.request_id', ['eq' => $request])
        ->addFieldToFilter(
        ['main_table.cart_promo_option', 'main_table.cart_promo_option','main_table.cart_promo_option'],
        [
            ['null' => true],
            ['eq' => 'ampromo_cart'],
            ['eq' => 'ampromo_spent']
        ]);
        return $collection;
    }
    
    public function checkWeight($request)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $ruleResource = $objectManager->get('Magento\CatalogRule\Model\ResourceModel\Rule');
        $totalWeight = 0;
        $items = $this->getCollectionItems($request);
        $rule = false;

        foreach ($items as $item) {
            
            if($item->getData('applied_rule_ids')){
                $arrayAppliedRuleIds = explode(',', $item->getData('applied_rule_ids'));
                $rule = $this->checkProductHaveFreeShip($arrayAppliedRuleIds);
            }
            
            if ($item->getData('qty') > 0 && !$rule) {
                $totalWeight = $totalWeight + ($item->getData('weight') * $item->getData('qty'));
            }
        }
       
        return $totalWeight;
    }
    public function checkProductHaveFreeShip($salesruleIds)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $productHaveFreeShip = 0;
        foreach($salesruleIds as $salesruleId) {
            if ($salesruleId) {
                $salesRule = $objectManager->get('\Magento\SalesRule\Model\Rule');
                $rule = $salesRule->load($salesruleId);
                if ($rule && $rule->getIsActive()) {
                    $productHaveFreeShip = $rule->getSimpleFreeShipping();
                }
            }
        }
        
        if ($productHaveFreeShip == 1) {
            return true;
        }
        return false;
    }
    public function getCustomTitleGrandTotal($requestQuote)
    {
        $shippingAmount = $requestQuote->getData('shipping_amount');
        $checkFlagShipping = $requestQuote->getData('flag_shipping');
        $requestId = $requestQuote->getData('request_id');
        $title = __('Grand Total');
        if ($shippingAmount > 0) {
            return $title;
        }

        if (($this->isOverWeight($requestId) == "over" || $shippingAmount == 0) && $checkFlagShipping != 'freeshipping') {
            $title = __('Grand Total Temp');
        }

        return $title;
    }
     /**
     * get Options attributes of Quote Item
     * @param $itemquoteID
     */
    public function getOptionsAttributes($itemquoteID)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $itemQuote = $objectManager->get('Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory');
        $itemQuoteCollection = $itemQuote->create()->addFieldToFilter('item_id',$itemquoteID)->addFieldToFilter('product_type','configurable');
        $lastItemQuote = $itemQuoteCollection->getLastItem();
        $lastItemQuote = $lastItemQuote->getData('item_id');
        $optionsQuote = $objectManager->get('Magento\Quote\Model\ResourceModel\Quote\Item\Option\CollectionFactory');
        $optionQuoteCollection = $optionsQuote->create()->addFieldToFilter('item_id',$lastItemQuote)->addFieldToFilter('code','attributes');
        $lastOptionQuote = $optionQuoteCollection->getLastItem();
        $attributesOption = array();
        $value = $lastOptionQuote->getData('value');
        if($value){
            $attributesOption = json_decode($value);
        }
        return $attributesOption;
    }
    /**
     * check Default Stock by product Id
     * @param $productId
     */
    public function checkDefaultStock($productId)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $productConfigurable = $objectManager->create('Magento\Catalog\Model\Product')->load($productId);
        $checkStatusQtyProduct = $objectManager->get('Chottvn\Sales\Rewrite\Magento\Catalog\Block\Product\View');
        $defaultStock = 0;
        $defaultStock = $checkStatusQtyProduct->checkStatusQtyProduct($productConfigurable);
        return $defaultStock;
    }
     /**
     * get Rules Name of Quote
     * @param $productId
     */
    public function getRulesNameWithQuoteCTT($getQuoteItemId, $quoteId, $ruleIdsApplied) {
        try {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $quoteItemFactory = $objectManager->create('Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory');
            $productRepository = $objectManager->create('Magento\Catalog\Api\ProductRepositoryInterface');
            // $messagesPrefix = $objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('ampromo/messages/prefix');
            $saleRuleFactory = $objectManager->create('Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory');
            $resource = $objectManager->create('Magento\Framework\App\ResourceConnection');
            $rulecart = array();
            if($ruleIdsApplied){
                $ruleIdsAppliedArray = explode(',', $ruleIdsApplied);
                $amasty_ampromo_rule = $resource->getTableName('amasty_ampromo_rule'); 
                $rules = $saleRuleFactory->create()->addFieldToFilter('rule_id', array('in' => $ruleIdsAppliedArray))->setOrder('main_table.from_date','DESC')->setOrder('main_table.name','DESC');
                $rules->getSelect()->joinLeft(array(
                    'ampromo' => $amasty_ampromo_rule),
                    'main_table.rule_id = ampromo.salesrule_id'
                    )
                    ->where('main_table.simple_action = ?','ampromo_items')
                    ->where('ampromo.sku IS NOT NULL');
                if ($rules) {
                    foreach ($rules as $keyrule => $rule){
                        $skus = $rule->getData('sku');
                        $discountAmount = $rule->getData('discount_amount') ? (int)$rule->getData('discount_amount') : 1;
                        $arrskus = array();
                        $arrskus = explode( ',', $skus);
                        $type = $rule->getType();
                        if(!empty($type) && $type == 1){
                            $items = $quoteItemFactory->create()->addFieldToFilter('quote_id', $quoteId)
                            ->addFieldToFilter('cart_promo_parent_item_id', $getQuoteItemId)
                            ->addFieldToFilter('cart_promo_ids', $keyrule);
                            $fisrtItem = $items->getFirstItem();
                            // get sp qtang duoc chon (check box)
                            if (!empty($fisrtItem) && count($fisrtItem->getData()) > 0) {
                                $sku = $fisrtItem->getData('sku');
                                $productPromo = $productRepository->get($sku);
                                $defaultStockPromo = $productPromo->getDefaultStockCustom(); // so luong ton hien tai (real time)
                                $sumQtyCurrentInQuoteItem = $productPromo->sumQtyCurrentInQuoteItem(); // sum qty cua product hien tai trong table quote_item (real time)
                                $product_unit = $productPromo->getData('product_unit') ? $productPromo->getData('product_unit'): '';
                                $rulecart[$keyrule]['sku'][] = $sku;
                                $rulecart[$keyrule]['default-stock'][] = $defaultStockPromo;
                                $rulecart[$keyrule]['sum-default-stock'][] = $sumQtyCurrentInQuoteItem;
                                $rulecart[$keyrule]['unit_product'][] = $product_unit;
                                $rulecart[$keyrule]['qty'][] = $discountAmount;
                            }
                        }else{
                            foreach($arrskus as $item){
                                $productPromo = $productRepository->get($item);
                                $defaultStockPromo = $productPromo->getDefaultStockCustom(); // so luong ton hien tai (real time)
                                $sumQtyCurrentInQuoteItem = $productPromo->sumQtyCurrentInQuoteItem(); // sum qty cua product hien tai trong table quote_item (real time)
                                $product_unit = $productPromo->getData('product_unit') ? $productPromo->getData('product_unit'): '';
                                $rulecart[$keyrule]['sku'][] = $item;
                                $rulecart[$keyrule]['default-stock'][] = $defaultStockPromo;
                                $rulecart[$keyrule]['sum-default-stock'][] = $sumQtyCurrentInQuoteItem;
                                $rulecart[$keyrule]['unit_product'][] = $product_unit;
                                $rulecart[$keyrule]['qty'][] = $discountAmount;
                            }
                        }
                    }
                }
            }
            return $rulecart;
        } catch(\Exception $e) {
            $this->writeLog("Exception:");
            $this->writeLog($e);
        }
    }
     /**
     * get Rules Name of Price Quote 
     * @param $productId
     */
    public function getRulesNameWithPriceQuoteCTT($itemQuoteId, $requestId, $ruleIdsApplied) {
        try {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $quoteItemFactory = $objectManager->create('Chottvn\PriceQuote\Model\ResourceModel\Items\CollectionFactory');
            $productRepository = $objectManager->create('Magento\Catalog\Api\ProductRepositoryInterface');
            // $messagesPrefix = $objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('ampromo/messages/prefix');
            $saleRuleFactory = $objectManager->create('Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory');
            $resource = $objectManager->create('Magento\Framework\App\ResourceConnection');
            $rulecart = array();
            if($ruleIdsApplied){
                $ruleIdsAppliedArray = explode(',', $ruleIdsApplied);
                $amasty_ampromo_rule = $resource->getTableName('amasty_ampromo_rule'); 
                $rules = $saleRuleFactory->create()->addFieldToFilter('rule_id', array('in' => $ruleIdsAppliedArray));
                $rules->getSelect()->joinLeft(array(
                    'ampromo' => $amasty_ampromo_rule),
                    'main_table.rule_id = ampromo.salesrule_id'
                    )
                    ->where('main_table.simple_action = ?','ampromo_items')
                    ->where('ampromo.sku IS NOT NULL')->order(array('main_table.from_date DESC','main_table.name DESC'));
                    
                if ($rules) {
                    foreach ($rules as $keyrule => $rule){
                        $skus = $rule->getData('sku');
                        $discountAmount = $rule->getData('discount_amount') ? (int)$rule->getData('discount_amount') : 1;
                        $arrskus = array();
                        $arrskus = explode( ',', $skus);
                        $type = $rule->getType();
                        if(!empty($type) && $type == 1){
                            // get sp qtang duoc chon (check box)
                            $items = $quoteItemFactory->create()->addFieldToFilter('request_id', $requestId)
                            ->addFieldToFilter('cart_promo_parent_item_id', $itemQuoteId)
                            ->addFieldToFilter('cart_promo_ids', $keyrule);
                            $fisrtItem = $items->getFirstItem();
                            // get sp qtang duoc chon (check box)
                            if (!empty($fisrtItem) && count($fisrtItem->getData()) > 0) {
                                $sku = $fisrtItem->getData('sku');
                                $productPromo = $productRepository->get($sku);
                                $product_unit = $productPromo->getData('product_unit') ? $productPromo->getData('product_unit'): '';
                                $rulecart[$keyrule]['sku'][] = $sku;
                                $rulecart[$keyrule]['unit_product'][] = $product_unit;
                                $rulecart[$keyrule]['qty'][] = $discountAmount;
                            }
                        }else{
                            foreach($arrskus as $item){
                                $productPromo = $productRepository->get($item);
                                $product_unit = $productPromo->getData('product_unit') ? $productPromo->getData('product_unit'): '';
                                $rulecart[$keyrule]['sku'][] = $item;
                                $rulecart[$keyrule]['unit_product'][] = $product_unit;
                                $rulecart[$keyrule]['qty'][] = $discountAmount;
                            }
                        }
                    }
                }
                
            }
            
            return $rulecart;
        } catch(\Exception $e) {
            $this->writeLog("Exception:");
            $this->writeLog($e);
        }
    }
  
    public function getRulesNameByIdRule($ruleId) {
        try {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $quoteItemFactory = $objectManager->create('Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory');
            $productRepository = $objectManager->create('Magento\Catalog\Api\ProductRepositoryInterface');
            // $messagesPrefix = $objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('ampromo/messages/prefix');
            $saleRuleFactory = $objectManager->create('Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory');
            $resource = $objectManager->create('Magento\Framework\App\ResourceConnection');
            $rules = $saleRuleFactory->create()->addFieldToFilter('rule_id',$ruleId);
            $firstItem = $rules->getFirstItem();
            $nameRules = '';
            if($firstItem){
                $nameRules = $firstItem['name'];
            }
            return  $nameRules;
        } catch(\Exception $e) {
            $this->writeLog("Exception:");
            $this->writeLog($e);
        }
    }
    public function url_exists($url) {
        $url_headers = get_headers($url);
        if(!$url_headers || $url_headers[0] == 'HTTP/1.0 404 Not Found') {
            $exists = false;
        }
        else {
            $exists = true;
        }
        return $exists;
    }
    /**
     * 
     * @return bool
     */
    public function getListQuote($items)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $arrayPrdMain = array();
        $arrayPrdCart = array();
        $arrayMain = array();
        try {
            $amPromoHelper = $objectManager->get('Amasty\Promo\Helper\Item');
            foreach($items as $item){
                $parentItem = $item->getParentItemId();
                $cartPromoOption = $item->getCartPromoOption() ? $item->getCartPromoOption(): '' ;
                $price = (int) $item->getPrice();
                $isPromoItem = $amPromoHelper->isPromoItem($item);
                if(!$parentItem && (!$isPromoItem && $cartPromoOption == '')){
                    array_push($arrayPrdMain,$item);
                }
                if(!$parentItem && ($cartPromoOption == 'ampromo_cart' || $cartPromoOption == 'ampromo_spent') ){
                    array_push($arrayPrdCart,$item);
                }
            }
            $arrayMain = array_merge($arrayPrdMain, $arrayPrdCart);

            return $arrayMain;
        } catch(\Exception $e) {
            $this->writeLog("Exception:");
            $this->writeLog($e);
        }

        return $arrayMain;
    }
    /**
     * @return bool
     */
    public function isPromoItem($itemId)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $itemOptions = $objectManager->get('Magento\Quote\Model\ResourceModel\Quote\Item\Option\CollectionFactory');
        $lastItemOptions = $itemOptions->create()->addFieldToFilter('item_id', array('eq' => $itemId));
        $lastItemOptions = $lastItemOptions->getLastItem();
        $rulesId = ''; $nameRule = '';
        if(isset($lastItemOptions['value'])){
            $arrRulesId = json_decode($lastItemOptions['value'], true);
            if(isset($arrRulesId["options"]) && isset($arrRulesId["options"]['ampromo_rule_id'])){
                $rulesId = $arrRulesId["options"]['ampromo_rule_id'];
            }
        }

        if($rulesId){
            return true;
        }

        return false;
    }
    
    /**
     * @return bool
     */
    public function isPromoItemCTT($cartPromoOption)
    {
        if($cartPromoOption == 'ampromo_cart' || $cartPromoOption == 'ampromo_spent'){
            return true;
        }
        return false;
    }
    
     /**
     * 
     * @return bool
     */
    public function getListPriceQuote($items)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $arrayPrdMain = array();
        $arrayPrdCart = array();
        $arrayMain = array();
        try {
            foreach($items as $item){
                $parentItem = $item['parent_item_id'];
                $cartPromoOption = $item['cart_promo_option'] ? $item['cart_promo_option']: '' ;
                $price = (int) $item['price'];
                $isPromoItem = $this->isPromoItem($item['item_id']);
                if(!$parentItem && (!$isPromoItem && $cartPromoOption == '')){
                    array_push($arrayPrdMain,$item);
                }
                if(!$parentItem && ($cartPromoOption == 'ampromo_cart' || $cartPromoOption == 'ampromo_spent') ){
                    array_push($arrayPrdCart,$item);
                }
            }
            $arrayMain = array_merge($arrayPrdMain, $arrayPrdCart);

            return $arrayMain;
        } catch(\Exception $e) {
            $this->writeLog("Exception:");
            $this->writeLog($e);
        }

        return $arrayMain;
    }
    /**
     * 
     * @return bool
     */
    public function getQtyGift($itemId,$qtyCurrent)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        try {
            $quoteFactory =  $objectManager->create('Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory');
            $quoteItems = $quoteFactory->create()->addFieldToFilter('cart_promo_parent_item_id', $itemId);
            $cart =  $objectManager->get('Magento\Checkout\Model\Cart');
            $productRepository =  $objectManager->get('Magento\Catalog\Api\ProductRepositoryInterface');
            if (count($quoteItems->getData()) > 0) {
                $quote = $cart->getQuote();
                foreach ($quoteItems as $item) {
                    // $this->writeLog('update $item->getId(): '.$item->getId());
                    $updateQtyPromo = $qtyCurrent * (int)$item->getCartPromoQty();
                        // check default stock sp qtang dc chon
                    if (!empty($item->getCartPromoIds())) {
                        $collection = $productRepository->get($item->getSku());
                        $defaultStockQty = $this->checkDefaultStockPromo($collection, (int)$updateQtyPromo);
                        return $defaultStockQty;
                    }
                }
                $this->writeLog("Code nay chay toi day");
            } 
        } catch(\Exception $e) {
            $this->writeLog("Exception:");
            $this->writeLog($e);
        }
    }

    public function checkDefaultStockPromo($product, $requestQty)
    {
        $result = array();
        $getDefaultStockCustom = $product->getDefaultStockCustom(); // so luong ton hien tai (real time)
        $sumQtyCurrentInQuoteItem = $product->sumQtyCurrentInQuoteItem(); // sum qty cua product hien tai trong table quote_item (real time)
        $defaultStockQty = $getDefaultStockCustom - $sumQtyCurrentInQuoteItem;

        if ($getDefaultStockCustom == 0 || $defaultStockQty == 0) {
            $result['messages'] =  'Sản phẩm quà tặng '.$product->getNameShort(). ' tạm hết hàng.';
            $result['status'] = 'warning';
            return $result;
        }

        $defaultStock = $getDefaultStockCustom - ($sumQtyCurrentInQuoteItem + $requestQty);
        if ($defaultStock < 0) {
            $result['messages'] =  'Sản phẩm quà tặng '.$product->getNameShort(). ' chỉ còn ' . $getDefaultStockCustom . ' sản phẩm';
            $result['status'] = 'warning';
            return $result;
        }

        return $result;
    }

    /**
	* @param $info
	* @param $type  [error, warning, info]
	* @return 
	*/
	private function writeLog($info, $type = "info") {
		$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/request.log');
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

