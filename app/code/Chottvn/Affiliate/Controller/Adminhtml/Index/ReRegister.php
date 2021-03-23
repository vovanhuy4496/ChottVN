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
class ReRegister extends \Chottvn\Affiliate\Controller\Adminhtml\Index implements HttpPostActionInterface
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

        $this->writeLog("Chottvn\Affiliate\Controller\Adminhtml\Index\ReRegister - Begin execute func");
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
                    if (
                        $customer->getCustomAttribute('affiliate_status')->getValue() == "approved" ||
                        $customer->getCustomAttribute('affiliate_status')->getValue() == "activated"
                    ) {
                        $this->messageManager->addError(__("Your affiliate you are request to re-register was " . $customer->getCustomAttribute('affiliate_status')->getValue() . " before"));
                    } else {
                        $customer = $this->_customerRepository->getById($customerId);

                        // Prepare email data
                        $data = [
                            "fullName" => $customer->getFirstname(),
                            "toEmail" => $customer->getCustomAttribute('customer_email')->getValue(),
                            "registerLink" => $this->createReRegisterLink(),
                            "homeLink" => $this->_storeManager->getStore()->getBaseUrl()
                        ];

                        if ($customer->getGroupId() == '4') {
                            // Update phone verification status
                            $phoneVerificationModel = $this->getPhoneVerificationModel($customerId, $customer->getCustomAttribute('phone_number')->getValue());
                            if ($phoneVerificationModel->getId()) {
                                $phoneVerificationModel->setData('verify_status', 0);
                                $phoneVerificationModel->save();
                            }
                        }

                        // Update affiliate status, email, affiliate code
                        $customer->setCustomAttribute("affiliate_status", "re-register");
                        // $customer->setCustomAttribute("affiliate_code", ""); // Set affiliate code to null
                        $customer->setCustomAttribute("customer_email", ""); // Set customer email to null
                        $this->_customerRepository->save($customer);

                        // Send Email
                        $this->_affiliateHelper->sendReRegisterEmail($data);

                        // Save log
                        $this->_helperAffiliateLog->saveLog(["account_id" => $customerId, "event" => AffiliateLog::EVENT_REREGISTER]);
                        $this->messageManager->addSuccess(__('You requested the affiliate to re-register.'));
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

    private function createReRegisterLink()
    {
        $this->_frontendUrlBuilder->setScope($this->_storeManager->getDefaultStoreView()->getStoreId());
        return $this->_frontendUrlBuilder->getUrl('affiliate/register/index/', [
            '_current' => false,
            '_nosid' => true
        ]);
    }

    private function getPhoneVerificationModel($customerId, $phoneNumber)
    {
        // Find record customer_id, phone_number from table
        $phoneVerification = $this->_phoneVerificationFactory->create();
        $collection = $phoneVerification->getCollection()
            ->addFieldToFilter('customer_id', $customerId)
            ->addFieldToFilter('phone_number', $phoneNumber)
            ->setOrder('customer_id', 'ASC');
        return $collection->getLastItem();
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
