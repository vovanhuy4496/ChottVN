<?php
/**
 * Copyright © (c) Cho Truc Tuyen 2020 All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Chottvn\PaymentAccount\Model;

use Chottvn\PaymentAccount\Api\BankRepositoryInterface;
use Chottvn\PaymentAccount\Api\Data\BankInterfaceFactory;
use Chottvn\PaymentAccount\Api\Data\BankSearchResultsInterfaceFactory;
use Chottvn\PaymentAccount\Model\ResourceModel\Bank as ResourceBank;
use Chottvn\PaymentAccount\Model\ResourceModel\Bank\CollectionFactory as BankCollectionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Store\Model\StoreManagerInterface;

class BankRepository implements BankRepositoryInterface
{

    protected $resource;

    protected $bankFactory;

    protected $dataObjectHelper;

    protected $extensibleDataObjectConverter;
    protected $bankCollectionFactory;

    private $storeManager;

    protected $dataObjectProcessor;

    protected $dataBankFactory;

    protected $searchResultsFactory;

    private $collectionProcessor;

    protected $extensionAttributesJoinProcessor;


    /**
     * @param ResourceBank $resource
     * @param BankFactory $bankFactory
     * @param BankInterfaceFactory $dataBankFactory
     * @param BankCollectionFactory $bankCollectionFactory
     * @param BankSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     * @param CollectionProcessorInterface $collectionProcessor
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     */
    public function __construct(
        ResourceBank $resource,
        BankFactory $bankFactory,
        BankInterfaceFactory $dataBankFactory,
        BankCollectionFactory $bankCollectionFactory,
        BankSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        CollectionProcessorInterface $collectionProcessor,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->resource = $resource;
        $this->bankFactory = $bankFactory;
        $this->bankCollectionFactory = $bankCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataBankFactory = $dataBankFactory;
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
        \Chottvn\PaymentAccount\Api\Data\BankInterface $bank
    ) {
        /* if (empty($bank->getStoreId())) {
            $storeId = $this->storeManager->getStore()->getId();
            $bank->setStoreId($storeId);
        } */
        
        $bankData = $this->extensibleDataObjectConverter->toNestedArray(
            $bank,
            [],
            \Chottvn\PaymentAccount\Api\Data\BankInterface::class
        );
        
        $bankModel = $this->bankFactory->create()->setData($bankData);
        
        try {
            $this->resource->save($bankModel);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the bank: %1',
                $exception->getMessage()
            ));
        }
        return $bankModel->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function get($bankId)
    {
        $bank = $this->bankFactory->create();
        $this->resource->load($bank, $bankId);
        if (!$bank->getId()) {
            throw new NoSuchEntityException(__('Bank with id "%1" does not exist.', $bankId));
        }
        return $bank->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->bankCollectionFactory->create();
        
        $this->extensionAttributesJoinProcessor->process(
            $collection,
            \Chottvn\PaymentAccount\Api\Data\BankInterface::class
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
        \Chottvn\PaymentAccount\Api\Data\BankInterface $bank
    ) {
        try {
            $bankModel = $this->bankFactory->create();
            $this->resource->load($bankModel, $bank->getBankId());
            $this->resource->delete($bankModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the Bank: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($bankId)
    {
        return $this->delete($this->get($bankId));
    }
}

