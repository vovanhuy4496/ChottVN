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
class RequestIdentityCard extends \Chottvn\Affiliate\Controller\Adminhtml\Index implements HttpPostActionInterface
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
        
        $this->writeLog("Chottvn\Affiliate\Controller\Adminhtml\Index\RequestIdentityCard - Begin execute func");
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
                        // Prepare email data
                        $data = [
                            "fullName" => $customer->getFirstname(),
                            "toEmail" => $customer->getCustomAttribute('customer_email')->getValue(),
                            "homeLink" => $this->_storeManager->getStore()->getBaseUrl()
                        ];

                        // Send Email
                        $this->_affiliateHelper->sendRequestIdentityCardEmail($data);

                        // Save log
                        $this->_helperAffiliateLog->saveLog(["account_id" => $customerId, "event" => AffiliateLog::EVENT_REQUEST_IDENTITY_CARD]);
                        $this->messageManager->addSuccess(__('You sent email request identity card successfull.'));
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
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/affiliate_verify.log');
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
