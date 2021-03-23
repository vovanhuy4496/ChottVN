<?php
/**
 * Copyright (c) 2019 2020 ChottVN
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace Chottvn\OrderPayment\Model;

use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Chottvn\OrderPayment\Api\Data\BankAccountSearchResultsInterfaceFactory;
use Magento\Framework\Exception\CouldNotSaveException;
use Chottvn\OrderPayment\Api\Data\BankAccountInterfaceFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Exception\CouldNotDeleteException;
use Chottvn\OrderPayment\Api\BankAccountRepositoryInterface;
use Magento\Framework\Reflection\DataObjectProcessor;
use Chottvn\OrderPayment\Model\ResourceModel\BankAccount\CollectionFactory as BankAccountCollectionFactory;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Chottvn\OrderPayment\Model\ResourceModel\BankAccount as ResourceBankAccount;

/**
 * Class BankAccountRepository
 *
 * @package Chottvn\OrderPayment\Model
 */
class BankAccountRepository implements BankAccountRepositoryInterface
{

    protected $searchResultsFactory;

    protected $dataObjectHelper;

    protected $dataObjectProcessor;

    protected $dataBankAccountFactory;

    protected $bankAccountFactory;

    protected $extensionAttributesJoinProcessor;

    protected $bankAccountCollectionFactory;

    private $collectionProcessor;

    protected $resource;

    private $storeManager;

    protected $extensibleDataObjectConverter;

    /**
     * @param ResourceBankAccount $resource
     * @param BankAccountFactory $bankAccountFactory
     * @param BankAccountInterfaceFactory $dataBankAccountFactory
     * @param BankAccountCollectionFactory $bankAccountCollectionFactory
     * @param BankAccountSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     * @param CollectionProcessorInterface $collectionProcessor
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     */
    public function __construct(
        ResourceBankAccount $resource,
        BankAccountFactory $bankAccountFactory,
        BankAccountInterfaceFactory $dataBankAccountFactory,
        BankAccountCollectionFactory $bankAccountCollectionFactory,
        BankAccountSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        CollectionProcessorInterface $collectionProcessor,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->resource = $resource;
        $this->bankAccountFactory = $bankAccountFactory;
        $this->bankAccountCollectionFactory = $bankAccountCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataBankAccountFactory = $dataBankAccountFactory;
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
        \Chottvn\OrderPayment\Api\Data\BankAccountInterface $bankAccount
    ) {
        /* if (empty($bankAccount->getStoreId())) {
            $storeId = $this->storeManager->getStore()->getId();
            $bankAccount->setStoreId($storeId);
        } */
        
        $bankAccountData = $this->extensibleDataObjectConverter->toNestedArray(
            $bankAccount,
            [],
            \Chottvn\OrderPayment\Api\Data\BankAccountInterface::class
        );
        
        $bankAccountModel = $this->bankAccountFactory->create()->setData($bankAccountData);
        
        try {
            $this->resource->save($bankAccountModel);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the bankAccount: %1',
                $exception->getMessage()
            ));
        }
        return $bankAccountModel->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function get($bankAccountId)
    {
        $bankAccount = $this->bankAccountFactory->create();
        $this->resource->load($bankAccount, $bankAccountId);
        if (!$bankAccount->getId()) {
            throw new NoSuchEntityException(__('BankAccount with id "%1" does not exist.', $bankAccountId));
        }
        return $bankAccount->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->bankAccountCollectionFactory->create();
        
        $this->extensionAttributesJoinProcessor->process(
            $collection,
            \Chottvn\OrderPayment\Api\Data\BankAccountInterface::class
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
        \Chottvn\OrderPayment\Api\Data\BankAccountInterface $bankAccount
    ) {
        try {
            $bankAccountModel = $this->bankAccountFactory->create();
            $this->resource->load($bankAccountModel, $bankAccount->getBankaccountId());
            $this->resource->delete($bankAccountModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the BankAccount: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($bankAccountId)
    {
        return $this->delete($this->get($bankAccountId));
    }
}

