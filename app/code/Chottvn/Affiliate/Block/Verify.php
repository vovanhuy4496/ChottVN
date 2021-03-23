<?php

/**
 * A Magento 2 module named Chottvn/Affiliate
 * Copyright (C) 2020 Chottvn
 *
 * This file included in Chottvn/SigninWithPhoneNumber is licensed under OSL 3.0
 *
 * http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * Please see LICENSE.txt for the full text of the OSL 3.0 license
 */

namespace Chottvn\Affiliate\Block;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Chottvn\SigninPhoneNumber\Api\PhoneVerificationRepositoryInterface;

/**
 * Phone Verify
 *
 * @api
 * @since 100.0.2
 */
class Verify extends \Magento\Framework\View\Element\Template
{
	protected $_coreSession;
	
    protected $_session;

    protected $_scopeConfig;

    /**
     * @type PhoneVerification
     */
    protected $_phoneVerificationRepository;
    
	public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
		ScopeConfigInterface $scopeConfig,
        Session $customerSession,
		\Magento\Framework\Session\SessionManagerInterface $coreSession,
		PhoneVerificationRepositoryInterface $phoneVerificationRepository
        )
	{
		$this->_scopeConfig = $scopeConfig;
        $this->_session = $customerSession;
		$this->_coreSession = $coreSession;
		$this->_phoneVerificationRepository = $phoneVerificationRepository;
		parent::__construct($context);
	}
	
	public function getVerifyingPhone() {
		$phone = $this->_coreSession->getVerifyingPhone();
		return ($phone) ? $phone : null;
	}

	public function getTimeToResendOTP() {
		$phone = $this->getVerifyingPhone();
		if(!$phone) {
			return 0;
		}

		return $this->_phoneVerificationRepository->getTimeToResendOTP($phone);
	}

    public function getEffectiveTime() {
        return $this->_scopeConfig->getValue('chottvn_sms/sms_brandname/effective_time');
    }

    public function getCustomerId() {
        return $this->_session->getCustomerId();
    }

    public function getPostVerifyPhoneNumber() {
        return '/affiliate/verify/verify';
    }

    public function getAPISendOTP() {
        return '/rest/V1/chottvn/phone-verification/send-otp';
    }

    public function getAPISendOTPByPhone() {
        return '/rest/V1/chottvn/phone-verification/send-otp-phone';
    }
}
