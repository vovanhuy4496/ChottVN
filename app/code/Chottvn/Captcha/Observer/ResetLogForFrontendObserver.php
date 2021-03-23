<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Chottvn\Captcha\Observer;

use Magento\Framework\Event\ObserverInterface;

class ResetLogForFrontendObserver implements ObserverInterface
{
    /**
     * @var \Magento\Captcha\Model\ResourceModel\LogFactory
     */
    public $resLogFactory;

    /**
     * @param \Magento\Captcha\Model\ResourceModel\LogFactory $resLogFactory
     */
    public function __construct(
        \Magento\Captcha\Model\ResourceModel\LogFactory $resLogFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
    ) {
        $this->resLogFactory = $resLogFactory;
        $this->customerRepository = $customerRepository;
    }

    /**
     * Reset Attempts For Frontend
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Magento\Captcha\Observer\ResetAttemptForFrontendObserver
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            /** @var \Magento\Customer\Model\Customer $model */
            $model = $observer->getModel();
            $customer = $this->customerRepository->getById($model->getId());
            if ($phoneNumberAttr = $customer->getCustomAttribute('phone_number')) {
                $chottCustomerPhoneNumber = $phoneNumberAttr->getValue();
                return $this->resLogFactory->create()->deleteUserAttempts($chottCustomerPhoneNumber);
            }
        } catch (\Exception $e) {
            $this->writeLog($e->getMessage());
        }

        return $this->resLogFactory->create()->deleteUserAttempts(null);
    }

    /**
     * @param $info
     * @param $type  [error, warning, info]
     * @return 
     */
    private function writeLog($info, $type = "info")
    {
        $info = 'Chottvn\Captcha\Observer\ResetLogForFrontendObserver - '.$info;
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/debug.log');
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
