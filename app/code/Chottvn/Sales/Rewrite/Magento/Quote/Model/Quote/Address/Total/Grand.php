<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Chottvn\Sales\Rewrite\Magento\Quote\Model\Quote\Address\Total;

class Grand extends \Magento\Quote\Model\Quote\Address\Total\Grand
{
    /**
     * Collect grand total address amount
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        $grandTotal = $total->getGrandTotal();
        $baseGrandTotal = $total->getBaseGrandTotal();
        $totals = array_sum($total->getAllTotalAmounts());
        $baseTotals = array_sum($total->getAllBaseTotalAmounts());

        $grandTotal = $grandTotal + $totals;
        $baseGrandTotal = $baseGrandTotal + $baseTotals;

        $total->setGrandTotal($grandTotal);
        $total->setBaseGrandTotal($baseGrandTotal);
        return $this;
    }

    /**
     * Add grand total information to address
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function fetch(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total)
    {
        $title = __('Grand Total');
        $flagShipping = $quote->getFlagShipping();
        
        if ($flagShipping == 'over' || empty($quote->getShippingAddress()->getRegionId())) {
            $title = __('Grand Total Temp');
        }
        if ($flagShipping == 'accept' && $total->getShippingAmount() == 0) {
            $title = __('Grand Total Temp');
        }
        if ($flagShipping == 'freeshipping') {
            $title = __('Grand Total');
        }
        
        return [
            'code' => $this->getCode(),
            'title' => $title,
            'value' => $total->getGrandTotal(),
            'area' => 'footer',
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
