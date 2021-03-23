<?php
namespace Chottvn\Notification\Model;

class Delivery extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
	const CACHE_TAG = 'chottvn_notification_delivery';

	protected $_cacheTag = 'chottvn_notification_delivery';

	protected $_eventPrefix = 'chottvn_notification_delivery';

	protected function _construct()
	{
		$this->_init('Chottvn\Notification\Model\ResourceModel\Delivery');
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