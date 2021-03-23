<?php
namespace Chottvn\Sms\Model;
class SendOtp extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
	const CACHE_TAG = 'chottvn_sms_otp';

	protected $_cacheTag = 'chottvn_sms_otp';

	protected $_eventPrefix = 'chottvn_sms_otp';

	protected function _construct()
	{
		$this->_init('Chottvn\Sms\Model\ResourceModel\SendOtp');
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