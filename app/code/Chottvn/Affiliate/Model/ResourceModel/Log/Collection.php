<?php 
namespace Chottvn\Affiliate\Model\ResourceModel\Log;
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection{
	public function _construct(){
		$this->_init("Chottvn\Affiliate\Model\Log","Chottvn\Affiliate\Model\ResourceModel\Log");
	}
}
 ?>