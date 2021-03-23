<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Chottvn\SigninPhoneNumber\Block;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Chottvn\SigninPhoneNumber\Api\PhoneVerificationRepositoryInterface;

/**
 * Customer reset password form
 *
 * @api
 * @since 100.0.2
 */
class Resetpassword extends \Magento\Customer\Block\Account\Resetpassword
{
	protected $_coreSession;
    
	public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
		ScopeConfigInterface $scopeConfig,
		\Magento\Framework\Session\SessionManagerInterface $coreSession,
		PhoneVerificationRepositoryInterface $phoneVerificationRepository
        )
	{
		$this->_scopeConfig = $scopeConfig;
		$this->_coreSession = $coreSession;
		$this->_phoneVerificationRepository = $phoneVerificationRepository;
		parent::__construct($context);
	}
	
	public function getForgottenPhone() {
		$phone = $this->_coreSession->getForgottenPhone();
		return ($phone) ? $phone : null;
	}

	public function getTimeToResendOTP() {
		$phone = $this->getForgottenPhone();
		if(!$phone) {
			return 0;
		}

		return $this->_phoneVerificationRepository->getTimeToResendOTP($phone);
	}

    public function getEffectiveTime() {
        return $this->_scopeConfig->getValue('chottvn_sms/sms_brandname/effective_time');
    }

    public function getPostResetPassword() {
        return '/customer/account/resetpasswordpost';
    }

    public function getAPIResendOtpResetPw() {
        return '/rest/V1/chottvn/phone-verification/send-otp-forgotpassword';
    }
}
