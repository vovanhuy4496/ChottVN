<?php

namespace Chottvn\Sales\Model\Total\Quote;

use Exception;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Exception\LocalizedException;

class SavingsAmount extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal {

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
        
        $savingsAmount = 0;
        // $originalTotal = 0;
        // $shippingAmount = 0;
        // $totalWeight = 0;
        // $grandTotal = 0;
        // $overWeightValue = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('carriers/tablerate/handling_over_weight_fee');
        $checkoutSession = $objectManager->get('Magento\Checkout\Model\Session');

        // $countryId = $checkoutSession->getQuote()->getShippingAddress()->getCountryId();
        // $regionId = $checkoutSession->getQuote()->getShippingAddress()->getRegionId();

        $items = $checkoutSession->getQuote()->getAllItems();
        if (!count($items)) {
            return $this;
        }
        foreach ($items as $item) {
            if ($item->getQty() > 0 && empty($item->getAmpromoRuleId())) {
                // $this->writeLog(($item->getOriginalPrice()));
                // $this->writeLog(($item->getPrice()));
                // $this->writeLog(($item->getQty()));
                // $this->writeLog(($item->getDiscountAmount()));
                // $originalTotal = $originalTotal + ($item->getPrice() * $item->getQty());
                // $totalWeight = $totalWeight + ($item->getWeight() * $item->getQty());
                $savingsAmount = $savingsAmount + (($item->getOriginalPrice() - $item->getPrice()) * $item->getQty());
                // Bạn có mã giảm giá/khuyến mãi?
                // $savingsAmount = $savingsAmount + $item->getDiscountAmount();
            }
        }

        // $grandTotal = $total->getGrandTotal();
        // $grandTotal = array_sum($total->getAllTotalAmounts()) + $grandTotal;
        
        // if ($totalWeight < $overWeightValue) {
        //     $shippingAmount = $this->returnShippingAmount($objectManager, $countryId, $regionId, $totalWeight);
        // }

        // $grandTotal = $grandTotal + $shippingAmount;

        // if ($totalWeight >= $overWeightValue) {
        //     $grandTotal = $grandTotal - $shippingAmount;
        // }

        // $savingsAmount = $originalTotal - $grandTotal - $shippingAmount;

        // $this->writeLog('originalTotal: '.$originalTotal);
        // $this->writeLog('grandTotal: '.$grandTotal);
        // $this->writeLog('savingsAmount: '.$savingsAmount);
        // $this->writeLog('totalWeight: '.$totalWeight);
        // $this->writeLog('shippingAmount: '.$shippingAmount);
        // $this->writeLog('---------------------------------------------');
        
        // set Tiết kiệm
        $quote->setSavingsAmount($savingsAmount);
        $quote->setBaseSavingsAmount($savingsAmount);

        return $this;
    }
     
    public function fetch(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        return [
            'code' => 'savings_amount',
            'title' => 'Savings Amount',
            'value' => $quote->getSavingsAmount()
            // You can change the reduced amount, or replace it with your own variable
        ];
    }

    /**
	* @param $info
	* @param $type  [error, warning, info]
	* @return 
	*/
	private function writeLog($info, $type = "info") {
		$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/default_config_provider.log');
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