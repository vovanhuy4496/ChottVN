<?php
namespace Chottvn\Inventory\Model\ResourceModel\TempImport;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
	protected $_idFieldName = 'id';
	protected $_eventPrefix = 'chottvn_temp_import_collection';
	protected $_eventObject = 'chottvn_temp_import_collection';

	/**
	 * Define resource model
	 *
	 * @return void
	 */
	protected function _construct()
	{
		$this->_init('Chottvn\Inventory\Model\TempImport', 'Chottvn\Inventory\Model\ResourceModel\TempImport');
	}

}