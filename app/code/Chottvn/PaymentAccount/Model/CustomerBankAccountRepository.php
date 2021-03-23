<?php
/**
 * Copyright © (c) Cho Truc Tuyen 2020 All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Chottvn\PaymentAccount\Model;

use Chottvn\PaymentAccount\Api\CustomerBankAccountRepositoryInterface;
use Chottvn\PaymentAccount\Api\Data\CustomerBankAccountInterfaceFactory;
use Chottvn\PaymentAccount\Api\Data\CustomerBankAccountSearchResultsInterfaceFactory;
use Chottvn\PaymentAccount\Model\ResourceModel\CustomerBankAccount as ResourceCustomerBankAccount;
use Chottvn\PaymentAccount\Model\ResourceModel\CustomerBankAccount\CollectionFactory as CustomerBankAccountCollectionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Store\Model\StoreManagerInterface;

class CustomerBankAccountRepository implements CustomerBankAccountRepositoryInterface
{

    protected $resource;

    protected $customerBankAccountCollectionFactory;

    protected $dataObjectHelper;

    protected $extensibleDataObjectConverter;
    private $storeManager;

    protected $dataObjectProcessor;

    protected $searchResultsFactory;

    protected $customerBankAccountFactory;

    private $collectionProcessor;

    protected $dataCustomerBankAccountFactory;

    protected $extensionAttributesJoinProcessor;


    /**
     * @param ResourceCustomerBankAccount $resource
     * @param CustomerBankAccountFactory $customerBankAccountFactory
     * @param CustomerBankAccountInterfaceFactory $dataCustomerBankAccountFactory
     * @param CustomerBankAccountCollectionFactory $customerBankAccountCollectionFactory
     * @param CustomerBankAccountSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     * @param CollectionProcessorInterface $collectionProcessor
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     */
    public function __construct(
        ResourceCustomerBankAccount $resource,
        CustomerBankAccountFactory $customerBankAccountFactory,
        CustomerBankAccountInterfaceFactory $dataCustomerBankAccountFactory,
        CustomerBankAccountCollectionFactory $customerBankAccountCollectionFactory,
        CustomerBankAccountSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        CollectionProcessorInterface $collectionProcessor,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->resource = $resource;
        $this->customerBankAccountFactory = $customerBankAccountFactory;
        $this->customerBankAccountCollectionFactory = $customerBankAccountCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataCustomerBankAccountFactory = $dataCustomerBankAccountFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
        $this->collectionProcessor = $collectionProcessor;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function save(
        \Chottvn\PaymentAccount\Api\Data\CustomerBankAccountInterface $customerBankAccount
    ) {
        /* if (empty($customerBankAccount->getStoreId())) {
            $storeId = $this->storeManager->getStore()->getId();
            $customerBankAccount->setStoreId($storeId);
        } */
        
        $customerBankAccountData = $this->extensibleDataObjectConverter->toNestedArray(
            $customerBankAccount,
            [],
            \Chottvn\PaymentAccount\Api\Data\CustomerBankAccountInterface::class
        );
        
        $customerBankAccountModel = $this->customerBankAccountFactory->create()->setData($customerBankAccountData);
        
        try {
            $this->resource->save($customerBankAccountModel);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the customerBankAccount: %1',
                $exception->getMessage()
            ));
        }
        return $customerBankAccountModel->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function get($customerBankAccountId)
    {
        $customerBankAccount = $this->customerBankAccountFactory->create();
        $this->resource->load($customerBankAccount, $customerBankAccountId);
        if (!$customerBankAccount->getId()) {
            throw new NoSuchEntityException(__('CustomerBankAccount with id "%1" does not exist.', $customerBankAccountId));
        }
        return $customerBankAccount->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->customerBankAccountCollectionFactory->create();
        
        $this->extensionAttributesJoinProcessor->process(
            $collection,
            \Chottvn\PaymentAccount\Api\Data\CustomerBankAccountInterface::class
        );
        
        $this->collectionProcessor->process($criteria, $collection);
        
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        
        $items = [];
        foreach ($collection as $model) {
            $items[] = $model->getDataModel();
        }
        
        $searchResults->setItems($items);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(
        \Chottvn\PaymentAccount\Api\Data\CustomerBankAccountInterface $customerBankAccount
    ) {
        try {
            $customerBankAccountModel = $this->customerBankAccountFactory->create();
            $this->resource->load($customerBankAccountModel, $customerBankAccount->getCustomerbankaccountId());
            $this->resource->delete($customerBankAccountModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the CustomerBankAccount: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($customerBankAccountId)
    {
        return $this->delete($this->get($customerBankAccountId));
    }
}

