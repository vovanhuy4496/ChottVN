<?php
namespace Chottvn\SigninPhoneNumber\Model;
class PhoneVerification extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
	const CACHE_TAG = 'chottvn_phone_verification';
    const VERIFY_STATUS_ACTIVE = 1;
	const VERIFY_STATUS_INACTIVE = 0;
	
    const TYPE_SMS_VERIFY_PHONE = 1;
    const TYPE_SMS_FORGOT_PASSWORD = 2;

	protected $_cacheTag = 'chottvn_phone_verification';

	protected $_eventPrefix = 'chottvn_phone_verification';

	protected function _construct()
	{
		$this->_init('Chottvn\SigninPhoneNumber\Model\ResourceModel\PhoneVerification');
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