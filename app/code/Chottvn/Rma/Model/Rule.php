<?php
namespace Chottvn\Rma\Model;
class Rule extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
	const CACHE_TAG = 'chottvn_rma_rule';

	protected $_cacheTag = 'chottvn_rma_rule';

	protected $_eventPrefix = 'chottvn_rma_rule';

	protected function _construct()
	{
		$this->_init('Chottvn\Rma\Model\ResourceModel\Rule');
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