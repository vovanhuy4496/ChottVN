<?php 
namespace Chottvn\Affiliate\Model\ResourceModel\Account;
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection{
	public function _construct(){
		$this->_init("Chottvn\Affiliate\Model\Account","Chottvn\Affiliate\Model\ResourceModel\Account");
	}
}
 ?>