<?php
/**
 * Copyright © (c) chotructuyen.vn All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Chottvn\PriceQuote\Controller\Request;

class Create extends \Magento\Framework\App\Action\Action
{

    protected $resultPageFactory;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context  $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {        
        try{
            $postData = $this->getRequest()->getParams();
            if (!$postData) {
                $this->_redirect('*/*/');
                return;
            }
            // Prepare Data
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $customerSession = $objectManager->create('Magento\Customer\Model\Session');
            //$customerData = $customerSession->getCustomer()->getData();
            //$customerId = $customerSession->getCustomer()->getId();            
            $checkoutSection = $objectManager->get('Magento\Checkout\Model\Session');
            $quote = $checkoutSection->getQuote();
            $address = $quote->getShippingAddress();
            // Create request
            $request = $objectManager->create('Chottvn\PriceQuote\Model\Request');
            $request->setData($postData);
            $request->setCustomerId($customerSession->getId());
            $request->setQuoteId($quote->getId());
            $request->setUrlKey($this->createUrlKey());
            $request->setCreatedAt(time());
            $request->setUpdatedAt(time());
            $request->setGrandTotal($quote->getGrandTotal());
            $request->setBaseGrandTotal($quote->getBaseGrandTotal());
            $request->setCheckoutMethod($quote->getCheckoutMethod());
            $request->setAppliedRuleIds($quote->getAppliedRuleIds());
            $request->setCouponCode($quote->getCouponCode());
            $request->setSubtotal($quote->getSubtotal());
            $request->setBaseSubtotal($quote->getBaseSubtotal());
            $request->setSavingsAmount($quote->getSavingsAmount());
            $request->setBaseSavingsAmount($quote->getBaseSavingsAmount());
            $request->setOriginalTotal($quote->getOriginalTotal());
            $request->setFlagShipping($quote->getFlagShipping());
            $request->setShippingAmount($address->getShippingAmount());
            $request->setStreet($address->getStreet()[0]);
            $request->setCity($address->getCity());
            $request->setRegion($address->getRegion());
            $request->setRegionId($address->getRegionId());
            $request->setPostcode($address->getPostcode());
            $request->setCountryId($address->getCountryId());
            $request->setCityId($address->getCityId());
            $request->setTownship($address->getTownship());
            $request->setTownshipId($address->getTownshipId());
            $request->setShippingMethod($address->getShippingMethod());
            $request->setDiscountAmount($address->getDiscountAmount());
            $request->setBaseDiscountAmount($address->getBaseDiscountAmount());
            $request->save();
            // Create Item
            $quoteManager = $objectManager->create('Magento\Quote\Model\QuoteFactory');
            $quoteFactory = $quoteManager->create()->load($quote->getId());
            $itemQuote = $quoteFactory->getAllItems();
            $itemRequest = $objectManager->create('Chottvn\PriceQuote\Model\Items');
            foreach($itemQuote as $item){
                $parentItem = $item->getParentItemId();
                    $product = $objectManager->create('Magento\Catalog\Model\Product')->load($item->getData('product_id'));
                    $blockRules = $objectManager->create('Chottvn\Frontend\Block\Rules');
                    $catalogRule = $blockRules->getIdCatalogRuleByProduct($product);
                    $cartRule = $blockRules->getIdCartRuleByProduct($product);
                    $itemquoteId = $item->getItemId();
                    // all rules
                    $allRule = array_merge($cartRule,$catalogRule);
                    // Add guarantee
                    $guaranteeValue = $product->getData('guarantee') ? $product->getData('guarantee'):'';
                    // Add name short to product
                    $productNameShort = $product->getNameShort() ? $product->getNameShort():'';
                    // Add model 
                    $model = $product->getData('model') ? $product->getData('model'):'';
                    // Add product unit 
                    $productUnit = $product->getData('product_unit') ? $product->getData('product_unit'): '';
                    // Add brand id
                    $productBrandHelper = $objectManager->create('Ves\Brand\Helper\ProductBrand');
                    $productBrand = $productBrandHelper->getFirstBrandByProduct($product);
                    $productBrandId = '';
                    if($productBrand){
                         $productBrandId = $productBrand->getData('brand_id') ? $productBrand->getData('brand_id'):'';
                    }
                    //options product
                    $itemquoteId = $item->getId();
                    $optionsQuote = $objectManager->get('Magento\Quote\Model\ResourceModel\Quote\Item\Option\CollectionFactory');
                    $optionQuoteCollection = $optionsQuote->create()->addFieldToFilter('item_id',$itemquoteId)->addFieldToFilter('code','attributes');
                    $lastOptionQuote = $optionQuoteCollection->getLastItem();
                    $productOptions = '';
                    $productOptions = $lastOptionQuote->getData('value');
                    $cartPromoParentId = $item->getData('cart_promo_parent_id') ? $item->getData('cart_promo_parent_id'): '';
                    $cartPromoItemIds = $item->getData('cart_promo_item_ids') ? $item->getData('cart_promo_item_ids'): '';
                    $cartPromoOption = $item->getData('cart_promo_option') ? $item->getData('cart_promo_option'): '';
                    $cartPromoIds = $item->getData('cart_promo_ids') ? $item->getData('cart_promo_ids'): '';
                    $cartPromoQty = $item->getData('cart_promo_qty') ? $item->getData('cart_promo_qty'): '';
                    $cartPromoParentItemId = $item->getData('cart_promo_parent_item_id') ? $item->getData('cart_promo_parent_item_id'): '';
                    $productKind = $product->getData('product_kind') ? $product->getData('product_kind'): $this->getValueDefaultProductKind();
                    $itemRequest->setData($item->getData());
                    $itemRequest->setData('request_id',$request->getId());
                    $itemRequest->setData('product_id',$item->getData('product_id'));
                    $itemRequest->setData('product_name_short',$productNameShort);
                    $itemRequest->setData('model',$model);
                    $itemRequest->setData('product_unit',$productUnit);
                    $itemRequest->setData('product_brand_id',$productBrandId);
                    $itemRequest->setData('product_kind',$productKind);
                    $itemRequest->setData('guarantee',$guaranteeValue);
                    $itemRequest->setData('original_price',$product->getData('price'));
                    $itemRequest->setData('base_original_price',$product->getData('price'));
                    $itemRequest->setData('all_rule_ids',json_encode($allRule));
                    $itemRequest->setData('product_options',$productOptions);
                    $itemRequest->setData('cart_promo_parent_id',$cartPromoParentId);
                    $itemRequest->setData('cart_promo_item_ids', $cartPromoItemIds);
                    $itemRequest->setData('cart_promo_qty',$cartPromoQty);
                    $itemRequest->setData('cart_promo_parent_item_id',$cartPromoParentItemId);
                    $itemRequest->setData('cart_promo_option',$cartPromoOption);
                    $itemRequest->setData('cart_promo_ids',$cartPromoIds);
                    $itemRequest->save();
            }
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath($this->getRequestViewUrl($request->getUrlKey()));
            return $resultRedirect;
            
            /*$resultPage = $this->resultPageFactory->create();
            $resultPage->getConfig()->getTitle()->set(__('Price Quote'));
            return $resultPage;*/
        }catch(\Exception $e){

            $this->_redirect('*/*/');
        }
        
    }
    public function getValueDefaultProductKind(){
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $tableName = $resource->getTableName('eav_attribute');
        $select =$resource->getConnection()->select()->from($tableName)
            ->where('attribute_code = ?', 'product_kind');
        $items = $resource->getConnection()->fetchRow($select);
        return $items['default_value'];
    }
    private function getRequestViewUrl($requestKey){
        return 'price_quote/request/view/key/'.$requestKey;
    }
    private function createUrlKey(){
        return md5(time()."ChoTT");
    }

      /**
     * @param $info
     * @param $type  [error, warning, info]
     * @return 
     */
    private function writeLog($info, $type = "info")
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/create_pricequote.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        switch ($type) {
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