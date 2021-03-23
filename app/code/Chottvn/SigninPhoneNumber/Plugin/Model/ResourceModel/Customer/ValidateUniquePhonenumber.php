<?php
/**
 * A Magento 2 module named Chottvn/SigninPhoneNumber
 * Copyright (C) 2020 Chottvn
 *
 * This file included in Chottvn/SigninWithPhoneNumber is licensed under OSL 3.0
 *
 * http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * Please see LICENSE.txt for the full text of the OSL 3.0 license
 */

namespace Chottvn\SigninPhoneNumber\Plugin\Model\ResourceModel\Customer;

use Magento\Customer\Model\Customer;
use Magento\Customer\Model\ResourceModel\Customer as ResourceModel;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;
use Magento\Framework\Exception\LocalizedException;
use Chottvn\SigninPhoneNumber\Helper\Data as Config;
use Chottvn\SigninPhoneNumber\Setup\InstallData;
use Chottvn\SigninPhoneNumber\Api\PhoneVerificationRepositoryInterface as PhoneVerificationRepository;

/**
 * Class ValidateUniquePhonenumber
 * Validates if the customer's phone number is unique
 */
class ValidateUniquePhonenumber
{

    /**
     * @var CustomerCollectionFactory
     */
    private $customerCollectionFactory;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var PhoneVerificationRepository
     */
    protected $phoneVerificationRepository;

    /**
     * @param CustomerCollectionFactory $customerCollectionFactory
     * @param Config $config
     */
    public function __construct(
        CustomerCollectionFactory $customerCollectionFactory,
        Config $config,
        PhoneVerificationRepository $phoneVerificationRepository
    ) {
        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->config = $config;
        $this->phoneVerificationRepository = $phoneVerificationRepository;
    }

    /**
     * Validates if the customer phone number is unique
     *
     * @param ResourceModel $subject
     * @param Customer $customer
     *
     * @throws LocalizedException
     */
    public function beforeSave(ResourceModel $subject, Customer $customer)
    {
        if (!$this->config->isActive()) {
            return;
        }

        $collection = $this->customerCollectionFactory
            ->create()
            ->addAttributeToFilter(InstallData::PHONE_NUMBER, $customer->getData(InstallData::PHONE_NUMBER));

        // If the customer already exists, exclude them from the query
        if ($customer->getId()) {
            $collection->addAttribuTeToFilter(
                'entity_id',
                [
                    'neq' => (int) $customer->getId(),
                ]
            );
        }

        if ($collection->getSize() > 0) {
            $this->writeLog("func:beforeSave - Size of collection customer: ".$collection->getSize());
            // If have activated phone number then return phone already existed
                $activatedCustomerId = $this->phoneVerificationRepository->getValidatedCustomerId($customer->getData(InstallData::PHONE_NUMBER));
                $this->writeLog("func:beforeSave - activated customer id of phone ".$customer->getData(InstallData::PHONE_NUMBER)." is ".$activatedCustomerId);
            if(!empty($activatedCustomerId)){
                $this->writeLog("func:beforeSave - activated");
                $this->writeLog("func:beforeSave - current customer id ".$customer->getId());
                if($activatedCustomerId != $customer->getId()){
                    throw new LocalizedException(
                        __('A customer with the same phone number already exists in an associated website.')
                    );
                }
            }
        }
    }

	/**
	 * @param $info
	 * @param $type  [error, warning, info]
	 * @return 
	 */
	private function writeLog($info, $type = "info")
	{
		$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/validate_unique_phone.log');
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
