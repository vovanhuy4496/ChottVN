<?php 
namespace Chottvn\PriceQuote\Model;
class Items extends \Magento\Framework\Model\AbstractModel{

	public function _construct(){
		$this->_init("Chottvn\PriceQuote\Model\ResourceModel\Items");
	}
}	
