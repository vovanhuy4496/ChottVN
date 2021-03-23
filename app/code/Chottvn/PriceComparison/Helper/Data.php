<?php

namespace Chottvn\PriceComparison\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{

	const XML_PATH_PRICECOMPARE = 'catalog_price_comparison/';

	public function __construct(
        \Zend\Http\Client $zendClient,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->zendClient = $zendClient;
        $this->scopeConfig = $scopeConfig;
    }

	public function getConfigValue($field, $storeId = null)
	{
		return $this->scopeConfig->getValue(
			$field, ScopeInterface::SCOPE_STORE, $storeId
		);
	}

	public function getGeneralConfig($code, $storeId = null)
	{

		return $this->getConfigValue(self::XML_PATH_PRICECOMPARE .'general/'. $code, $storeId);
	}

	public function callApiPriceComparison($model_id)
    {
    	$api_url = $this->getGeneralConfig('url_api_price_comparison').'/'.$model_id;
    	$token = $this->getGeneralConfig('authorization_price_comparison');

        try 
        {
            $this->zendClient->reset();
            $this->zendClient->setUri($api_url);
            $this->zendClient->setMethod(\Zend\Http\Request::METHOD_GET); 
       	    $this->zendClient->setHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => $token,
       	    ]);
       	    // $this->zendClient->setParameterPost([
            //     'yourparameter1' => 'yourvalue1',
            // ]);
       	    $this->zendClient->send();
            $response = $this->zendClient->getResponse();
            return $response;
        }
        catch (\Zend\Http\Exception\RuntimeException $runtimeException) 
        {
            return [];
        }
    }

    public function getEnable(){
    	$enable = $this->getGeneralConfig('enable_price_comparison');

    	return $enable;
    }

}