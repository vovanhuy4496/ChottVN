<?php
/**
 * Copyright © (c) chotructuyen.vn All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Chottvn\PriceQuote\Model;

use Chottvn\PriceQuote\Api\Data\RequestInterfaceFactory;
use Chottvn\PriceQuote\Api\Data\RequestSearchResultsInterfaceFactory;
use Chottvn\PriceQuote\Api\RequestRepositoryInterface;
use Chottvn\PriceQuote\Model\ResourceModel\Request as ResourceRequest;
use Chottvn\PriceQuote\Model\ResourceModel\Request\CollectionFactory as RequestCollectionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Store\Model\StoreManagerInterface;

class RequestRepository implements RequestRepositoryInterface
{

    protected $extensibleDataObjectConverter;
    protected $dataRequestFactory;

    protected $requestFactory;

    protected $dataObjectHelper;

    protected $resource;

    private $storeManager;

    protected $searchResultsFactory;

    protected $dataObjectProcessor;

    protected $extensionAttributesJoinProcessor;

    protected $requestCollectionFactory;

    private $collectionProcessor;


    /**
     * @param ResourceRequest $resource
     * @param RequestFactory $requestFactory
     * @param RequestInterfaceFactory $dataRequestFactory
     * @param RequestCollectionFactory $requestCollectionFactory
     * @param RequestSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     * @param CollectionProcessorInterface $collectionProcessor
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     */
    public function __construct(
        ResourceRequest $resource,
        RequestFactory $requestFactory,
        RequestInterfaceFactory $dataRequestFactory,
        RequestCollectionFactory $requestCollectionFactory,
        RequestSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        CollectionProcessorInterface $collectionProcessor,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->resource = $resource;
        $this->requestFactory = $requestFactory;
        $this->requestCollectionFactory = $requestCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataRequestFactory = $dataRequestFactory;
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
        \Chottvn\PriceQuote\Api\Data\RequestInterface $request
    ) {
        /* if (empty($request->getStoreId())) {
            $storeId = $this->storeManager->getStore()->getId();
            $request->setStoreId($storeId);
        } */
        
        $requestData = $this->extensibleDataObjectConverter->toNestedArray(
            $request,
            [],
            \Chottvn\PriceQuote\Api\Data\RequestInterface::class
        );
        
        $requestModel = $this->requestFactory->create()->setData($requestData);
        
        try {
            $this->resource->save($requestModel);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the request: %1',
                $exception->getMessage()
            ));
        }
        return $requestModel->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function get($requestId)
    {
        $request = $this->requestFactory->create();
        $this->resource->load($request, $requestId);
        if (!$request->getId()) {
            throw new NoSuchEntityException(__('Request with id "%1" does not exist.', $requestId));
        }
        return $request->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->requestCollectionFactory->create();
        
        $this->extensionAttributesJoinProcessor->process(
            $collection,
            \Chottvn\PriceQuote\Api\Data\RequestInterface::class
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
        \Chottvn\PriceQuote\Api\Data\RequestInterface $request
    ) {
        try {
            $requestModel = $this->requestFactory->create();
            $this->resource->load($requestModel, $request->getRequestId());
            $this->resource->delete($requestModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the Request: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($requestId)
    {
        return $this->delete($this->get($requestId));
    }
}

