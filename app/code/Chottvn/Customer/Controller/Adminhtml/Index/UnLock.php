<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Chottvn\Customer\Controller\Adminhtml\Index;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;

/**
 * Unlock Customer action.
 */
class UnLock extends \Chottvn\Affiliate\Controller\Adminhtml\Index implements HttpPostActionInterface
{
    /**
     * Unlock Customer action.
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $customerId = $this->initCurrentCustomer();
        
        $resultRedirect = $this->resultRedirectFactory->create();
        $formKeyIsValid = $this->_formKeyValidator->validate($this->getRequest());
        $isPost = $this->getRequest()->isPost();
        $_data = $this->getRequest()->getPostValue();

        if (!$formKeyIsValid || !$isPost) {
            $this->messageManager->addError(__('Bad Request'));
            return $resultRedirect->setPath(
                $_data['page_action'].'/*/edit',
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
                        "toEmail" => $_data['page_action'] == 'affiliate' ? $customer->getCustomAttribute('customer_email')->getValue() : $customer->getEmail(),
                        "username" => $customer->getCustomAttribute('phone_number')->getValue(),
                    ];
                    // $this->writeLog($data);

                    if ($_data['page_action'] == 'affiliate') {
                        $customerFactory = $this->_customerFactory->create()->load($customerId);

                        $customerData = $customerFactory->getDataModel();
                        $customerData->setCustomAttribute('is_disabled', '0');
                        $customerFactory->updateData($customerData);
    
                        $customerFactory->save();
                        // $this->writeLog('pass saved affiliate');
                    } else {
                        $customer->setCustomAttribute("is_disabled", '0');
                        $this->_customerRepository->save($customer);
                        // $this->writeLog('pass saved customer');
                    }

                    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                    $checkSendMail = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('email_affiliate/locked_unlocked_customer/enabled');
                    // Send Email
                    if ($checkSendMail == 1) {
                        $this->_affiliateHelper->sendUnLockedEmail($data);
                    }

                    $this->messageManager->addSuccess(__('You unlocked the customer.'));
                } else {
                    $this->messageManager->addError(__("Your customer you are requesting is not exists"));
                }
            } catch (\Exception $exception) {
                $this->messageManager->addError($exception->getMessage());
            }
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath(
            $_data['page_action'].'/*/edit',
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
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/customer_unlock.log');
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
