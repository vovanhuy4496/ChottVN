<?php 
namespace Chottvn\StockAlert\Model;
class StockAvailableObserver extends \Magento\Framework\Model\AbstractModel{

	public function _construct(){
		$this->_init("Chottvn\StockAlert\Model\ResourceModel\StockAvailableObserver");
	}
}	
