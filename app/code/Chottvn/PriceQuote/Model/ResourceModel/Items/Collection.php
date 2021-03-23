<?php 
namespace Chottvn\PriceQuote\Model\ResourceModel\Items;
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection{
	public function _construct(){
		$this->_init("Chottvn\PriceQuote\Model\Items","Chottvn\PriceQuote\Model\ResourceModel\Items");
	}
}
 ?>