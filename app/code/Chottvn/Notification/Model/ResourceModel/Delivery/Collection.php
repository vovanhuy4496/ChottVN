<?php
namespace Chottvn\Notification\Model\ResourceModel\Delivery;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
	protected $_idFieldName = 'id';
	protected $_eventPrefix = 'chottvn_notification_delivery_collection';
	protected $_eventObject = 'delivery_collection';

	/**
	 * Define resource model
	 *
	 * @return void
	 */
	protected function _construct()
	{
		$this->_init('Chottvn\Notification\Model\Delivery', 'Chottvn\Notification\Model\ResourceModel\Delivery');
	}

}