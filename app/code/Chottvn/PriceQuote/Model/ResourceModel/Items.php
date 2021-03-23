<?php 
namespace Chottvn\PriceQuote\Model\ResourceModel;
	class Items extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb{
		public function _construct(){
			$this->_init("chottvn_pricequote_request_item","id");
		}
	}
?>