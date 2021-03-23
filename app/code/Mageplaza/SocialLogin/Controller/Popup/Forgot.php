<?php

/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category  Mageplaza
 * @package   Mageplaza_SocialLogin
 * @copyright Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license   https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\SocialLogin\Controller\Popup;

use Exception;
use Magento\Captcha\Helper\Data as CaptchaData;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\AccountManagement;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\SecurityViolationException;
use Mageplaza\SocialLogin\Helper\Data;
use Zend_Validate;
use Zend_Validate_Exception;
use Chottvn\SigninPhoneNumber\Api\PhoneVerificationRepositoryInterface as PhoneVerificationRepository;
use Magento\Framework\UrlFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;

/**
 * Class Forgot
 *
 * @package Mageplaza\SocialLogin\Controller\Popup
 */
class Forgot extends Action
{
    /**
     * @var AccountManagementInterface
     */
    protected $customerAccountManagement;

    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @type JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @type CaptchaData
     */
    protected $captchaHelper;

    /**
     * @type Data
     */
    protected $socialHelper;

    /**
     * @var PhoneVerificationRepository
     */
    protected $phoneVerificationRepository;

    /**
     * @var Session
     */
    protected $coreSession;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlModel;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param AccountManagementInterface $customerAccountManagement
     * @param Escaper $escaper
     * @param JsonFactory $resultJsonFactory
     * @param CaptchaData $captchaHelper
     * @param Data $socialHelper
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        AccountManagementInterface $customerAccountManagement,
        Escaper $escaper,
        JsonFactory $resultJsonFactory,
        CaptchaData $captchaHelper,
        PhoneVerificationRepository $phoneVerificationRepository,
        CustomerRepositoryInterface $customerRepository,
        Data $socialHelper,
        UrlFactory $urlFactory,
        \Magento\Framework\Session\SessionManagerInterface $coreSession
    ) {
        $this->urlModel = $urlFactory->create();
        $this->session                   = $customerSession;
        $this->customerAccountManagement = $customerAccountManagement;
        $this->escaper                   = $escaper;
        $this->resultJsonFactory         = $resultJsonFactory;
        $this->captchaHelper             = $captchaHelper;
        $this->socialHelper              = $socialHelper;
        $this->phoneVerificationRepository = $phoneVerificationRepository;
        $this->coreSession = $coreSession;
        $this->customerRepository = $customerRepository;

        parent::__construct($context);
    }

    /**
     * @return bool
     */
    public function checkCaptcha()
    {
        $formId       = 'user_forgotpassword';
        $captchaModel = $this->captchaHelper->getCaptcha($formId);
        $resolve      = $this->socialHelper->captchaResolve($this->getRequest(), $formId);

        return !($captchaModel->isRequired() && !$captchaModel->isCorrect($resolve));
    }

    /**
     * @return $this|ResponseInterface|ResultInterface
     * @throws Zend_Validate_Exception
     */
    public function execute()
    {
        /**
         * @var Json $resultJson
         */
        $resultJson = $this->resultJsonFactory->create();

        $result = [
            'success' => false,
            'message' => []
        ];

        if (!$this->checkCaptcha()) {
            $result['message'] = __('Incorrect CAPTCHA.');

            return $resultJson->setData($result);
        }

        return $this->forgotByPhone();
    }

    private function forgotByPhone()
    {
        $result = [
            'success' => false,
            'message' => []
        ];

        $resultJson = $this->resultJsonFactory->create();
        /**
         * @var Redirect $resultRedirect
         */
        $phoneNumber = (string) $this->getRequest()->getPost('phone_number');
        $fullName = (string) $this->getRequest()->getPost('full_name');
        if ($phoneNumber && $fullName) {
            if (!$this->validateCustomer($phoneNumber, $fullName)) {
                $result['errors']   = true;
                $result['message'][] = __('The phone number and full name is incorrect. Please check the information and try again.');
                $this->writeLog("func:forgotByPhone - Return result to FE".json_encode($result));
                return $resultJson->setData($result);
            }

            try {
                $resultSendOtp = json_decode($this->phoneVerificationRepository->sendForgotPWOtp($phoneNumber));
                if (!$resultSendOtp->status) {
                    if ($resultSendOtp->error_code == 'NOT_VERIFIED') {
                        $this->coreSession->setSignInPhone($phoneNumber);

                        $urlVerifyPhone = $this->urlModel->getUrl('signinphonenumber/phoneverification/display');
                        $result['errors']   = true;
                        $result['message'][] = __(
                            "This phone number %1 was not verified. Please <a href='%2'>Click here</a> to verify your phone number", 
                            $phoneNumber,
                            $urlVerifyPhone
                        );

                        $this->writeLog("func:forgotByPhone - Return result to FE".json_encode($result));
                        return $resultJson->setData($result);
                    }
                }

                $this->coreSession->setForgottenPhone($phoneNumber);
                $result['success']   = true;
                $result['message'][] = $this->getSuccessMessage($phoneNumber);
                $result['redirect'] = $this->urlModel->getUrl('customer/account/createpassword');
            } catch (NoSuchEntityException $e) {
                $result['errors']     = true;
                $result['message'][] = __(
                    'If there is an account associated with %1 you will receive an OTP Code for reset your password.',
                    $this->escaper->escapeHtml($phoneNumber)
                );
                // Do nothing, we don't want anyone to use this action to determine which email accounts are registered.
            } catch (SecurityViolationException $exception) {
                $result['errors']     = true;
                $result['message'][] = $exception->getMessage();
            } catch (Exception $exception) {
                $result['errors']     = true;
                $result['message'][] = __('We\'re unable to send the password reset OTP code.');
                // $result['message'][] = $exception->getMessage();
            }
        }

        $this->writeLog("func:forgotByPhone - Return result to FE".json_encode($result));
        return $resultJson->setData($result);
    }

    private function forgotByEmail()
    {
        $resultJson = $this->resultJsonFactory->create();
        /**
         * @var Redirect $resultRedirect
         */
        $email = (string) $this->getRequest()->getPost('email');
        if ($email) {
            if (!Zend_Validate::is($email, 'EmailAddress')) {
                $this->session->setForgottenEmail($email);
                $result['message'][] = __('Please correct the email address.');
            }

            try {
                $this->customerAccountManagement->initiatePasswordReset(
                    $email,
                    AccountManagement::EMAIL_RESET
                );
                $result['success']   = true;
                $result['message'][] = __(
                    'If there is an account associated with %1 you will receive an email with a link to reset your password.',
                    $this->escaper->escapeHtml($email)
                );
            } catch (NoSuchEntityException $e) {
                $result['success']   = true;
                $result['message'][] = __(
                    'If there is an account associated with %1 you will receive an email with a link to reset your password.',
                    $this->escaper->escapeHtml($email)
                );
                // Do nothing, we don't want anyone to use this action to determine which email accounts are registered.
            } catch (SecurityViolationException $exception) {
                $result['errors']     = true;
                $result['message'][] = $exception->getMessage();
            } catch (Exception $exception) {
                $result['errors']     = true;
                $result['message'][] = __('We\'re unable to send the password reset email.');
            }
        }

        return $resultJson->setData($result);
    }
    
    protected function getSuccessMessage($phoneNumber)
    {
        return __(
            // 'If there is an account associated with %1 you will receive an email with a link to reset your password.',
            'You will get an OTP code to %1 for reset your password. Please check your phone',
            $this->escaper->escapeHtml($phoneNumber)
        );
    }

    /**
     * @param $phoneNumber
     * @param $fullName
     * @return bool
     */
    protected function validateCustomer($phoneNumber, $fullName)
    {
        try {
            $customer = $this->customerRepository->getByPhoneNumber($phoneNumber);
            if($customer->getId()){
                if($customer->getFirstname() != $fullName) {
                    return false;
                }
            }
        }catch(\Exception $exception) {
            return false;
        }

        return true;
    }

    /**
     * @param $info
     * @param $type  [error, warning, info]
     * @return 
     */
    private function writeLog($info, $type = "info")
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/popup_forgot.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        switch ($type) {
            case "error":
                $logger->err($info);
                break;
            case "warning":
                $logger->notice($info);
                break;
            case "info":
                $logger->info($info);
                break;
            default:
                $logger->info($info);
        }
    }
}
