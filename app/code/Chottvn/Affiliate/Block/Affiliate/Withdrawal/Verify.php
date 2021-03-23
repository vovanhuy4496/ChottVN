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

namespace Chottvn\Affiliate\Block\Affiliate\Withdrawal;

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
	// Request
	protected $_request;
	protected $_response;
	public $_helperAccount;
	public $_customer;
    protected $_coreSession;
    protected $_scopeConfig;
    protected $moduleAssetDir;
     /**
     * @type PhoneVerification
     */
    protected $_phoneVerificationRepository;

	/**
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Magento\Customer\Model\Session $customer,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\App\Response\Http $response,
        \Chottvn\Affiliate\Helper\Account $helperAccount,
        ScopeConfigInterface $scopeConfig,
        \Magento\Framework\View\Asset\Repository $moduleAssetDir,
        \Magento\Framework\Session\SessionManagerInterface $coreSession,
        PhoneVerificationRepositoryInterface $phoneVerificationRepository,
        array $data = []
    ) {
        $this->_customer = $customer;
        $this->_request = $request;
        $this->_scopeConfig = $scopeConfig;
        $this->_response = $response;
        $this->_coreSession = $coreSession;
        $this->moduleAssetDir = $moduleAssetDir;
        $this->_helperAccount = $helperAccount;
        $this->_phoneVerificationRepository = $phoneVerificationRepository;
        parent::__construct($context, $data);
    }
       /**
     * Prepare layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $title = __('Withdrawal Verify');
        $this->pageConfig->getTitle()->set($title);
        // add Home breadcrumb
        $breadcrumbs = $this->getLayout()->getBlock('breadcrumbs');
        if ($breadcrumbs) {
            $breadcrumbs->addCrumb(
                'home',
                [
                    'label' => __('Home'),
                    'title' => __('Go to Home Page'),
                    'link' => $this->_storeManager->getStore()->getBaseUrl()
                ]
            )->addCrumb(
                'affiliate',
                [
                    'label' => __('Affiliate Program'),
                    'title' => __('Affiliate Program'),
                    'link' => $this->_storeManager->getStore()->getBaseUrl().'/affiliate'
                ]
            )->addCrumb(
                'account affiliate',
                [
                    'label' => __('Account Affiliate'),
                    'title' => __('Account Affiliate'),
                    'link' => $this->_storeManager->getStore()->getBaseUrl().'/customer/account/affiliate/'
                ]
            )->addCrumb(
                '',
                [
                    'label' => $title,
                    'title' => $title
                ]
            );
        }

        return parent::_prepareLayout();
    }
    public function getVerifyingPhone() {
        $phone = $this->_coreSession->getVerifyingPhone();
        return ($phone) ? $phone : null;
    }
    public function getRate() {
        $rate = $this->_coreSession->getRate();
        return ($rate) ? $rate : null;
    }
    public function getAmount() {
        $amount = $this->_coreSession->getAmount();
        return ($amount) ? $amount : null;
    }
    public function getTransactionTypeId() {
        $transactionTypeId = $this->_coreSession->getTransactionTypeId();
        return ($transactionTypeId) ? $transactionTypeId : null;
    }
    public function getRequest() {
        $request = $this->_coreSession->getRequest();
        return ($request) ? $request : null;
    }
    public function getAccountAmountAvailable() {
        $accountAmountAvailable = $this->_coreSession->getAccountAmountAvailable();
        return ($accountAmountAvailable) ? $accountAmountAvailable : null;
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
        return '/affiliate/account_withdrawal/verify';
    }

    public function getAPISendOTP() {
        return '/rest/V1/chottvn/phone-verification/send-otp';
    }

    public function getAPISendOTPByPhone() {
        return '/rest/V1/chottvn/phone-verification/send-otp-forgotpassword';
    }
    public function getUrlImage($url) {
        return $this->moduleAssetDir->getUrl($url);
    }
   
}
