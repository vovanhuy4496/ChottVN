<?php
namespace Chottvn\SigninPhoneNumber\Model\ResourceModel\PhoneVerification;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
	protected $_idFieldName = 'id';
	protected $_eventPrefix = 'chottvn_phone_verification_collection';
	protected $_eventObject = 'chottvn_phone_verification_collection';

	/**
	 * Define resource model
	 *
	 * @return void
	 */
	protected function _construct()
	{
		$this->_init('Chottvn\SigninPhoneNumber\Model\PhoneVerification', 'Chottvn\SigninPhoneNumber\Model\ResourceModel\PhoneVerification');
	}

}