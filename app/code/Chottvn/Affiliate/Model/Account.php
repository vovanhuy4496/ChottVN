<?php 
namespace Chottvn\Affiliate\Model;
class Account extends \Magento\Framework\Model\AbstractModel{

	public function _construct(){
		$this->_init("Chottvn\Affiliate\Model\ResourceModel\Account");
	}
}	
