<?php
namespace Chottvn\Sms\Model\ResourceModel;


class SendOtp extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
	
	public function __construct(
		\Magento\Framework\Model\ResourceModel\Db\Context $context
	)
	{
		parent::__construct($context);
	}
	
	protected function _construct()
	{
		$this->_init('chottvn_sms_otplog', 'id');
	}
	
}