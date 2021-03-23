<?php

namespace Chottvn\Frontend\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
 
class Compare extends AbstractHelper
{
	/**
	* @var \Magento\Framework\App\Config\ScopeConfigInterface
	*/
	protected $_scopeConfig;
	protected $_storeCode;
	protected $_objectManager;

	public function __construct(
	        Context $context,
	        ScopeConfigInterface $scopeConfig,
	        StoreManagerInterface $storeManager
	    ){
		$this->_scopeConfig = $scopeConfig;
		$this->_storeManager = $storeManager;
		$this->_storeCode=$this->_storeManager->getStore()->getCode();
	    parent::__construct($context);
	}
	 
	public function getCompareProductsConfiguration(){
		$compare_config = $this->_scopeConfig->getValue('catalog_compare_configuration/general/json_compare_catalog_detect_device_config',\Magento\Store\Model\ScopeInterface::SCOPE_STORE,$this->_storeCode);
		$compare_config = json_decode($compare_config);

        return $compare_config;
	}
	
}
?>