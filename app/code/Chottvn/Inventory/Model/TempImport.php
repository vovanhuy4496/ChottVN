<?php
namespace Chottvn\Inventory\Model;
class TempImport extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
	const CACHE_TAG = 'chottvn_inventory_temp_import';

	protected $_cacheTag = 'chottvn_inventory_temp_import';

	protected $_eventPrefix = 'chottvn_inventory_temp_import';

	protected function _construct()
	{
		$this->_init('Chottvn\Inventory\Model\ResourceModel\TempImport');
	}

	public function getIdentities()
	{
		return [self::CACHE_TAG . '_' . $this->getId()];
	}

	public function getDefaultValues()
	{
		$values = [];

		return $values;
	}
}