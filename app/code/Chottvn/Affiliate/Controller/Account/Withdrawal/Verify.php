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

use Chottvn\SigninPhoneNumber\Api\PhoneVerificationRepositoryInterface as PhoneVerificationRepository;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Customer\Model\Session;
use Magento\Framework\UrlFactory;
use Chottvn\SigninPhoneNumber\Api\SigninInterface as HandlerSignin;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Chottvn\Affiliate\Helper\Log as HelperAffiliateLog;
use Chottvn\Affiliate\Model\Log as AffiliateLog;
use Magento\Framework\Exception\SessionException;

class Verify extends \Magento\Framework\App\Action\Action implements \Magento\Framework\App\Action\HttpPostActionInterface
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
     * @var SigninInterface
     */
    protected $handlerSignin;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $_customerRepository;
    /**
     * @var Session
     */
    protected $coreSession;
    /**
     * @var HelperAffiliateLog
     */
    protected $helperAffiliateLog;
	 /**
     * @var HelperData
     */
    protected $_affiliateHelper;

     /**
     * @var HelperData
     */
    protected $scopeConfig;
    
    protected $_helperFinanceLog;

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
        PhoneVerificationRepository $phoneVerificationRepository,
        Validator $formKeyValidator = null,
        UrlFactory $urlFactory,
        HandlerSignin $handlerSignin,
        CustomerRepositoryInterface $customerRepository,
        Session $customerSession,
        \Magento\Framework\Session\SessionManagerInterface $coreSession,
        HelperAffiliateLog $helperAffiliateLog,
        \Chottvn\Finance\Helper\Log $helperFinanceLog,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Chottvn\Affiliate\Helper\Data $affiliateHelper
	) {
        $this->handlerSignin = $handlerSignin;
        $this->session = $customerSession;
        $this->urlModel = $urlFactory->create();
        $this->coreSession = $coreSession;
        $this->phoneVerificationRepository = $phoneVerificationRepository;
        $this->_customerRepository = $customerRepository;
        $this->formKeyValidator = $formKeyValidator ?: \Magento\Framework\App\ObjectManager::getInstance()->get(Validator::class);
        $this->_helperAffiliateLog = $helperAffiliateLog;
        $this->_affiliateHelper = $affiliateHelper;
        $this->scopeConfig = $scopeConfig;
        $this->_helperFinanceLog = $helperFinanceLog;
		return parent::__construct($context);
	}

    /**
     * Verify phone number
     *
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute() {
      
		$resultRedirect = $this->resultRedirectFactory->create();
		$url = $this->urlModel->getUrl('*/*/code', ['_secure' => true]);

        if (!$this->getRequest()->isPost()
            || !$this->formKeyValidator->validate($this->getRequest())
        ){
            return $resultRedirect->setUrl($this->_redirect->error($url));
        }
        $message = '';
        try{
            $customerId = $this->session->getCustomer()->getId() ? $this->session->getCustomer()->getId(): '';
            $phoneNumber = $this->getRequest()->getParam('phone_number') ? $this->getRequest()->getParam('phone_number'): '';
            $otpCode = $this->getRequest()->getParam('auth_code') ? $this->getRequest()->getParam('auth_code'): '';
            $transactionTypeId =  $this->getRequest()->getParam('transaction_type_id') ? $this->getRequest()->getParam('transaction_type_id'): '';
            $amount = $this->getRequest()->getParam('amount') ? $this->getRequest()->getParam('amount'): '';
            $withdrawalRewardAmountMin = $this->getRequest()->getParam('withdrawalrewardamountmin') ? $this->getRequest()->getParam('withdrawalrewardamountmin'): '';
            $rate =  $this->getRequest()->getParam('rate') ? $this->getRequest()->getParam('rate'): '';
            $requestId =  $this->getRequest()->getParam('request_id') ? $this->getRequest()->getParam('request_id'): '';
            $accountAmountAvailable =  $this->getRequest()->getParam('accountamountavailable') ? $this->getRequest()->getParam('accountamountavailable'): 0;
            // $this->writeLog($accountAmountAvailable);
            // $this->writeLog($amount);
            // $this->writeLog($withdrawalRewardAmountMin);
            // $this->writeLog($transactionTypeId);
            // $this->writeLog($amount);
            // $this->writeLog($rate);
            // $this->writeLog($request_id);
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $timefc = $objectManager->create('\Magento\Framework\Stdlib\DateTime\DateTime');
            $timeinterface = $objectManager->create('\Magento\Framework\Stdlib\DateTime\TimezoneInterface');
            $priceHelper = $objectManager->get('Chottvn\PriceDecimal\Helper\Data');
            $customer = $this->_customerRepository->getById($customerId);
            $dateRequest = $timeinterface->date($timefc->gmtDate())->format('d/m/Y');
            $data = [
                "fullName" => $customer->getFirstname(),
                "toEmail" => $customer->getCustomAttribute('customer_email')->getValue(),
                "amountRequest" => $priceHelper->formatPrice($amount),
                "dateRequest" => $dateRequest
            ];
            // request
            $request = $objectManager->create('Chottvn\Finance\Model\Request');
            $transaction = $objectManager->create('Chottvn\Finance\Model\Transaction');
            $collection = $request->getCollection()
            ->addFieldToFilter('request_id', ['eq' => $requestId]);
            $lastRequest = $collection->getLastItem();
            $requestDate = $lastRequest->getData('created_at');
            $updateRequest = $request->load($requestId);
            if($withdrawalRewardAmountMin <= $amount && $amount <= $accountAmountAvailable){
                $this->phoneVerificationRepository->validateOtp($customerId, $phoneNumber, $otpCode);
                // Save transaction 
                $currentDate = $timefc->gmtDate();
                $transaction->setAccountId($customerId);
                $transaction->setRequestId($requestId);
                $transaction->setStatus(1);
                $transaction->setAmount($amount);
                $transaction->setTransactionTypeId($transactionTypeId);
                $transaction->setRate($rate);
                $transaction->setStartDate($requestDate);
                $transaction->setTransactionDate($currentDate);
                $transaction->save();
                // update request
                $request->setStatus(10);
                $request->save();
                $this->coreSession->setNotification('success');
                $data += [
                    "message" => __('CHO TRUC TUYEN has confirmed the withdrawal request. We will pay you within 7 working days')
                ];
                $notification = 'success';
                // Send mail
                if ($this->scopeConfig->getValue('email_affiliate/withdrawal_affiliate/enabled') == 1) {
                    $this->_affiliateHelper->sendWithdrawalAffiliateEmail($data);
                }
            }else{
                $this->coreSession->setNotification('amountinvalid');
                $notification = 'amountinvalid';
            }
        }catch (SessionException $e) {
            $notification = 'otpexpired';
            $this->messageManager->addError(__('Authentication code has been invalid. Your withdrawal request has been canceled. Please do it again.'));
            $this->coreSession->setNotification('otpexpired');
        }catch (\Exception $e) {
            $notification = 'error';
            $this->writeLog($e->getMessage());
            $this->coreSession->setNotification('error');
        }
         // Save log transaction
         if ($transaction->getId()) {
            $this->_helperFinanceLog->saveLogWithResource([
                "account_id" => $customerId,
                "resource_type" => 'chottvn_finance_transaction',
                "resource_id" => $transaction->getId(),
                "event" => 'widthdrawal_transaction',
                "notification" => $notification,
                "value" => [
                    "request_id" => $requestId,
                    "status" => $transaction->getStatus(),
                    "amount" => $transaction->getAmount(),
                    "transaction_type_id" => $transaction->getTransactionTypeId(),
                    "rate" => $transaction->getRate(),
                    "start_date" => $transaction->getStartDate(),
                    "transaction_date" => $transaction->getTransactionDate()
                ]
            ]);
        }
        if ($requestId) {
            // Save log request
            $this->_helperFinanceLog->saveLogWithResource([
                "account_id" => $customerId,
                "notification" => $notification,
                "resource_type" => 'chottvn_finance_request',
                "event" => 'widthdrawal_request',
                "resource_id" => $updateRequest->getId(),
                "value" => [
                    "request_id" => $request->getId(),
                    "transaction_type_id" => $request->getTransactionTypeId(),
                    "amount" => $request->getAmount(),
                    "status" => $request->getStatus()
                ]
            ]);
        }
        return $resultRedirect->setPath('customer/account/withdrawalsuccess');
    }
      /**
     * @param $info
     * @param $type  [error, warning, info]
     * @return 
     */
    private function writeLog($info, $type = "info")
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/verify.log');
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
