<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Chottvn\Affiliate\Controller\Adminhtml\Index;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Chottvn\Affiliate\Model\Log as AffiliateLog;

/**
 * Approve Affiliate action.
 */
class Freezed extends \Chottvn\Affiliate\Controller\Adminhtml\Index implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Chottvn_Affiliate::manage';

    /**
     * Approve affiliate action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $customerId = $this->initCurrentCustomer();
        
        $resultRedirect = $this->resultRedirectFactory->create();
        $formKeyIsValid = $this->_formKeyValidator->validate($this->getRequest());
        $isPost = $this->getRequest()->isPost();
        if (!$formKeyIsValid || !$isPost) {
            $this->messageManager->addError(__('Affliate could not be verify.'));
            return $resultRedirect->setPath(
                '*/*/edit',
                ['id' => $customerId, '_current' => true]
            );
        }

        if (!empty($customerId)) {
            try {
                $customer = $this->_customerRepository->getById($customerId);
                if ($customer) {
                    /** @var Customer $customer */
                    // Prepare email data
                    $data = [
                        "homeLink" => $this->_storeManager->getStore()->getBaseUrl(),
                        "fullName" => $customer->getFirstname(),
                        "toEmail" => $customer->getCustomAttribute('customer_email')->getValue(),
                        "affiliateCode" => $customer->getCustomAttribute('affiliate_code')->getValue(),
                        "username" => $customer->getCustomAttribute('phone_number')->getValue(),
                    ];

                    // $customer->setCustomAttribute("affiliate_status", "freezed");
                    // $this->_customerRepository->save($customer);

                    $customerFactory = $this->_customerFactory->create()->load($customerId);

                    $customerData = $customerFactory->getDataModel();
                    $customerData->setCustomAttribute('affiliate_status', 'freezed');
                    // $customerFactory->setFailuresNum(10);
                    // $customerFactory->setLockExpires('2038-01-01 00:00:00');
                    $customerFactory->updateData($customerData);

                    $customerFactory->save();
                    // $this->writeLog($customerFactory->getFailuresNum());
                    // $this->writeLog($customerFactory->getLockExpires());
                    
                    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                    $checkSendMail = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('email_affiliate/freezed_unfreezed_affiliate/enabled');
                    // Send Email
                    if ($checkSendMail == 1) {
                        $this->_affiliateHelper->sendFreezedEmail($data);
                    }

                    // Save log
                    $this->_helperAffiliateLog->saveLog(["account_id" => $customerId, "event" => AffiliateLog::EVENT_FREEZED]);
                    $this->messageManager->addSuccess(__('You freezed the affiliate.'));
                } else {
                    $this->messageManager->addError(__("Your affiliate you are requesting is not exists"));
                }
            } catch (\Exception $exception) {
                $this->messageManager->addError($exception->getMessage());
            }
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath(
            '*/*/edit',
            ['id' => $customerId, '_current' => true]
        );
    }

    /**
     * @param $info
     * @param $type  [error, warning, info]
     * @return 
     */
    private function writeLog($info, $type = "info")
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/affiliate_Locked.log');
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
