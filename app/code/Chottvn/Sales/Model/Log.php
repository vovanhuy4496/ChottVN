<?php
namespace Chottvn\Sales\Model;
class Log extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
	const CACHE_TAG = 'chottvn_log_sales_order';

	protected $_cacheTag = 'chottvn_log_sales_order';

	protected $_eventPrefix = 'chottvn_log_sales_order';

	protected function _construct()
	{
		$this->_init('Chottvn\Sales\Model\ResourceModel\Log');
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