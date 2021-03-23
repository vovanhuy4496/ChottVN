<?php

namespace Chottvn\Sales\Model\Total\Quote;

use Exception;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Exception\LocalizedException;

class OriginalTotal extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal {

    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);

        if (!count($shippingAssignment->getItems())) {
            return $this;
        }

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        
        $originalTotal = 0;
        $checkoutSession = $objectManager->get('Magento\Checkout\Model\Session');
        
        // $items = $shippingAssignment->getItems();
        $items = $checkoutSession->getQuote()->getAllItems();
        if (!count($items)) {
            return $this;
        }
        foreach ($items as $item) {
            if (empty($item->getAmpromoRuleId()) && $item->getQty() > 0) {
                // $quoteItem = $checkoutSession->getQuote()->getItemById($item->getId());
                // $productId = $quoteItem->getProduct()->getId();
                // $product = $objectManager->create('Magento\Catalog\Model\Product')->load($productId);
                $originalTotal = $originalTotal + ($item->getOriginalPrice() * $item->getQty());
            }
        }

        //$this->writeLog($originalTotal);        
        // set Tổng tiền hàng
        $quote->setOriginalTotal($originalTotal);
        $quote->setBaseOriginalTotal($originalTotal);
        
        return $this;
    }
     
    public function fetch(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        return [
            'code' => 'original_total',
            'title' => 'Original Total',
            'value' => $quote->getOriginalTotal()
            // You can change the reduced amount, or replace it with your own variable
        ];
    }

    /**
	* @param $info
	* @param $type  [error, warning, info]
	* @return 
	*/
	private function writeLog($info, $type = "info") {
		$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/chottvn_sales_quote.log');
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