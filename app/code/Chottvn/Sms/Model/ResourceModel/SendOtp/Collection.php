<?php
namespace Chottvn\Sms\Model\ResourceModel\SendOtp;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
	protected $_idFieldName = 'id';
	protected $_eventPrefix = 'chottvn_sent_otp_log_collection';
	protected $_eventObject = 'chottvn_sent_otp_log_collection';

	/**
	 * Define resource model
	 *
	 * @return void
	 */
	protected function _construct()
	{
		$this->_init('Chottvn\Sms\Model\SendOtp', 'Chottvn\Sms\Model\ResourceModel\SendOtp');
	}

}