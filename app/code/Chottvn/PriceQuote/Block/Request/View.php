<?php
/**
 * Copyright © (c) chotructuyen.vn All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Chottvn\PriceQuote\Block\Request;

class View extends \Magento\Framework\View\Element\Template
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
     * @var \Chottvn\Sales\Rewrite\Magento\Quote\Model\Quote\Address\Total\Shipping
     */
    protected  $quoteTotalShipping;
   
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
     * Get Request Key from params
     *
     * @return \Chottvn\PriceQuote\Model\Request
     */
    public function getPriceQuoteRequest(){
        $requestId = $this->getData("requestId");
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $priceQuoteRequest = $objectManager->get('Chottvn\PriceQuote\Model\Request')->load($requestId);
        return $priceQuoteRequest;
    } 
    /*public function getPriceQuoteRequest_OLD(){
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        //$priceQuoteRequest = $objectManager->get('Chottvn\PriceQuote\Model\Request')->load();
        $priceQuoteRequest = $objectManager->create('Chottvn\PriceQuote\Model\ResourceModel\Request\CollectionFactory')->create()->addFieldToFilter("url_key",$this->getRequestKey())->getFirstItem();
        return $priceQuoteRequest;
    } */

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
   
    public function getRequestId()
    {
        return $this->getData("requestId");
    }
    /**
	* @param $info
	* @param $type  [error, warning, info]
	* @return 
	*/
	private function writeLog($info, $type = "info") {
		$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/view.log');
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

