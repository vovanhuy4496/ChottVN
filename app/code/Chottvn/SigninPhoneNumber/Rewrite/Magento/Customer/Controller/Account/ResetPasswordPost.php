<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Chottvn\SigninPhoneNumber\Rewrite\Magento\Customer\Controller\Account;

use Chottvn\SigninPhoneNumber\Rewrite\Magento\Customer\Model\AccountManagement;
// use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Exception\InputException;
use Magento\Customer\Model\Customer\CredentialsValidator;
use Chottvn\SigninPhoneNumber\Api\PhoneVerificationRepositoryInterface as PhoneVerificationRepository;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\SessionException;
use Magento\Framework\Exception\ValidatorException;

/**
 * Class ResetPasswordPost
 *
 * @package Magento\Customer\Controller\Account
 */
class ResetPasswordPost extends \Magento\Customer\Controller\Account\ResetPasswordPost
{
    /**
     * @var \Magento\Customer\Api\AccountManagementInterface
     */
    protected $accountManagement;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var PhoneVerificationRepository
     */
    protected $phoneVerificationRepository;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param AccountManagementInterface $accountManagement
     * @param CustomerRepositoryInterface $customerRepository
     * @param CredentialsValidator|null $credentialsValidator
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        AccountManagement $accountManagement,
        CustomerRepositoryInterface $customerRepository,
        PhoneVerificationRepository $phoneVerificationRepository,
        CredentialsValidator $credentialsValidator = null,
		\Magento\Framework\Session\SessionManagerInterface $coreSession
    ) {
        $this->phoneVerificationRepository = $phoneVerificationRepository;
		$this->coreSession = $coreSession;
        parent::__construct($context, $customerSession, $accountManagement, $customerRepository);
    }

    /**
     * Reset forgotten password
     *
     * Used to handle data received from reset forgotten password form
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $this->writeLog("func: execute - create password");
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
		$phone = $this->coreSession->getForgottenPhone();
        $otpCode = (string)$this->getRequest()->getPost('auth_code');
        $password = (string)$this->getRequest()->getPost('password');
        $passwordConfirmation = (string)$this->getRequest()->getPost('password_confirmation');

        $this->writeLog("func: execute - forgotten phone number".$phone);

        if ($password !== $passwordConfirmation) {
            $this->messageManager->addError(__("New Password and Confirm New Password values didn't match."));
            $resultRedirect->setPath('*/*/createPassword');

            return $resultRedirect;
        }
        if (iconv_strlen($password) <= 0) {
            $this->messageManager->addError(__('Please enter a new password.'));
            $resultRedirect->setPath('*/*/createPassword');

            return $resultRedirect;
        }

        try {
            $this->writeLog("func: execute - Reset password by OTP");
            $this->accountManagement->resetPasswordByOTP(
                $phone,
                $otpCode,
                $password
            );
            $this->messageManager->addSuccess(__('You updated your password.'));
            $resultRedirect->setPath('*/*/login');

            return $resultRedirect;
        } catch (InputException $e) {
            $this->messageManager->addError($e->getMessage());
            foreach ($e->getErrors() as $error) {
                $this->messageManager->addError($error->getMessage());
            }
        } catch (NoSuchEntityException $e) {
            $this->messageManager->addError(__('There are no account with phone number %1', $phone));
        } catch (SessionException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (ValidatorException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $exception) {
            // $this->messageManager->addError(__('Something went wrong while saving the new password.'));
            $this->messageManager->addError($exception->getMessage());
        }
        $resultRedirect->setPath('*/*/createPassword');

        return $resultRedirect;
    }

    /**
     * @param $info
     * @param $type  [error, warning, info]
     * @return 
     */
    private function writeLog($info, $type = "info")
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/reset_pw_post.log');
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
