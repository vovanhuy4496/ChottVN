<?php 
namespace Chottvn\StockAlert\Model\ResourceModel;
	class StockAvailableObserver extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb{
		public function _construct(){
			$this->_init("chottvn_stockalert_request","id");
		}
	}
?>