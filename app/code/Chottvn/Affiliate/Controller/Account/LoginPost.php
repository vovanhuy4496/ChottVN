<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Chottvn\Affiliate\Controller\Account;

use Chottvn\Affiliate\Exception\AffiliateAccountNotActiveException;
use Chottvn\Affiliate\Exception\NotAffiliateAccountException;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Customer\Model\Account\Redirect as AccountRedirect;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\Url as CustomerUrl;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\EmailNotConfirmedException;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\State\UserLockedException;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Customer\Controller\AbstractAccount;
use Magento\Framework\Phrase;
use Chottvn\SigninPhoneNumber\Api\PhoneVerificationRepositoryInterface as PhoneVerificationRepository;
use Chottvn\SigninPhoneNumber\Exception\PhoneNotVerifiedException;
use Magento\Framework\UrlFactory;

/**
 * Post login customer action.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LoginPost extends \Magento\Customer\Controller\Account\LoginPost
{
    /**
     * @var \Magento\Customer\Api\AccountManagementInterface
     */
    protected $customerAccountManagement;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $formKeyValidator;

    /**
     * @var AccountRedirect
     */
    protected $accountRedirect;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\PhpCookieManager
     */
    private $cookieMetadataManager;

    /**
     * @var PhoneVerificationRepository
     */
    protected $phoneVerificationRepository;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlModel;

    /**
     * @var Session
     */
    protected $coreSession;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param AccountManagementInterface $customerAccountManagement
     * @param CustomerUrl $customerHelperData
     * @param Validator $formKeyValidator
     * @param AccountRedirect $accountRedirect
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        AccountManagementInterface $customerAccountManagement,
        CustomerUrl $customerHelperData,
        Validator $formKeyValidator,
        AccountRedirect $accountRedirect,
        UrlFactory $urlFactory,
        PhoneVerificationRepository $phoneVerificationRepository,
        \Magento\Framework\Session\SessionManagerInterface $coreSession
    ) {
        $this->session = $customerSession;
        $this->customerAccountManagement = $customerAccountManagement;
        $this->customerUrl = $customerHelperData;
        $this->formKeyValidator = $formKeyValidator;
        $this->accountRedirect = $accountRedirect;
        $this->phoneVerificationRepository = $phoneVerificationRepository;
        $this->coreSession = $coreSession;
        $this->urlModel = $urlFactory->create();
        parent::__construct(
            $context,
            $customerSession,
            $customerAccountManagement,
            $customerHelperData,
            $formKeyValidator,
            $accountRedirect
        );
    }

    /**
     * Get scope config
     *
     * @return ScopeConfigInterface
     * @deprecated 100.0.10
     */
    private function getScopeConfig()
    {
        if (!($this->scopeConfig instanceof \Magento\Framework\App\Config\ScopeConfigInterface)) {
            return \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\App\Config\ScopeConfigInterface::class
            );
        } else {
            return $this->scopeConfig;
        }
    }

    /**
     * Retrieve cookie manager
     *
     * @deprecated 100.1.0
     * @return \Magento\Framework\Stdlib\Cookie\PhpCookieManager
     */
    private function getCookieManager()
    {
        if (!$this->cookieMetadataManager) {
            $this->cookieMetadataManager = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\Stdlib\Cookie\PhpCookieManager::class
            );
        }
        return $this->cookieMetadataManager;
    }

    /**
     * Retrieve cookie metadata factory
     *
     * @deprecated 100.1.0
     * @return \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    private function getCookieMetadataFactory()
    {
        if (!$this->cookieMetadataFactory) {
            $this->cookieMetadataFactory = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory::class
            );
        }
        return $this->cookieMetadataFactory;
    }

    /**
     * @inheritDoc
     */
    public function createCsrfValidationException(
        RequestInterface $request
    ): ?InvalidRequestException {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('*/*/login');

        return new InvalidRequestException(
            $resultRedirect,
            [new Phrase('Invalid Form Key. Please refresh the page.')]
        );
    }

    /**
     * @inheritDoc
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return null;
    }

    /**
     * Login post action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        if ($this->session->isLoggedIn() || !$this->formKeyValidator->validate($this->getRequest())) {
            /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('*/*/login');
            return $resultRedirect;
        }

        if ($this->getRequest()->isPost()) {
            $login = $this->getRequest()->getPost('login');
            if (!empty($login['username']) && !empty($login['password'])) {
                try {
                    $this->writeLog('func: execute - try authenticate');
                    $customer = $this->customerAccountManagement->authenticate($login['username'], $login['password']);
                    // Check account is affiliate account
                    if($customer->getGroupId() != 4){
                        throw new NotAffiliateAccountException();
                    }
                    // Check account is active
                    // $this->writeLog($customer->getCustomAttribute('affiliate_status')->getValue());
                    $affiliate_status = $customer->getCustomAttribute('affiliate_status')->getValue();
                    if($affiliate_status != 'activated' && $affiliate_status != 'freezed') {
                        throw new AffiliateAccountNotActiveException();
                    }
                    $this->writeLog("func: execute - customer id: ".$customer->getId()." username: ".$login['username']." password: ".$login['password']);
                    $this->session->setCustomerDataAsLoggedIn($customer);
                    if ($this->getCookieManager()->getCookie('mage-cache-sessid')) {
                        $metadata = $this->getCookieMetadataFactory()->createCookieMetadata();
                        $metadata->setPath('/');
                        $this->getCookieManager()->deleteCookie('mage-cache-sessid', $metadata);
                    }
                    $redirectUrl = $this->accountRedirect->getRedirectCookie();
                    $this->writeLog('func: execute - $redirectUrl: '.$redirectUrl);
                    if (!$this->getScopeConfig()->getValue('customer/startup/redirect_dashboard') && $redirectUrl) {
                        $this->accountRedirect->clearRedirectCookie();
                        $resultRedirect = $this->resultRedirectFactory->create();
                        // URL is checked to be internal in $this->_redirect->success()
                        $resultRedirect->setUrl($this->_redirect->success($redirectUrl));
                        return $resultRedirect;
                    }
                    $resultRedirect = $this->resultRedirectFactory->create();
                    return $resultRedirect->setPath('customer/account');
                } 
                catch (PhoneNotVerifiedException $e) {
                    $this->writeLog('func: execute - catch phonenotverified');
                    $resultRedirect = $this->resultRedirectFactory->create();
                    // $value = $this->customerUrl->getEmailConfirmationUrl($login['username']);
                    $this->coreSession->setSignInPhone($login['username']);
                    $url = $this->urlModel->getUrl('signinphonenumber/phoneverification/display', ['_secure' => true]);
                    $message = __(
                        "This phone number %1 was not verified. Please <a href='%2'>Click here</a> to verify your phone number", 
                        $login['username'],
                        $url
                    );
                    // $resultRedirect->setUrl($this->_redirect->error($url));
                    // return $resultRedirect;
                // }  
                // catch (EmailNotConfirmedException $e) {
                //     $value = $this->customerUrl->getEmailConfirmationUrl($login['username']);
                //     $message = __(
                //         'This account is not confirmed. <a href="%1">Click here</a> to resend confirmation email.',
                //         $value
                //     );
                // } catch (UserLockedException $e) {
                //     $message = __(
                //         'The account sign-in was incorrect or your account is disabled temporarily. '
                //             . 'Please wait and try again later.'
                //     );
                // } catch (AuthenticationException $e) {
                //     $message = __(
                //         'The account sign-in was incorrect or your account is disabled temporarily. '
                //             . 'Please wait and try again later.'
                //     );
                // } catch (LocalizedException $e) {
                //     $message = $e->getMessage();
                } catch (\Exception $e) {
                    // PA DSS violation: throwing or logging an exception here can disclose customer password
                    // $this->messageManager->addError(
                    //     __('Invalid phone number or password.')
                    //  // __('An unspecified error occurred. Please contact us for assistance.')
                    // );
                    // $message = __("Invalid phone number or password.");
                    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                    $accountManagement = $objectManager->get('Chottvn\SigninPhoneNumber\Rewrite\Magento\Customer\Model\AccountManagement');
                    try {
                        $customer = $accountManagement->getCustomerRepository($login['username']);
                        $getAuthenticationCTT = $accountManagement->getAuthenticationCTT($customer->getId(), $login['password']);
                        $message = __("Invalid phone number or password.");
                        if ($customer->getCustomAttribute('is_disabled')->getValue() === '1' && $getAuthenticationCTT) {
                            $message = __('Your account is disabled. Please contact customer service.');
                        }
                    } catch (\Exception $e) {
                        $message = __("Invalid phone number or password.");
                    }
                // } catch (NotAffiliateAccountException $e) {
                //     $message = $e->getMessage();
                // } catch (AffiliateAccountNotActiveException $e) {
                //     $message = $e->getMessage();
                } finally {
                    if (isset($message)) {
                        $this->messageManager->addError($message);
                        $this->session->setUsername($login['username']);
                    }
                }
            } else {
                $this->messageManager->addError(
                    __('Invalid phone number or password.')
                    // __('A login and a password are required.')
                );
            }
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('*/*/login');
        // return $this->accountRedirect->getRedirect();
    }

    /**
     * @param $info
     * @param $type  [error, warning, info]
     * @return 
     */
    private function writeLog($info, $type = "info")
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/affiliate_loginPost.log');
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
