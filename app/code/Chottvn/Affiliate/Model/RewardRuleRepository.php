<?php
/**
 * Copyright © chotructuyen.co All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Chottvn\Affiliate\Model;

use Chottvn\Affiliate\Api\Data\RewardRuleInterfaceFactory;
use Chottvn\Affiliate\Api\Data\RewardRuleSearchResultsInterfaceFactory;
use Chottvn\Affiliate\Api\RewardRuleRepositoryInterface;
use Chottvn\Affiliate\Model\ResourceModel\RewardRule as ResourceRewardRule;
use Chottvn\Affiliate\Model\ResourceModel\RewardRule\CollectionFactory as RewardRuleCollectionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Store\Model\StoreManagerInterface;

class RewardRuleRepository implements RewardRuleRepositoryInterface
{

    protected $searchResultsFactory;

    private $collectionProcessor;

    protected $dataRewardRuleFactory;

    protected $resource;

    protected $extensibleDataObjectConverter;
    protected $dataObjectProcessor;

    protected $dataObjectHelper;

    private $storeManager;

    protected $rewardRuleFactory;

    protected $rewardRuleCollectionFactory;

    protected $extensionAttributesJoinProcessor;


    /**
     * @param ResourceRewardRule $resource
     * @param RewardRuleFactory $rewardRuleFactory
     * @param RewardRuleInterfaceFactory $dataRewardRuleFactory
     * @param RewardRuleCollectionFactory $rewardRuleCollectionFactory
     * @param RewardRuleSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     * @param CollectionProcessorInterface $collectionProcessor
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     */
    public function __construct(
        ResourceRewardRule $resource,
        RewardRuleFactory $rewardRuleFactory,
        RewardRuleInterfaceFactory $dataRewardRuleFactory,
        RewardRuleCollectionFactory $rewardRuleCollectionFactory,
        RewardRuleSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        CollectionProcessorInterface $collectionProcessor,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->resource = $resource;
        $this->rewardRuleFactory = $rewardRuleFactory;
        $this->rewardRuleCollectionFactory = $rewardRuleCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataRewardRuleFactory = $dataRewardRuleFactory;
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
        \Chottvn\Affiliate\Api\Data\RewardRuleInterface $rewardRule
    ) {
        /* if (empty($rewardRule->getStoreId())) {
            $storeId = $this->storeManager->getStore()->getId();
            $rewardRule->setStoreId($storeId);
        } */
        
        $rewardRuleData = $this->extensibleDataObjectConverter->toNestedArray(
            $rewardRule,
            [],
            \Chottvn\Affiliate\Api\Data\RewardRuleInterface::class
        );
        
        $rewardRuleModel = $this->rewardRuleFactory->create()->setData($rewardRuleData);
        
        try {
            $this->resource->save($rewardRuleModel);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the rewardRule: %1',
                $exception->getMessage()
            ));
        }
        return $rewardRuleModel->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function get($rewardRuleId)
    {
        $rewardRule = $this->rewardRuleFactory->create();
        $this->resource->load($rewardRule, $rewardRuleId);
        if (!$rewardRule->getId()) {
            throw new NoSuchEntityException(__('RewardRule with id "%1" does not exist.', $rewardRuleId));
        }
        return $rewardRule->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->rewardRuleCollectionFactory->create();
        
        $this->extensionAttributesJoinProcessor->process(
            $collection,
            \Chottvn\Affiliate\Api\Data\RewardRuleInterface::class
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
        \Chottvn\Affiliate\Api\Data\RewardRuleInterface $rewardRule
    ) {
        try {
            $rewardRuleModel = $this->rewardRuleFactory->create();
            $this->resource->load($rewardRuleModel, $rewardRule->getRewardruleId());
            $this->resource->delete($rewardRuleModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the RewardRule: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($rewardRuleId)
    {
        return $this->delete($this->get($rewardRuleId));
    }
}

