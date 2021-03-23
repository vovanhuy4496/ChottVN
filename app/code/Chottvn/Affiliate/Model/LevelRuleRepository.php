<?php
/**
 * Copyright © chotructuyen.co All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Chottvn\Affiliate\Model;

use Chottvn\Affiliate\Api\Data\LevelRuleInterfaceFactory;
use Chottvn\Affiliate\Api\Data\LevelRuleSearchResultsInterfaceFactory;
use Chottvn\Affiliate\Api\LevelRuleRepositoryInterface;
use Chottvn\Affiliate\Model\ResourceModel\LevelRule as ResourceLevelRule;
use Chottvn\Affiliate\Model\ResourceModel\LevelRule\CollectionFactory as LevelRuleCollectionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Store\Model\StoreManagerInterface;

class LevelRuleRepository implements LevelRuleRepositoryInterface
{

    protected $levelRuleCollectionFactory;

    protected $levelRuleFactory;

    protected $searchResultsFactory;

    private $collectionProcessor;

    protected $resource;

    protected $dataLevelRuleFactory;

    protected $extensibleDataObjectConverter;
    protected $dataObjectProcessor;

    protected $dataObjectHelper;

    private $storeManager;

    protected $extensionAttributesJoinProcessor;


    /**
     * @param ResourceLevelRule $resource
     * @param LevelRuleFactory $levelRuleFactory
     * @param LevelRuleInterfaceFactory $dataLevelRuleFactory
     * @param LevelRuleCollectionFactory $levelRuleCollectionFactory
     * @param LevelRuleSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     * @param CollectionProcessorInterface $collectionProcessor
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     */
    public function __construct(
        ResourceLevelRule $resource,
        LevelRuleFactory $levelRuleFactory,
        LevelRuleInterfaceFactory $dataLevelRuleFactory,
        LevelRuleCollectionFactory $levelRuleCollectionFactory,
        LevelRuleSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        CollectionProcessorInterface $collectionProcessor,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->resource = $resource;
        $this->levelRuleFactory = $levelRuleFactory;
        $this->levelRuleCollectionFactory = $levelRuleCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataLevelRuleFactory = $dataLevelRuleFactory;
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
        \Chottvn\Affiliate\Api\Data\LevelRuleInterface $levelRule
    ) {
        /* if (empty($levelRule->getStoreId())) {
            $storeId = $this->storeManager->getStore()->getId();
            $levelRule->setStoreId($storeId);
        } */
        
        $levelRuleData = $this->extensibleDataObjectConverter->toNestedArray(
            $levelRule,
            [],
            \Chottvn\Affiliate\Api\Data\LevelRuleInterface::class
        );
        
        $levelRuleModel = $this->levelRuleFactory->create()->setData($levelRuleData);
        
        try {
            $this->resource->save($levelRuleModel);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the levelRule: %1',
                $exception->getMessage()
            ));
        }
        return $levelRuleModel->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function get($levelRuleId)
    {
        $levelRule = $this->levelRuleFactory->create();
        $this->resource->load($levelRule, $levelRuleId);
        if (!$levelRule->getId()) {
            throw new NoSuchEntityException(__('LevelRule with id "%1" does not exist.', $levelRuleId));
        }
        return $levelRule->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->levelRuleCollectionFactory->create();
        
        $this->extensionAttributesJoinProcessor->process(
            $collection,
            \Chottvn\Affiliate\Api\Data\LevelRuleInterface::class
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
        \Chottvn\Affiliate\Api\Data\LevelRuleInterface $levelRule
    ) {
        try {
            $levelRuleModel = $this->levelRuleFactory->create();
            $this->resource->load($levelRuleModel, $levelRule->getLevelruleId());
            $this->resource->delete($levelRuleModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the LevelRule: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($levelRuleId)
    {
        return $this->delete($this->get($levelRuleId));
    }
}

