<?php
/**
 * Copyright © chotructuyen.co All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Chottvn\Affiliate\Model;

use Chottvn\Affiliate\Api\Data\LevelRuleInterface;
use Chottvn\Affiliate\Api\Data\LevelRuleInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;

class LevelRule extends \Magento\Framework\Model\AbstractModel
{

    protected $dataObjectHelper;

    protected $levelruleDataFactory;

    protected $_eventPrefix = 'affiliate_levelrule';

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param LevelRuleInterfaceFactory $levelruleDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param \Chottvn\Affiliate\Model\ResourceModel\LevelRule $resource
     * @param \Chottvn\Affiliate\Model\ResourceModel\LevelRule\Collection $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        LevelRuleInterfaceFactory $levelruleDataFactory,
        DataObjectHelper $dataObjectHelper,
        \Chottvn\Affiliate\Model\ResourceModel\LevelRule $resource,
        \Chottvn\Affiliate\Model\ResourceModel\LevelRule\Collection $resourceCollection,
        array $data = []
    ) {
        $this->levelruleDataFactory = $levelruleDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Retrieve levelrule model with levelrule data
     * @return LevelRuleInterface
     */
    public function getDataModel()
    {
        $levelruleData = $this->getData();
        
        $levelruleDataObject = $this->levelruleDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $levelruleDataObject,
            $levelruleData,
            LevelRuleInterface::class
        );
        
        return $levelruleDataObject;
    }
}

