<?php
/**
 * Copyright © chotructuyen.co All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Chottvn\Affiliate\Model;

use Chottvn\Affiliate\Api\Data\RewardRuleInterface;
use Chottvn\Affiliate\Api\Data\RewardRuleInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;

class RewardRule extends \Magento\Framework\Model\AbstractModel
{

    protected $_eventPrefix = 'chottvn_affiliate_reward_rule';
    protected $dataObjectHelper;

    protected $rewardruleDataFactory;


    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param RewardRuleInterfaceFactory $rewardruleDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param \Chottvn\Affiliate\Model\ResourceModel\RewardRule $resource
     * @param \Chottvn\Affiliate\Model\ResourceModel\RewardRule\Collection $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        RewardRuleInterfaceFactory $rewardruleDataFactory,
        DataObjectHelper $dataObjectHelper,
        \Chottvn\Affiliate\Model\ResourceModel\RewardRule $resource,
        \Chottvn\Affiliate\Model\ResourceModel\RewardRule\Collection $resourceCollection,
        array $data = []
    ) {
        $this->rewardruleDataFactory = $rewardruleDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Retrieve rewardrule model with rewardrule data
     * @return RewardRuleInterface
     */
    public function getDataModel()
    {
        $rewardruleData = $this->getData();
        
        $rewardruleDataObject = $this->rewardruleDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $rewardruleDataObject,
            $rewardruleData,
            RewardRuleInterface::class
        );
        
        return $rewardruleDataObject;
    }
}

