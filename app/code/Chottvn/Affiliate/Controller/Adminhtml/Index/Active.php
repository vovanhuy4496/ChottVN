<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Chottvn\Affiliate\Controller\Adminhtml\Index;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Stdlib\DateTime;
use Chottvn\Affiliate\Model\Log as AffiliateLog;

/**
 * Active Affiliate action.
 */
class Active extends \Chottvn\Affiliate\Controller\Adminhtml\Index implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Chottvn_Affiliate::manage';

    /**
     * Active affiliate action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $customerId = $this->initCurrentCustomer();
        
        $this->writeLog("Chottvn\Affiliate\Controller\Adminhtml\Index\Active - Begin execute func");
        $resultRedirect = $this->resultRedirectFactory->create();
        $formKeyIsValid = $this->_formKeyValidator->validate($this->getRequest());
        $isPost = $this->getRequest()->isPost();
        if (!$formKeyIsValid || !$isPost) {
            $this->messageManager->addError(__('Affliate could not be active.'));
            return $resultRedirect->setPath(
                '*/*/edit',
                ['id' => $customerId, '_current' => true]
            );
        }

        if (!empty($customerId)) {
            try {
                $customer = $this->_customerRepository->getById($customerId);

                if ($customer) {
                    if ($customer->getCustomAttribute('affiliate_status')->getValue() == "activated") {
                        $this->messageManager->addError(__("Your affiliate you are activating was activated before"));
                    } else {
                        // Generate Affiliate Code if not exists
                        $affiliateCode = $customer->getCustomAttribute('affiliate_code');
                        if (!$affiliateCode) {
                            $affiliateCode = $this->generateAffiliateCode();
                        } else {
                            $affiliateCode = $affiliateCode->getValue();
                        }

                        // Update approve status
                        $customer->setCustomAttribute("affiliate_status", "activated");
                        $customer->setCustomAttribute("affiliate_level", "ctv");
                        $customer->setGroupId('4');
                        $customer->setCustomAttribute("affiliate_code", $affiliateCode);

                        // Check need to reset password, if true then generate password, if false keep current password 
                        $customerSecure = $this->_customerRegistry->retrieveSecureData($customer->getId());
                        $hash = $customerSecure->getPasswordHash();
                        $password = __('Password you are using for customer account');
                        if (is_null($hash)) {
                            $password = $this->createPassword($customer);
                        }

                        $this->_customerRepository->save($customer);
                        // Save log
                        $this->_helperAffiliateLog->saveLog(["account_id" => $customerId, "event" => AffiliateLog::EVENT_ACTIVATED]);

                        // Send email, maybe not have email, need to verify email
                        $data = [
                            "toEmail" => $customer->getCustomAttribute('customer_email')->getValue(),
                            "affiliateCode" => $affiliateCode,
                            "username" => $customer->getCustomAttribute('phone_number')->getValue(),
                            "password" => $password,
                            "template" => "chottvn_affiliate_active_affiliate_be_template"
                        ];
                        $this->writeLog("Chottvn\Affiliate\Controller\Adminhtml\Index\Active::execute - email data: " . json_encode($data));
                        $this->_affiliateHelper->sendEmail($data);

                        $this->messageManager->addSuccess(__('You activated the affiliate.'));

                        // Update email
                        $this->updateEmail($customer);
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

    public function updateEmail($customer){
        try {
            if(is_null($customer->getEmail())) {
                $customer->setEmail($customer->getCustomAttribute('customer_email')->getValue());
            }
            $this->_customerRepository->save($customer);
        } catch (\Exception $exception) {
            $this->writeLog($exception->getMessage());
        }
    }

    public function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public function createPassword($customer)
    {
        $newPassword = $this->generateRandomString(10);
        //Update secure data
        $customerSecure = $this->_customerRegistry->retrieveSecureData($customer->getId());
        $customerSecure->setPasswordHash($this->encryptor->getHash($newPassword, true));
        return $newPassword;
    }

    public function setIgnoreValidationFlag($customer)
    {
        $customer->setData('ignore_validation_flag', true);
    }

    /**
     * Generate Affiliate Code
     */
    public function generateAffiliateCode()
    {
        // Generate code form 1 to 999 random
        $codeInt = mt_rand(1, 999);
        $affiliateCode = "CTV" . sprintf("%03d", $codeInt);
        // Check code existed ? If true then plus 1
        $this->_connection = $this->_resourceConnection->getConnection();

        $query = "SELECT * FROM `customer_entity_varchar`
        WHERE value = '" . $affiliateCode . "'
        AND attribute_id = (
            SELECT
                attribute_id
            FROM
                `eav_attribute`
            WHERE
                attribute_code = 'affiliate_code'
        );";

        $queryCollection = $this->_connection->fetchAll($query);
        if (count($queryCollection) >= 1) {
            do {
                $codeInt++;
                $affiliateCode = "CTV" . sprintf("%03d", $codeInt);

                $query = "SELECT * FROM `customer_entity_varchar`
                WHERE value = '" . $affiliateCode . "'
                AND attribute_id = (
                    SELECT
                        attribute_id
                    FROM
                        `eav_attribute`
                    WHERE
                        attribute_code = 'affiliate_code'
                );";

                $queryCollection = $this->_connection->fetchAll($query);
            } while (count($queryCollection) >= 1);
        }

        // Return generated code
        return $affiliateCode;
    }

    /**
     * @param $info
     * @param $type  [error, warning, info]
     * @return 
     */
    private function writeLog($info, $type = "info")
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/be_affiliate_active.log');
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
