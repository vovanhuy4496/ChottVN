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
 * Approve Affiliate action.
 */
class Approve extends \Chottvn\Affiliate\Controller\Adminhtml\Index implements HttpPostActionInterface
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
        
        $this->writeLog("Chottvn\Affiliate\Controller\Adminhtml\Index\Approve - Begin execute func");
        $resultRedirect = $this->resultRedirectFactory->create();
        $formKeyIsValid = $this->_formKeyValidator->validate($this->getRequest());
        $isPost = $this->getRequest()->isPost();
        if (!$formKeyIsValid || !$isPost) {
            $this->messageManager->addError(__('Affliate could not be approved.'));
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
                        $this->messageManager->addError(__("Your affiliate you are approving was approved before"));
                    } else {
                        // Generate Affiliate Code if not exists
                        $affiliateCode = $customer->getCustomAttribute('affiliate_code');
                        if (!$affiliateCode) {
                            $affiliateCode = $this->generateAffiliateCode();
                        }else{
                            $affiliateCode = $affiliateCode->getValue();
                        }

                        // Update approve status
                        $customer->setCustomAttribute("affiliate_status", "approved");
                        $customer->setCustomAttribute("affiliate_code", $affiliateCode);
                        $this->_customerRepository->save($customer);
                        // Save log
                        $this->_helperAffiliateLog->saveLog(["account_id" => $customerId, "event" => AffiliateLog::EVENT_APPROVED]);
                        $activeLink = $this->createActiveLink($customer);

                        // Send email, maybe not have email, need to verify email
                        $data = [
                            "fullName" => $customer->getFirstname(),
                            "toEmail" => $customer->getCustomAttribute('customer_email')->getValue(),
                            "affiliateCode" => $affiliateCode,
                            "username" => $customer->getCustomAttribute('phone_number')->getValue(),
                            "activeLink" => $activeLink,
                            "homeLink" => $this->_storeManager->getStore()->getBaseUrl(),
                            "linkExpiredPeriod" => $this->getLinkExpirationPeriod()
                        ];
                        
                        $customerSecure = $this->_customerRegistry->retrieveSecureData($customer->getId());
                        $hash = $customerSecure->getPasswordHash();
                        if (is_null($hash)) {
                            $this->_affiliateHelper->sendActiveEmail($data);
                        }else{
                            $this->_affiliateHelper->sendActiveEmailForCustomer($data);
                        }

                        $this->messageManager->addSuccess(__('You approved the affiliate.'));
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

    public function createActiveLink($customer)
    {
        $newLinkToken = $this->_mathRandom->getUniqueHash();
        if (is_string($newLinkToken) && !empty($newLinkToken)) {
            $customerSecure = $this->_customerRegistry->retrieveSecureData($customer->getId());
            $customerSecure->setRpToken($newLinkToken);
            $customerSecure->setRpTokenCreatedAt(
                $this->_dateTimeFactory->create()->format(DateTime::DATETIME_PHP_FORMAT)
            );
            $this->setIgnoreValidationFlag($customer);
            $this->_customerRepository->save($customer);
        }

        $this->_frontendUrlBuilder->setScope($this->_storeManager->getDefaultStoreView()->getStoreId());
        return $this->_frontendUrlBuilder->getUrl('affiliate/account/active/', [
            'token' => $newLinkToken,
            '_current' => false,
            '_nosid' => true
        ]);
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
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/affiliate_approve.log');
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
