<?php
declare(strict_types=1);

namespace Chottvn\Finance\Model;

use Chottvn\Finance\Api\Data\TransactionTypeInterfaceFactory;
use Chottvn\Finance\Api\Data\TransactionTypeSearchResultsInterfaceFactory;
use Chottvn\Finance\Api\TransactionTypeRepositoryInterface;
use Chottvn\Finance\Model\ResourceModel\TransactionType as ResourceTransactionType;
use Chottvn\Finance\Model\ResourceModel\TransactionType\CollectionFactory as TransactionTypeCollectionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Store\Model\StoreManagerInterface;

class TransactionTypeRepository implements TransactionTypeRepositoryInterface
{

    protected $searchResultsFactory;

    private $collectionProcessor;

    protected $resource;

    protected $extensibleDataObjectConverter;
    protected $transactionTypeFactory;

    protected $dataObjectHelper;

    protected $dataObjectProcessor;

    protected $transactionTypeCollectionFactory;

    private $storeManager;

    protected $extensionAttributesJoinProcessor;

    protected $dataTransactionTypeFactory;


    /**
     * @param ResourceTransactionType $resource
     * @param TransactionTypeFactory $transactionTypeFactory
     * @param TransactionTypeInterfaceFactory $dataTransactionTypeFactory
     * @param TransactionTypeCollectionFactory $transactionTypeCollectionFactory
     * @param TransactionTypeSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     * @param CollectionProcessorInterface $collectionProcessor
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     */
    public function __construct(
        ResourceTransactionType $resource,
        TransactionTypeFactory $transactionTypeFactory,
        TransactionTypeInterfaceFactory $dataTransactionTypeFactory,
        TransactionTypeCollectionFactory $transactionTypeCollectionFactory,
        TransactionTypeSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        CollectionProcessorInterface $collectionProcessor,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->resource = $resource;
        $this->transactionTypeFactory = $transactionTypeFactory;
        $this->transactionTypeCollectionFactory = $transactionTypeCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataTransactionTypeFactory = $dataTransactionTypeFactory;
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
        \Chottvn\Finance\Api\Data\TransactionTypeInterface $transactionType
    ) {
        /* if (empty($transactionType->getStoreId())) {
            $storeId = $this->storeManager->getStore()->getId();
            $transactionType->setStoreId($storeId);
        } */
        
        $transactionTypeData = $this->extensibleDataObjectConverter->toNestedArray(
            $transactionType,
            [],
            \Chottvn\Finance\Api\Data\TransactionTypeInterface::class
        );
        
        $transactionTypeModel = $this->transactionTypeFactory->create()->setData($transactionTypeData);
        
        try {
            $this->resource->save($transactionTypeModel);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the transactionType: %1',
                $exception->getMessage()
            ));
        }
        return $transactionTypeModel->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function get($transactionTypeId)
    {
        $transactionType = $this->transactionTypeFactory->create();
        $this->resource->load($transactionType, $transactionTypeId);
        if (!$transactionType->getId()) {
            throw new NoSuchEntityException(__('TransactionType with id "%1" does not exist.', $transactionTypeId));
        }
        return $transactionType->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->transactionTypeCollectionFactory->create();
        
        $this->extensionAttributesJoinProcessor->process(
            $collection,
            \Chottvn\Finance\Api\Data\TransactionTypeInterface::class
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
        \Chottvn\Finance\Api\Data\TransactionTypeInterface $transactionType
    ) {
        try {
            $transactionTypeModel = $this->transactionTypeFactory->create();
            $this->resource->load($transactionTypeModel, $transactionType->getTransactiontypeId());
            $this->resource->delete($transactionTypeModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the TransactionType: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($transactionTypeId)
    {
        return $this->delete($this->get($transactionTypeId));
    }
}

