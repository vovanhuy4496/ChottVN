<?php

/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Chottvn\SigninPhoneNumber\Rewrite\Magento\Customer\Controller\Account;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\AccountManagement;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\SecurityViolationException;
use Chottvn\SigninPhoneNumber\Api\PhoneVerificationRepositoryInterface as PhoneVerificationRepository;

use function GuzzleHttp\json_decode;

/**
 * ForgotPasswordPost controller
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ForgotPasswordPost extends \Magento\Customer\Controller\Account\ForgotPasswordPost
{
    /**
     * @var \Magento\Customer\Api\AccountManagementInterface
     */
    protected $customerAccountManagement;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var PhoneVerificationRepository
     */
    protected $phoneVerificationRepository;

    /**
     * @var Session
     */
    protected $coreSession;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param AccountManagementInterface $customerAccountManagement
     * @param Escaper $escaper
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        AccountManagementInterface $customerAccountManagement,
        PhoneVerificationRepository $phoneVerificationRepository,
        CustomerRepositoryInterface $customerRepository,
        Escaper $escaper,
        \Magento\Framework\Session\SessionManagerInterface $coreSession
    ) {
        $this->phoneVerificationRepository = $phoneVerificationRepository;
        $this->customerRepository = $customerRepository;
        $this->coreSession = $coreSession;
        parent::__construct($context, $customerSession, $customerAccountManagement, $escaper);
    }

    /**
     * Forgot customer password action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $phoneNumber = (string) $this->getRequest()->getPost('phone_number');
        $fullName = (string) $this->getRequest()->getPost('full_name');
        if ($phoneNumber && $fullName) {
            if (!$this->validateCustomer($phoneNumber, $fullName)) {
                $this->session->setForgottenPhone($phoneNumber);
                $this->messageManager->addErrorMessage(
                    __('The phone number and full name is incorrect. Please check the information and try again.')
                );
                return $resultRedirect->setPath('*/*/forgotpassword');
            }

            try {
                $resultSendOtp = json_decode($this->phoneVerificationRepository->sendForgotPWOtp($phoneNumber));
                if (!$resultSendOtp->status) {
                    $this->writeLog("func: execute - Send OTP Failed");
                    if ($resultSendOtp->error_code == 'NOT_VERIFIED') {
                        $this->writeLog("func: execute - Phone not verified");
                        $this->coreSession->setSignInPhone($phoneNumber);
                        $this->messageManager->addErrorMessage("This phone number %1 was not verified. Please verify your phone number", $phoneNumber);
                       
                        return $resultRedirect->setPath('signinphonenumber/phoneverification/display');
                    }
                    $this->messageManager->addErrorMessage($resultSendOtp->message);
                    return $resultRedirect->setPath('*/*/forgotpassword');
                }
            } catch (NoSuchEntityException $exception) {
                $this->messageManager->addErrorMessage($exception->getMessage());
                return $resultRedirect->setPath('*/*/forgotpassword');
                // Do nothing, we don't want anyone to use this action to determine which email accounts are registered.
            } catch (SecurityViolationException $exception) {
                $this->messageManager->addErrorMessage($exception->getMessage());
                return $resultRedirect->setPath('*/*/forgotpassword');
            } catch (\Exception $exception) {
                $this->messageManager->addExceptionMessage(
                    $exception,
                    __('We\'re unable to send the password reset message.')
                );
                return $resultRedirect->setPath('*/*/forgotpassword');
            }
            $this->coreSession->setForgottenPhone($phoneNumber);
            $this->messageManager->addSuccessMessage($this->getSuccessMessage($phoneNumber));
            return $resultRedirect->setPath('*/*/createpassword');
        } else {
            $this->messageManager->addErrorMessage(__('Please enter your phone number and full name.'));
            return $resultRedirect->setPath('*/*/forgotpassword');
        }
    }

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

    protected function forgotPasswordByEmail()
    {
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $email = (string) $this->getRequest()->getPost('email');
        if ($email) {
            if (!\Zend_Validate::is($email, \Magento\Framework\Validator\EmailAddress::class)) {
                $this->session->setForgottenEmail($email);
                $this->messageManager->addErrorMessage(
                    __('The email address is incorrect. Verify the email address and try again.')
                );
                return $resultRedirect->setPath('*/*/forgotpassword');
            }

            try {
                $this->customerAccountManagement->initiatePasswordReset(
                    $email,
                    AccountManagement::EMAIL_RESET
                );
            } catch (NoSuchEntityException $exception) {
                // Do nothing, we don't want anyone to use this action to determine which email accounts are registered.
            } catch (SecurityViolationException $exception) {
                $this->messageManager->addErrorMessage($exception->getMessage());
                return $resultRedirect->setPath('*/*/forgotpassword');
            } catch (\Exception $exception) {
                $this->messageManager->addExceptionMessage(
                    $exception,
                    __('We\'re unable to send the password reset email.')
                );
                return $resultRedirect->setPath('*/*/forgotpassword');
            }
            $this->messageManager->addSuccessMessage($this->getSuccessMessage($email));
            return $resultRedirect->setPath('*/*/');
        } else {
            $this->messageManager->addErrorMessage(__('Please enter your email.'));
            return $resultRedirect->setPath('*/*/forgotpassword');
        }
    }

    /**
     * Retrieve success message
     *
     * @param string $email
     * @return \Magento\Framework\Phrase
     */
    protected function getSuccessMessage($phoneNumber)
    {
        return __(
            // 'If there is an account associated with %1 you will receive an email with a link to reset your password.',
            'You will get an OTP code for reset your password. Please check your phone',
            $this->escaper->escapeHtml($phoneNumber)
        );
    }

    /**
     * @param $info
     * @param $type  [error, warning, info]
     * @return 
     */
    private function writeLog($info, $type = "info")
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/forgotpw_post.log');
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
