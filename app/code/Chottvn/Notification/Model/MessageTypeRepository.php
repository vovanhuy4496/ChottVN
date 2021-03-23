<?php
declare(strict_types=1);

namespace Chottvn\Notification\Model;

use Chottvn\Notification\Api\Data\MessageTypeInterfaceFactory;
use Chottvn\Notification\Api\Data\MessageTypeSearchResultsInterfaceFactory;
use Chottvn\Notification\Api\MessageTypeRepositoryInterface;
use Chottvn\Notification\Model\ResourceModel\MessageType as ResourceMessageType;
use Chottvn\Notification\Model\ResourceModel\MessageType\CollectionFactory as MessageTypeCollectionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Store\Model\StoreManagerInterface;

class MessageTypeRepository implements MessageTypeRepositoryInterface
{

    protected $searchResultsFactory;

    private $collectionProcessor;

    protected $resource;

    protected $extensibleDataObjectConverter;

    protected $messageTypeFactory;

    protected $dataObjectHelper;

    protected $dataObjectProcessor;

    protected $messageTypeCollectionFactory;

    private $storeManager;

    protected $extensionAttributesJoinProcessor;

    protected $dataMessageTypeFactory;

    /**
     * @param ResourceMessageType $resource
     * @param MessageTypeFactory $messageTypeFactory
     * @param MessageTypeInterfaceFactory $dataMessageTypeFactory
     * @param MessageTypeCollectionFactory $messageTypeCollectionFactory
     * @param MessageTypeSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     * @param CollectionProcessorInterface $collectionProcessor
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     */
    public function __construct(
        ResourceMessageType $resource,
        MessageTypeFactory $messageTypeFactory,
        MessageTypeInterfaceFactory $dataMessageTypeFactory,
        MessageTypeCollectionFactory $messageTypeCollectionFactory,
        MessageTypeSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        CollectionProcessorInterface $collectionProcessor,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->resource = $resource;
        $this->messagetypeFactory = $messageTypeFactory;
        $this->messagetypeCollectionFactory = $messageTypeCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataMessageTypeFactory = $dataMessageTypeFactory;
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
        \Chottvn\Notification\Api\Data\MessageTypeInterface $messageType
    ) {
        /* if (empty($messageType->getStoreId())) {
            $storeId = $this->storeManager->getStore()->getId();
            $messageType->setStoreId($storeId);
        } */
        
        $messageTypeData = $this->extensibleDataObjectConverter->toNestedArray(
            $messageType,
            [],
            \Chottvn\Notification\Api\Data\MessageTypeInterface::class
        );
        
        $messageTypeModel = $this->messagetypeFactory->create()->setData($messageTypeData);
        
        try {
            $this->resource->save($messageTypeModel);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the MessageType: %1',
                $exception->getMessage()
            ));
        }
        return $messageTypeModel->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function get($messageTypeId)
    {
        $messageType = $this->messagetypeFactory->create();
        $this->resource->load($messageType, $messageTypeId);
        if (!$messageType->getId()) {
            throw new NoSuchEntityException(__('MessageType with id "%1" does not exist.', $messageTypeId));
        }
        return $messageType->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->messagetypeCollectionFactory->create();
        
        $this->extensionAttributesJoinProcessor->process(
            $collection,
            \Chottvn\Notification\Api\Data\MessageTypeInterface::class
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
        \Chottvn\Notification\Api\Data\MessageTypeInterface $messageType
    ) {
        try {
            $messageTypeModel = $this->messagetypeFactory->create();
            $this->resource->load($messageTypeModel, $messageType->getMessagetypeId());
            $this->resource->delete($messageTypeModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the MessageType: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($messageTypeId)
    {
        return $this->delete($this->get($messageTypeId));
    }
}

