<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Chottvn\PriceDecimal\Helper;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\Store\Model\Store;
use Magento\Store\Model\ScopeInterface;
use Magento\Sales\Api\PaymentFailuresInterface;

/**
 * Checkout default helper
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Data extends \Magento\Checkout\Helper\Data
{
    /**
     * Format Price
     *
     * @param float $price
     * @return string
     */
    public function formatPrice($price)
    {
        return $this->replaceFormatPrice($this->priceCurrency->format(
            $price,
            true,
            PriceCurrencyInterface::DEFAULT_PRECISION,
            $this->getQuote()->getStore()
        ));
    }

    /**
     * Format Price Space
     *
     * @param float $price
     * @return string
     */
    public function formatPriceSpace($price, $currency)
    {
        return $this->replaceFormatPriceSpace($this->priceCurrency->format(
            $price,
            true,
            PriceCurrencyInterface::DEFAULT_PRECISION,
            $this->getQuote()->getStore()
        ), $currency);
    }

    public function replaceFormatPriceSpace($value, $currency)
    {
        return str_replace(" ₫", " ".$currency, $value);
    }

    public function replaceFormatPrice($value)
    {
        return str_replace(" ₫", "đ", $value);
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
