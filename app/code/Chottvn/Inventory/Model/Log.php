<?php
/**
 * Copyright © © 2020 chotructuyen.co All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Chottvn\Inventory\Model;

use Chottvn\Inventory\Api\Data\LogInterface;
use Chottvn\Inventory\Api\Data\LogInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;

class Log extends \Magento\Framework\Model\AbstractModel
{

    protected $logDataFactory;

    protected $dataObjectHelper;

    protected $_eventPrefix = 'chottvn_inventory_log';

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param LogInterfaceFactory $logDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param \Chottvn\Inventory\Model\ResourceModel\Log $resource
     * @param \Chottvn\Inventory\Model\ResourceModel\Log\Collection $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        LogInterfaceFactory $logDataFactory,
        DataObjectHelper $dataObjectHelper,
        \Chottvn\Inventory\Model\ResourceModel\Log $resource,
        \Chottvn\Inventory\Model\ResourceModel\Log\Collection $resourceCollection,
        array $data = []
    ) {
        $this->logDataFactory = $logDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Retrieve log model with log data
     * @return LogInterface
     */
    public function getDataModel()
    {
        $logData = $this->getData();
        
        $logDataObject = $this->logDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $logDataObject,
            $logData,
            LogInterface::class
        );
        
        return $logDataObject;
    }
}

