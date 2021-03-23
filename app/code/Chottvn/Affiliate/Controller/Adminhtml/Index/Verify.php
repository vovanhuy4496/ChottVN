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
class Verify extends \Chottvn\Affiliate\Controller\Adminhtml\Index implements HttpPostActionInterface
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
        
        $this->writeLog("Chottvn\Affiliate\Controller\Adminhtml\Index\Verify - Begin execute func");
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
                    if ($customer->getCustomAttribute('affiliate_status')->getValue() == "approved") {
                        $this->messageManager->addError(__("Your affiliate you are verifying was approved before"));
                    } elseif ($customer->getCustomAttribute('affiliate_status')->getValue() == "processing") {
                        $this->messageManager->addError(__("Your affiliate you are verifying was verified before"));
                    } else {
                        $customer = $this->_customerRepository->getById($customerId);
                        $customer->setCustomAttribute("affiliate_status", "processing");
                        $this->_customerRepository->save($customer);
                        // Save log
                        $this->_helperAffiliateLog->saveLog(["account_id" => $customerId, "event" => AffiliateLog::EVENT_PROCESSING]);
                        $this->messageManager->addSuccess(__('You verified the affiliate.'));
                    }
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
