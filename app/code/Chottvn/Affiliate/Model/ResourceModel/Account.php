<?php 
namespace Chottvn\Affiliate\Model\ResourceModel;
	class Account extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb{
		public function _construct(){
			$this->_init("chottvn_affiliate_account","id");
		}
	}
?>