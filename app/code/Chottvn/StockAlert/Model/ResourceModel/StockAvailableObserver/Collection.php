<?php 
namespace Chottvn\StockAlert\Model\ResourceModel\StockAvailableObserver;
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection{
	public function _construct(){
		$this->_init("Chottvn\StockAlert\Model\StockAvailableObserver","Chottvn\StockAlert\Model\ResourceModel\StockAvailableObserver");
	}
}
 ?>