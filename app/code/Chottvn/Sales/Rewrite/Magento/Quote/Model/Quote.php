<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Chottvn\Sales\Rewrite\Magento\Quote\Model;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Directory\Model\AllowedCountries;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Address\Total as AddressTotal;
use Magento\Sales\Model\Status;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\ObjectManager;

/**
 * Quote model
 *
 * Supported events:
 *  sales_quote_load_after
 *  sales_quote_save_before
 *  sales_quote_save_after
 *  sales_quote_delete_before
 *  sales_quote_delete_after
 *
 * @api
 * @method int getIsMultiShipping()
 * @method Quote setIsMultiShipping(int $value)
 * @method float getStoreToBaseRate()
 * @method Quote setStoreToBaseRate(float $value)
 * @method float getStoreToQuoteRate()
 * @method Quote setStoreToQuoteRate(float $value)
 * @method string getBaseCurrencyCode()
 * @method Quote setBaseCurrencyCode(string $value)
 * @method string getStoreCurrencyCode()
 * @method Quote setStoreCurrencyCode(string $value)
 * @method string getQuoteCurrencyCode()
 * @method Quote setQuoteCurrencyCode(string $value)
 * @method float getGrandTotal()
 * @method Quote setGrandTotal(float $value)
 * @method float getBaseGrandTotal()
 * @method Quote setBaseGrandTotal(float $value)
 * @method int getCustomerId()
 * @method Quote setCustomerId(int $value)
 * @method Quote setCustomerGroupId(int $value)
 * @method string getCustomerEmail()
 * @method Quote setCustomerEmail(string $value)
 * @method string getCustomerPrefix()
 * @method Quote setCustomerPrefix(string $value)
 * @method string getCustomerFirstname()
 * @method Quote setCustomerFirstname(string $value)
 * @method string getCustomerMiddlename()
 * @method Quote setCustomerMiddlename(string $value)
 * @method string getCustomerLastname()
 * @method Quote setCustomerLastname(string $value)
 * @method string getCustomerSuffix()
 * @method Quote setCustomerSuffix(string $value)
 * @method string getCustomerDob()
 * @method Quote setCustomerDob(string $value)
 * @method string getRemoteIp()
 * @method Quote setRemoteIp(string $value)
 * @method string getAppliedRuleIds()
 * @method Quote setAppliedRuleIds(string $value)
 * @method string getPasswordHash()
 * @method Quote setPasswordHash(string $value)
 * @method string getCouponCode()
 * @method Quote setCouponCode(string $value)
 * @method string getGlobalCurrencyCode()
 * @method Quote setGlobalCurrencyCode(string $value)
 * @method float getBaseToGlobalRate()
 * @method Quote setBaseToGlobalRate(float $value)
 * @method float getBaseToQuoteRate()
 * @method Quote setBaseToQuoteRate(float $value)
 * @method string getCustomerTaxvat()
 * @method Quote setCustomerTaxvat(string $value)
 * @method string getCustomerGender()
 * @method Quote setCustomerGender(string $value)
 * @method float getSubtotal()
 * @method Quote setSubtotal(float $value)
 * @method float getBaseSubtotal()
 * @method Quote setBaseSubtotal(float $value)
 * @method float getSubtotalWithDiscount()
 * @method Quote setSubtotalWithDiscount(float $value)
 * @method float getBaseSubtotalWithDiscount()
 * @method Quote setBaseSubtotalWithDiscount(float $value)
 * @method int getIsChanged()
 * @method Quote setIsChanged(int $value)
 * @method int getTriggerRecollect()
 * @method Quote setTriggerRecollect(int $value)
 * @method string getExtShippingInfo()
 * @method Quote setExtShippingInfo(string $value)
 * @method int getGiftMessageId()
 * @method Quote setGiftMessageId(int $value)
 * @method bool|null getIsPersistent()
 * @method Quote setIsPersistent(bool $value)
 * @method Quote setSharedStoreIds(array $values)
 * @method Quote setWebsite($value)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class Quote extends \Magento\Quote\Model\Quote
{    

    /**
     * Add product. Returns error message if product type instance can't prepare product.
     *
     * @param mixed $product
     * @param null|float|\Magento\Framework\DataObject $request
     * @param null|string $processMode
     * @return \Magento\Quote\Model\Quote\Item|string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function addProductNew(
        \Magento\Catalog\Model\Product $product,
        $request = null,
        $processMode = \Magento\Catalog\Model\Product\Type\AbstractType::PROCESS_MODE_FULL
    ) {
        try {
            if ($request === null) {
                $request = 1;
            }
            if (is_numeric($request)) {
                $request = $this->objectFactory->create(['qty' => $request]);
            }
            if (!$request instanceof \Magento\Framework\DataObject) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('We found an invalid request for adding product to quote.')
                );
            }
    
            if (!$product->isSalable()) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Product that you are trying to add is not available.')
                );
            }
    
            $cartCandidates = $product->getTypeInstance()->prepareForCartAdvanced($request, $product, $processMode);
    
            /**
             * Error message
             */
            if (is_string($cartCandidates) || $cartCandidates instanceof \Magento\Framework\Phrase) {
                return (string)$cartCandidates;
            }
    
            /**
             * If prepare process return one object
             */
            if (!is_array($cartCandidates)) {
                $cartCandidates = [$cartCandidates];
            }
    
            $parentItem = null;
            $errors = [];
            $item = null;
            $items = [];
            foreach ($cartCandidates as $candidate) {
                // Child items can be sticked together only within their parent
                $stickWithinParent = $candidate->getParentProductId() ? $parentItem : null;
                $candidate->setStickWithinParent($stickWithinParent);
    
                //$item = $this->getItemByProduct($candidate);
                
                $item = $this->itemProcessor->init($candidate, $request);
                $item->setQuote($this);
                $item->setOptions($candidate->getCustomOptions());
                $item->setProduct($candidate);
                // Add only item that is not in quote already
                $this->addItem($item);
                $items[] = $item;
    
                /**
                 * As parent item we should always use the item of first added product
                 */
                if (!$parentItem) {
                    $parentItem = $item;
                }
                if ($parentItem && $candidate->getParentProductId() && !$item->getParentItem()) {
                    $item->setParentItem($parentItem);
                }
    
                $this->itemProcessor->prepare($item, $request, $candidate);
    
                // collect errors instead of throwing first one
                if ($item->getHasError()) {
                    foreach ($item->getMessage(false) as $message) {
                        if (!in_array($message, $errors)) {
                            // filter duplicate messages
                            $errors[] = $message;
                        }
                    }
                }
            }
            if (!empty($errors)) {
                throw new \Magento\Framework\Exception\LocalizedException(__(implode("\n", $errors)));
            }
    
            $this->_eventManager->dispatch('sales_quote_product_add_after', ['items' => $items]);
            return $parentItem;
        } catch (\Exception $e) {
            $this->writeLog($e);
        }
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
