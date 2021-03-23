<?php

/**
 * Copyright (c) 2019 2020 ChottVN
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace Chottvn\Affiliate\Controller\Account;

use Chottvn\SigninPhoneNumber\Api\PhoneVerificationRepositoryInterface as PhoneVerificationRepository;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Customer\Model\Session;
use Magento\Framework\UrlFactory;
use Magento\Customer\Model\ForgotPasswordToken\GetCustomerByToken;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Framework\Encryption\Helper\Security;
use Magento\Framework\Exception\State\ExpiredException;
use Magento\Framework\Exception\State\InputMismatchException;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Intl\DateTimeFactory;
use Magento\Customer\Model\Customer as CustomerModel;
use Chottvn\Affiliate\Helper\Log as HelperAffiliateLog;
use Chottvn\Affiliate\Model\Log as AffiliateLog;
use Magento\Framework\Exception\NoSuchEntityException;

class Active extends \Magento\Framework\App\Action\Action implements \Magento\Framework\App\Action\HttpGetActionInterface
{
    /**
     * @var PhoneVerificationRepository
     */
    protected $phoneVerificationRepository;

    /**
     * @var Validator
     */
    private $formKeyValidator;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlModel;

    /**
     * @var GetCustomerByToken
     */
    protected $getByToken;

    /**
     * @var CustomerRegistry
     */
    protected $_customerRegistry;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $_customerRepository;

    /**
     * @var CustomerModel
     */
    protected $customerModel;

    /**
     * @var DateTimeFactory
     */
    private $dateTimeFactory;

    /**
     * @var Session
     */
    protected $coreSession;

    /**
     * @var HelperAffiliateLog
     */
    protected $helperAffiliateLog;

    protected $_affiliateHelper;

    protected $_storeManager;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        PhoneVerificationRepository $phoneVerificationRepository,
        Validator $formKeyValidator = null,
        UrlFactory $urlFactory,
        GetCustomerByToken $getByToken = null,
        CustomerRegistry $customerRegistry,
        CustomerRepositoryInterface $customerRepository,
        CustomerModel $customerModel,
        DateTimeFactory $dateTimeFactory = null,
        Session $customerSession,
        \Magento\Framework\Session\SessionManagerInterface $coreSession,
        \Chottvn\Affiliate\Helper\Data $affiliateHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        HelperAffiliateLog $helperAffiliateLog
    ) {
        $this->session = $customerSession;
        $this->urlModel = $urlFactory->create();
        $this->phoneVerificationRepository = $phoneVerificationRepository;
        $this->_customerRegistry = $customerRegistry;
        $this->_customerRepository = $customerRepository;
        $this->customerModel = $customerModel;
        $this->coreSession = $coreSession;
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->formKeyValidator = $formKeyValidator ?: $objectManager->get(Validator::class);
        $this->dateTimeFactory = $dateTimeFactory ?: $objectManager->get(DateTimeFactory::class);
        $this->getByToken = $getByToken
            ?: $objectManager->get(GetCustomerByToken::class);
        $this->_helperAffiliateLog = $helperAffiliateLog;
        $this->_affiliateHelper = $affiliateHelper;
        $this->_storeManager = $storeManager;
        return parent::__construct($context);
    }

    /**
     * Verify phone number
     *
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $activeToken = (string)$this->getRequest()->getParam('token');

        try {
            // Check token is valid and not expired
            $this->validateToken($activeToken);

            // Get customer from token
            $customer = $this->getByToken->execute($activeToken);

            // Set affiliate_status to active
            $customer = $this->_customerRepository->getById($customer->getId());
            $customer->setCustomAttribute("affiliate_status", "activated");
            $customer->setCustomAttribute("affiliate_level", "ctv");
            $customer->setGroupId('4');

            // Save log
            $this->_helperAffiliateLog->saveLog(["account_id" => $customer->getId(), "event" => AffiliateLog::EVENT_ACTIVATED]);
            $this->_helperAffiliateLog->saveLog([
                "account_id" => $customer->getId(),
                "event" => AffiliateLog::EVENT_AFFILIATE_LEVEL_CHANGED,
                "value" => "ctv"
            ]);

            $phoneNumber = $customer->getCustomAttribute('phone_number')->getValue();

            // Check need to reset password, if true then redirect to resetpassword, if false then reset token    
            $customerSecure = $this->_customerRegistry->retrieveSecureData($customer->getId());
            $hash = $customerSecure->getPasswordHash();

            // Reset token
            //Update secure data
            $customerSecure->setRpToken(null);
            $customerSecure->setRpTokenCreatedAt(null);
            $this->_customerRepository->save($customer);

            $this->messageManager->addSuccess(__('Your affiliate account is active successfull.'));

            // Send email activated successfull
            $data = [
                "fullName" => $customer->getFirstname(),
                "toEmail" => $customer->getCustomAttribute('customer_email')->getValue(),
                "affiliateCode" => $customer->getCustomAttribute('affiliate_code')->getValue(),
                "username" => $customer->getCustomAttribute('phone_number')->getValue(),
                "homeLink" => $this->_storeManager->getStore()->getBaseUrl()
            ];
            $this->_affiliateHelper->sendActiveSuccessEmail($data);

            if (is_null($hash)) {
                $resultSendOtp = json_decode($this->phoneVerificationRepository->sendForgotPWOtp($phoneNumber));
                if (!$resultSendOtp->status) {
                    if ($resultSendOtp->error_code == 'NOT_VERIFIED') {
                        $this->coreSession->setSignInPhone($phoneNumber);
                        $this->messageManager->addErrorMessage("This phone number %1 was not verified. Please verify your phone number", $phoneNumber);

                        return $resultRedirect->setPath('signinphonenumber/phoneverification/display');
                    }

                    $this->messageManager->addErrorMessage($resultSendOtp->message);
                    // return $resultRedirect->setPath('affiliate/account/forgotpassword');
                }

                $this->coreSession->setActiveAffiliateMessage(__('Your affiliate account is active successfull, please input OTP code we have just sent to your phone number and reset password.'));
                $this->coreSession->setForgottenPhone($phoneNumber);
                $resultRedirect->setPath('affiliate/account/createaffiliatepassword');
            } else {
                $resultRedirect->setPath('affiliate/account/login');
            }

            // Update email
            $this->updateEmail($customer);

            return $resultRedirect;
        } catch (ExpiredException $e) {
            // $this->messageManager->addErrorMessage($e->getMessage());
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('affiliate/account/linkexpired');

            return $resultRedirect;
        } catch (InputMismatchException $e) {
            // $this->messageManager->addErrorMessage($e->getMessage());
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('affiliate/account/linkexpired');

            return $resultRedirect;
        } catch (NoSuchEntityException $e) {
            // $this->messageManager->addErrorMessage($e->getMessage());
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('affiliate/account/linkexpired');

            return $resultRedirect;
        } catch (\Exception $exception) {
            $this->writeLog('Chottvn\Affiliate\Controller\Account\Active - excute - exception:' . $exception->getMessage());
            $this->messageManager->addErrorMessage(__('Your password reset link has expired.'));
            /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('affiliate/account/forgotpassword');

            return $resultRedirect;
        }
    }

    public function updateEmail($customer)
    {
        try {
            if (is_null($customer->getEmail())) {
                $customer->setEmail($customer->getCustomAttribute('customer_email')->getValue());
            }
            $this->_customerRepository->save($customer);
        } catch (\Exception $exception) {
            $this->writeLog($exception->getMessage());
        }
    }

    private function validateToken($activeToken)
    {
        try {
            $customerId = $this->getByToken
                ->execute($activeToken)
                ->getId();

            $customerSecureData = $this->_customerRegistry->retrieveSecureData($customerId);
            $rpToken = $customerSecureData->getRpToken();
            $rpTokenCreatedAt = $customerSecureData->getRpTokenCreatedAt();
            if (!Security::compareStrings($rpToken, $activeToken)) {
                throw new InputMismatchException(__('The password token is mismatched. Reset and try again.'));
            } elseif ($this->isActiveLinkTokenExpired($rpToken, $rpTokenCreatedAt)) {
                throw new ExpiredException(__('Account activation link has expired. Please contact HR.'));
            }
        } catch (\Exception $e) {
            throw new NoSuchEntityException(__('Your password reset link has expired.'));
        }
    }

    /**
     * Check if rpToken is expired
     *
     * @param string $rpToken
     * @param string $rpTokenCreatedAt
     * @return bool
     */
    public function isActiveLinkTokenExpired($rpToken, $rpTokenCreatedAt)
    {
        if (empty($rpToken) || empty($rpTokenCreatedAt)) {
            return true;
        }

        $expirationPeriod = $this->customerModel->getResetPasswordLinkExpirationPeriod();

        $currentTimestamp = $this->dateTimeFactory->create()->getTimestamp();
        $tokenTimestamp = $this->dateTimeFactory->create($rpTokenCreatedAt)->getTimestamp();
        if ($tokenTimestamp > $currentTimestamp) {
            return true;
        }

        $hourDifference = floor(($currentTimestamp - $tokenTimestamp) / (60 * 60));
        if ($hourDifference >= $expirationPeriod) {
            return true;
        }

        return false;
    }

    /**
     * @param $info
     * @param $type  [error, warning, info]
     * @return 
     */
    private function writeLog($info, $type = "info")
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/affiliate_active.log');
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
