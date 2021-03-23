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

namespace Chottvn\Affiliate\Controller\Account\Withdrawal;

use Magento\Framework\Controller\ResultFactory;
use Chottvn\Affiliate\Helper\Log as HelperAffiliateLog;
use Chottvn\Affiliate\Model\Log as AffiliateLog;
use Chottvn\Affiliate\Helper\Data;

class Handle extends \Magento\Framework\App\Action\Action
{
    protected $_pageFactory;
    protected $_request;

    /**
     * @var Validator
     */
    private $formKeyValidator;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var \Magento\Customer\Model\CustomerExtractor
     */
    protected $customerExtractor;

    /**
     * @var \Magento\Customer\Model\ResourceModel\CustomerRepository
     */
    protected $accountManagement;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var \Magento\Customer\Model\Metadata\FormFactory
     */
    protected $formFactory;

    /**
     * @var \Magento\Customer\Api\Data\RegionInterfaceFactory
     */
    protected $regionDataFactory;

    /**
     * @var \Magento\Customer\Api\Data\AddressInterfaceFactory
     */
    protected $addressDataFactory;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var \Chottvn\SigninPhoneNumber\Model\Handler\Signin
     */
    protected $signInPhoneNubmer;

    /**
     * @var \Magento\Customer\Model\Group
     */
    protected $groupCustomer;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var PhoneVerificationRepository
     */
    protected $phoneVerificationRepository;

    /**
     * @var Session
     */
    protected $coreSession;

    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $customerModel;

    /**
     * @var \Magento\Customer\Model\Address
     */
    protected $addressModel;

    /**
     * @var \Magento\Customer\Model\AddressFactory
     */
    protected $addressFactory;

    /**
     * @var \Chottvn\SigninPhoneNumber\Model\PhoneVerificationRepository
     */
    protected $phoneVerification;

    /**
     * @var HelperAffiliateLog
     */
    protected $helperAffiliateLog;

    protected $_affiliateHelper;

    protected $scopeConfig;

    protected $_helperFinanceLog;

    public function __construct(
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\CustomerExtractor $customerExtractor,
        \Magento\Customer\Api\AccountManagementInterface $accountManagement,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Model\Customer $customerModel,
        \Magento\Customer\Model\ResourceModel\CustomerRepository $customerRepository,
        \Magento\Customer\Model\Metadata\FormFactory $formFactory,
        \Magento\Customer\Api\Data\RegionInterfaceFactory $regionDataFactory,
        \Magento\Customer\Api\Data\AddressInterfaceFactory $addressDataFactory,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Chottvn\SigninPhoneNumber\Model\Handler\Signin $signInPhoneNubmer,
        \Magento\Customer\Model\Group $groupCustomer,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Chottvn\SigninPhoneNumber\Api\PhoneVerificationRepositoryInterface $phoneVerificationRepository,
        \Magento\Framework\Session\SessionManagerInterface $coreSession,
        \Magento\Customer\Model\Address $addressModel,
        \Magento\Customer\Model\AddressFactory $addressFactory,
        \Chottvn\SigninPhoneNumber\Model\PhoneVerificationRepository $phoneVerification,
        \Magento\Framework\App\Action\Context $context,
        HelperAffiliateLog $helperAffiliateLog,
        \Chottvn\Finance\Helper\Log $helperFinanceLog,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        Data $affiliateHelper
    ) {
        $this->_pageFactory = $pageFactory;
        $this->_request = $request;
        $this->formKeyValidator = $formKeyValidator;
        $this->session = $customerSession;
        $this->customerExtractor = $customerExtractor;
        $this->accountManagement = $accountManagement;
        $this->customerRepository = $customerRepository;
        $this->customerFactory = $customerFactory;
        $this->formFactory = $formFactory;
        $this->regionDataFactory = $regionDataFactory;
        $this->addressDataFactory = $addressDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->signInPhoneNubmer = $signInPhoneNubmer;
        $this->groupCustomer = $groupCustomer;
        $this->storeManager = $storeManager;
        $this->phoneVerificationRepository = $phoneVerificationRepository;
        $this->coreSession = $coreSession;
        $this->customerModel = $customerModel;
        $this->addressModel = $addressModel;
        $this->addressFactory = $addressFactory;
        $this->phoneVerification = $phoneVerification;
        $this->_helperAffiliateLog = $helperAffiliateLog;
        $this->_helperFinanceLog = $helperFinanceLog;
        $this->_affiliateHelper = $affiliateHelper;
        $this->scopeConfig = $scopeConfig;

        return parent::__construct($context);
    }

    public function execute()
    {
        try{
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            // số tiền khả dụng
            $accountamountavailable = (int) $this->getRequest()->getParam('accountamountavailable') ? (int) $this->getRequest()->getParam('accountamountavailable'): 0;
            // số tiền rút
            $withdrawal =  $this->getRequest()->getParam('withdrawal')? $this->getRequest()->getParam('withdrawal'): '0';
            $withdrawal = intval(str_replace('.', '', $withdrawal));
            // số tiền tối thiểu có thể rút
            $withdrawalRewardAmountMin = (int) $this->getRequest()->getParam('withdrawalrewardamountmin') ? (int) $this->getRequest()->getParam('withdrawalrewardamountmin') :0;
            // transaction type id
            $transactionTypeId = (int) $this->getRequest()->getParam('transaction_type_id_form') ? (int) $this->getRequest()->getParam('transaction_type_id_form') : 0;
            if($withdrawalRewardAmountMin <= $withdrawal && $withdrawal <= $accountamountavailable){
                $customerId = $this->session->getCustomer()->getId();
                // Save Request 
                $request = $objectManager->create('Chottvn\Finance\Model\Request');
                $timefc = $objectManager->create('\Magento\Framework\Stdlib\DateTime\DateTime');
                $currentDate = $timefc->gmtDate();
                $request->setAccountId($customerId);
                $request->setTransactionTypeId($transactionTypeId);
                $request->setAmount($withdrawal);
                $request->setStatus(0);
                $request->setCreatedAt($currentDate);
                $request->save();
                if ($request->getId()) {
                    // Save log request
                    $this->_helperFinanceLog->saveLogWithResource([
                        "account_id" => $customerId,
                        "resource_type" => 'chottvn_finance_request',
                        "resource_id" => $request->getId(),
                        "event" => 'widthdrawal_request',
                        "notification" => '',
                        "value" => [
                            "request_id" => $request->getId(),
                            "transaction_type_id" => $request->getTransactionTypeId(),
                            "amount" => $request->getAmount(),
                            "status" => $request->getStatus()
                        ]
                    ]);
                }
                $collection = $request->getCollection()
                ->addFieldToFilter('transaction_type_id', ['eq' => $transactionTypeId])
                ->addFieldToFilter('amount', ['eq' => $withdrawal])
                ->addFieldToFilter('status', ['eq' => 0])
                ->addFieldToFilter('account_id', ['eq' => $customerId]);
                $lastRequest = $collection->getLastItem();
                $request_id = $lastRequest->getData('request_id');
                // rate 
                $transactiontype = $objectManager->create('Chottvn\Finance\Model\TransactionType');;
                $collection = $transactiontype->getCollection()->addFieldToFilter('transactiontype_id', ['eq' => $transactionTypeId]);
                $lastRate = $collection->getLastItem();
                $rate = $lastRate->getData('rate');
                // Send OTP
                $customer_current = $this->customerRepository->getById($customerId);
                $phoneNumberAttribute = $customer_current->getCustomAttribute('phone_number');
                $resultSendOtp = json_decode($this->phoneVerificationRepository->sendForgotPWOtp($phoneNumberAttribute->getValue()));
                if (!$resultSendOtp->status) {
                    $response = $this->resultFactory
                    ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
                    ->setData([
                        'status'  => "error",
                        'message' => __("Invalid withdrawal")
                    ]);
                }
                // session save value
                $this->coreSession->setVerifyingPhone($phoneNumberAttribute->getValue());
                $this->coreSession->setTransactionTypeId($transactionTypeId);
                $this->coreSession->setAmount($withdrawal);
                $this->coreSession->setRate($rate);
                $this->coreSession->setRequest($request_id);
                $this->coreSession->setAccountAmountAvailable($accountamountavailable);
                $response = $this->resultFactory
                    ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
                    ->setData([
                        'status'  => "ok",
                        'message' => __("Successfull!"),
                        'redirect_url' => $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB) . 'customer/account/withdrawalcode'
                    ]);
            } else {
                $response = $this->resultFactory
                    ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
                    ->setData([
                        'status'  => "error",
                        'message' => __("Invalid amount")
                    ]);
            }
        } catch (\Exception $e) {
            $this->writeLog($e->getMessage());
            $response = $this->resultFactory
                ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
                ->setData([
                    'status'  => "error",
                    'message' => __($e->getMessage())
                ]);
        }

        return $response;
    }


    /**
     * @param $info
     * @param $type  [error, warning, info]
     * @return 
     */
    private function writeLog($info, $type = "info")
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/handle.log');
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
