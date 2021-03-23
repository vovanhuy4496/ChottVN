<?php
declare(strict_types=1);

namespace Chottvn\Finance\Model;

class Log extends \Magento\Framework\Model\AbstractModel
{
    const CACHE_TAG = 'chottvn_log_finance';

	protected $_cacheTag = 'chottvn_log_finance';

	protected $_eventPrefix = 'chottvn_log_finance';

	protected function _construct()
	{
		$this->_init('Chottvn\Finance\Model\ResourceModel\Log');
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

