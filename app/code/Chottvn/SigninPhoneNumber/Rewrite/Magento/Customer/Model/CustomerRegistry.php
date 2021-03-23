<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Chottvn\SigninPhoneNumber\Rewrite\Magento\Customer\Model;

use Magento\Customer\Model\Data\CustomerSecure;
use Magento\Customer\Model\Data\CustomerSecureFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Model\CustomerFactory;

/**
 * Registry for \Magento\Customer\Model\Customer
 */
class CustomerRegistry extends \Magento\Customer\Model\CustomerRegistry
{

    /**
     * @var array
     */
    private $customerRegistryByPhoneNumber = [];

    /**
     * Constructor
     *
     * @param CustomerFactory $customerFactory
     * @param CustomerSecureFactory $customerSecureFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        CustomerFactory $customerFactory,
        CustomerSecureFactory $customerSecureFactory,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($customerFactory,
            $customerSecureFactory,
            $storeManager);
        $this->customerFactory = $customerFactory;
        $this->customerSecureFactory = $customerSecureFactory;
        $this->storeManager = $storeManager;
    }
    
    /**
     * Retrieve Customer Model from registry given an email
     *
     * @param string $customerEmail Customers email address
     * @param string|null $websiteId Optional website ID, if not set, will use the current websiteId
     * @return Customer
     * @throws NoSuchEntityException
     */
    public function retrieveByEmail($customerEmail, $websiteId = null)
    {
        if ($websiteId === null) {
            $websiteId = $this->storeManager->getStore()->getWebsiteId();
        }
        $emailKey = $this->getEmailKey($customerEmail, $websiteId);
        if (isset($this->customerRegistryByEmail[$emailKey])) {
            return $this->customerRegistryByEmail[$emailKey];
        }

        /** @var Customer $customer */
        $customer = $this->customerFactory->create();

        if (isset($websiteId)) {
            $customer->setWebsiteId($websiteId);
        }

        $customer->loadByEmail($customerEmail);
        if (!$customer->getEmail()) {
            // customer does not exist            
            throw new NoSuchEntityException(
                __(
                    'No such entity with %fieldName = %fieldValue, %field2Name = %field2Value',
                    [
                        'fieldName' => 'email',
                        'fieldValue' => $customerEmail,
                        'field2Name' => 'websiteId',
                        'field2Value' => $websiteId
                    ]
                )
            );                        
        } else {
            $this->customerRegistryById[$customer->getId()] = $customer;
            $this->customerRegistryByEmail[$emailKey] = $customer;
            return $customer;
        }
    }

    /**
     * Retrieve Customer Model from registry given an email
     *
     * @param string $customerPhoneNumber Customers Phone number
     * @param string|null $websiteId Optional website ID, if not set, will use the current websiteId
     * @return Customer
     * @throws NoSuchEntityException
     */
    public function retrieveByPhoneNumber($customerPhoneNumber, $websiteId = null)
    {
        if ($websiteId === null) {
            $websiteId = $this->storeManager->getStore()->getWebsiteId();
        }
        $phoneNumberKey = $this->getPhoneNumberKey($customerPhoneNumber, $websiteId);
        if (isset($this->customerRegistryByPhoneNumber[$phoneNumberKey])) {
            return $this->customerRegistryByPhoneNumber[$phoneNumberKey];
        }

        /** @var Customer $customer */
        $customer = $this->customerFactory->create();

        if (isset($websiteId)) {
            $customer->setWebsiteId($websiteId);
        }

        //$customer->loadByPhoneNumber($customerPhoneNumber);
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customerObj = $objectManager->create('Magento\Customer\Model\ResourceModel\Customer\Collection');
        $collection = $customerObj->addAttributeToSelect('*')
                      ->addAttributeToFilter('phone_number',$customerPhoneNumber)
                      ->load();

        // $customer=$collection->getFirstItem(); //>getData()
        $customer=$collection->getLastItem(); //>getData()

        
        if (!$customer->getPhoneNumber()) {
            // customer does not exist            
            throw new NoSuchEntityException(
                __(
                    'No such entity with %fieldName = %fieldValue, %field2Name = %field2Value',
                    [
                        'fieldName' => 'phone_number',
                        'fieldValue' => $customerPhoneNumber,
                        'field2Name' => 'websiteId',
                        'field2Value' => $websiteId
                    ]
                )
            );                        
        } else {
            $this->customerRegistryById[$customer->getId()] = $customer;
            $this->customerRegistryByPhoneNumber[$phoneNumberKey] = $customer;
            return $customer;
        }
    }

    /**
     * Create registry key
     *
     * @param string $customerPhoneNumber
     * @param string $websiteId
     * @return string
     */
    protected function getPhoneNumberKey($customerPhoneNumber, $websiteId)
    {
        return $customerPhoneNumber . self::REGISTRY_SEPARATOR . $websiteId;
    }

    /**
     * Retrieve Customer Model from registry given an id
     *
     * @param string $customerId
     * @return Customer
     * @throws NoSuchEntityException
     */
    public function retrieve($customerId)
    {
        // Đoạn này kiểm tra data, nếu có rồi sẽ lấy ra luôn
        // phải xoá đi để request customer mới
        // if (isset($this->customerRegistryById[$customerId])) {
        //     return $this->customerRegistryById[$customerId];
        // }
        /** @var Customer $customer */
        $customer = $this->customerFactory->create()->load($customerId);
        if (!$customer->getId()) {
            // customer does not exist
            throw NoSuchEntityException::singleField('customerId', $customerId);
        } else {
            $emailKey = $this->getEmailKey($customer->getEmail(), $customer->getWebsiteId());
            $this->customerRegistryById[$customerId] = $customer;
            $this->customerRegistryByEmail[$emailKey] = $customer;
            return $customer;
        }
    }
}
