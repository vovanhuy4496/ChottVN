<?php
/**
 * Copyright © (c) chotructuyen.vn All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Chottvn\PriceQuote\Block\Request;

class Pdf extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Url
     */
    protected $_catalogUrlBuilder;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;    

    /**
     * @var Quote|null
     */
    protected $_quote = null;

    /**
     * @var array
     */
    protected $_totals;

    /**
     * TODO: MAGETWO-34827: unused object?
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;   

    /**
     * @var \Chottvn\PriceQuote\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Checkout\Helper\Cart
     */
    protected $cartHelper;

    /**
     * @var \Chottvn\PriceDecimal\Helper\Data
     */
    protected $checkoutHelper;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context  $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Catalog\Model\ResourceModel\Url $catalogUrlBuilder
     * @param \Chottvn\PriceQuote\Helper\Data $helper
     * @param \Magento\Checkout\Helper\Cart $cartHelper
     * @param \Chottvn\PriceDecimal\Helper\Data $checkoutHelper     
     * @param \Magento\Framework\App\Http\Context $httpContext
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Catalog\Model\ResourceModel\Url $catalogUrlBuilder,
        \Chottvn\PriceQuote\Helper\Data $helper,
        \Magento\Checkout\Helper\Cart $cartHelper,
        \Magento\Framework\App\Http\Context $httpContext,
        \Chottvn\PriceDecimal\Helper\Data $checkoutHelper,
        \Chottvn\Sales\Rewrite\Magento\Quote\Model\Quote\Address\Total\Shipping $quoteTotalShipping,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_customerSession = $customerSession;
        $this->_checkoutSession = $checkoutSession;        
        $this->_catalogUrlBuilder = $catalogUrlBuilder;    
        $this->helper = $helper; 
        $this->cartHelper = $cartHelper;
        $this->checkoutHelper = $checkoutHelper;
        $this->httpContext = $httpContext;
        $this->quoteTotalShipping = $quoteTotalShipping;
    }

    /**
     * @codeCoverageIgnore
     * @return int
     */
    public function getItemsCount($request){
        $collection = $this->helper->getCollectionItems($request);
        $collection->getSelect()
        ->reset(\Zend_Db_Select::COLUMNS)
        ->columns("COUNT(main_table.request_id) as count")->group('main_table.request_id');
        $lastItem = $collection->getLastItem();
        $count = $lastItem->getData('count');
        return $count;
    }

    /**
     * Get all cart items
     *
     * @return array
     * @codeCoverageIgnore
     */
    public function getItems()
    {
        return $this->getQuote()->getAllVisibleItems();
    }

    /**
     * Get item row html
     *
     * @param   \Magento\Quote\Model\Quote\Item $item
     * @return  string
     */
    public function getItemHtml(\Magento\Quote\Model\Quote\Item $item)
    {
        // $renderer = $this->getItemRenderer($item->getProductType())->setItem($item);
        // return $renderer->toHtml();
        return "";
    }

    /**
     * Get active quote
     *
     * @return Quote
     */
    public function getQuote()
    {
        if (null === $this->_quote) {
            $quoteId = $this->getPriceQuoteRequest()->getQuoteId();
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $this->_quote = $objectManager->create('Magento\Quote\Model\Quote')->load($quoteId);
        }        
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
     * Get totals
     *
     * @return Quote
     */
    public function getSubtotal()
    {
        return $this->getShippingAddress()->getSubtotal();
    }
    public function getShippingAmount()
    {
        $getShippingAmount = $this->getShippingAddress()->getShippingAmount();
        
        if ($getShippingAmount > 0) {
            return $getShippingAmount;
        }

        return 0;
    }
    public function getCustomShippingAmount()
    {
        $checkFlagShipping = $this->getQuote()->getFlagShipping();

        if ($checkFlagShipping == 'freeshipping') {
            return __('Free Shipping');
        }
        if ($this->isOverWeight() == "over") {
            return __('Price Contact');
        }
        // chua chon address ben checkout
        if (empty($this->getShippingAddress()->getData('region_id'))) {
            return __('Not included');
        }
        $getShippingAmount = $this->getShippingAmount();
        if ($getShippingAmount > 0) {
            return $this->formatPrice($getShippingAmount);
        }
        if ($checkFlagShipping == 'accept' && $getShippingAmount == 0) {
            return __('Not included');
        }
        return $this->formatPrice($getShippingAmount);
    }
    public function getDiscountAmount()
    {
        return $this->getShippingAddress()->getDiscountAmount();
    }
    public function getDiscounts()
    {
        $discounts = [];

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $coupon = $objectManager->get('\Magento\SalesRule\Model\Coupon');

        if (empty($this->getCouponCode())) {
            return $discounts;
        }

        $coupon->loadByCode($this->getCouponCode());
        $salesruleIds[] = $coupon->getRuleId();

        if (count($salesruleIds) < 1) {
            return $discounts;
        }

        foreach($salesruleIds as $index => $salesruleId) {
            $salesRule = $objectManager->get('\Magento\SalesRule\Model\Rule');
            $rule = $salesRule->load($salesruleId);
            if ($rule->getIsActive()) {
                $discount['name'] = $rule->getName();
                $discount['discount_amount'] = $rule->getDiscountAmount();
                $discounts[$index] = $discount;
            }
        }
        return $discounts;
    }
    public function getOriginalTotal()
    {
        $originalTotal = $this->getQuote()->getOriginalTotal();
        if ((int)$originalTotal > 0) {
            return $originalTotal;
        }

        $items = $this->getQuote()->getAllItems();

        if (!count($items)) {
            return $this;
        }
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        foreach ($items as $item) {
            if (empty($item->getAmpromoRuleId()) && $item->getQty() > 0) {
                $quoteItem = $this->getQuote()->getItemById($item->getId());
                $productId = $quoteItem->getProduct()->getId();
                $product = $objectManager->create('Magento\Catalog\Model\Product')->load($productId);

                $originalTotal = $originalTotal + ($product->getPrice() * $item->getQty());
            }
        }
        return $originalTotal;
    }
    public function getSavingsAmount()
    {
        $savingsAmount = $this->getQuote()->getSavingsAmount();
        if ((int)$savingsAmount < 0) {
            $savingsAmount = 0;
            $items = $this->getQuote()->getAllItems();

            if (!count($items)) {
                return $this;
            }
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    
            foreach ($items as $item) {
                if (empty($item->getAmpromoRuleId()) && $item->getQty() > 0) {
                    $quoteItem = $this->getQuote()->getItemById($item->getId());
                    $productId = $quoteItem->getProduct()->getId();
                    $product = $objectManager->create('Magento\Catalog\Model\Product')->load($productId);
                    (int)$savingsAmount = (int)$savingsAmount + (((int)$product->getPrice() - (int)$item->getPrice()) * (int)$item->getQty());
                }
            }
            return $savingsAmount;
        }
        return $savingsAmount;
    }
    public function isOverWeight()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $handlingOverWeightFee = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('carriers/tablerate/handling_over_weight_fee');
        $totalWeight = $this->checkWeight();

        if ($totalWeight > $handlingOverWeightFee) {
            return "over";
        }
        return "accept";
    }
    public function checkWeight()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $checkoutSession = $objectManager->get('Magento\Checkout\Model\Session');
        $ruleResource = $objectManager->get('Magento\CatalogRule\Model\ResourceModel\Rule');
        $totalWeight = 0;
        $items = $this->getItems();
        $rule = false;

        foreach ($items as $item) {
            if($item->getAppliedRuleIds()){
                $arrayAppliedRuleIds = explode(',', $item->getAppliedRuleIds());
                $rule = $this->checkProductHaveFreeShip($arrayAppliedRuleIds);
            }
            // $this->writeLog($rule);
            if ($item->getQty() > 0 && empty($item->getAmpromoRuleId()) && !$rule) {
                // $this->writeLog($item->getWeight());
                $totalWeight = $totalWeight + ($item->getWeight() * $item->getQty());
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
    public function getGrandTotal()
    {
        $getGrandTotal = $this->getShippingAddress()->getGrandTotal();

        return $getGrandTotal;
    }
    public function getCustomTitleGrandTotal($shippingAmount)
    {
        $title = __('Grand Total');
        if ($shippingAmount > 0) {
            return $title;
        }
        $checkFlagShipping = $this->getQuote()->getFlagShipping();

        if (($this->isOverWeight() == "over" || $shippingAmount == 0) && $checkFlagShipping != 'freeshipping') {
            $title = __('Grand Total Temp');
        }
        return $title;
    }
  
    /**
     * Format price
     *
     * @param float $value
     * @return string
     */
    public function formatPrice($value)
    {
        return $this->checkoutHelper->formatPrice($value);
    }

    /**
     * @codeCoverageIgnore
     * @return string
     */
    public function getCheckoutUrl()
    {
        return $this->getUrl('checkout', ['_secure' => true]);
    }

    /**
     * @return string
     */
    public function getContinueShoppingUrl()
    {
        $url = $this->getData('continue_shopping_url');
        if ($url === null) {
            $url = $this->_checkoutSession->getContinueShoppingUrl(true);
            if (!$url) {
                $url = $this->_urlBuilder->getUrl();
            }
            $this->setData('continue_shopping_url', $url);
        }
        return $url;
    }

    /**
     * @codeCoverageIgnore
     * @return int
     */
    public function getItemsSummaryQty()
    {
        return $this->getQuote()->getItemsSummaryQty();
    }

    /**
     * Get Request Key from params
     *
     * @return string
     */
    public function getRequestKey()
    {
        return $this->getRequest()->getParam("key");
    }  

    /**
     * Get QuoteRequest from Id
     *
     * @return \Chottvn\PriceQuote\Model\Request
     */
    public function getPriceQuoteRequest(){
        $requestId = $this->getData("requestId");
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $priceQuoteRequest = $objectManager->get('Chottvn\PriceQuote\Model\Request')->load($requestId);
        return $priceQuoteRequest;
    } 

    public function isRequestNotFound(){
        return empty($this->getPriceQuoteRequest()->getId());
    }

    public function getCompanyName(){
        return $this->getPriceQuoteRequest()->getCompanyName();
    }

    public function getContactName(){
        return $this->getPriceQuoteRequest()->getContactName();
    }

    public function getRequestDate(){
        return $this->formatDate($this->getPriceQuoteRequest()->getCreatedAt());
    }
     /**
	* @param $info
	* @param $type  [error, warning, info]
	* @return 
	*/
	private function writeLog($info, $type = "info") {
		$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/pdf.log');
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

