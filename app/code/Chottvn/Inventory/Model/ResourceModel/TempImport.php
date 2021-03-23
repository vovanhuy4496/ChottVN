<?php
namespace Chottvn\Inventory\Model\ResourceModel;


class TempImport extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
	
	public function __construct(
		\Magento\Framework\Model\ResourceModel\Db\Context $context
	)
	{
		parent::__construct($context);
	}
	
	protected function _construct()
	{
		$this->_init('chottvn_inventory_temp_import', 'id');
	}
	
}