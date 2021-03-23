<?php
/**
 * Copyright © (c) Cho Truc Tuyen 2020 All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Chottvn\PaymentAccount\Model;

use Chottvn\PaymentAccount\Api\Data\BankInterface;
use Chottvn\PaymentAccount\Api\Data\BankInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;

class Bank extends \Magento\Framework\Model\AbstractModel
{

    protected $bankDataFactory;

    protected $dataObjectHelper;

    protected $_eventPrefix = 'chottvn_paymentaccount_bank';

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param BankInterfaceFactory $bankDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param \Chottvn\PaymentAccount\Model\ResourceModel\Bank $resource
     * @param \Chottvn\PaymentAccount\Model\ResourceModel\Bank\Collection $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        BankInterfaceFactory $bankDataFactory,
        DataObjectHelper $dataObjectHelper,
        \Chottvn\PaymentAccount\Model\ResourceModel\Bank $resource,
        \Chottvn\PaymentAccount\Model\ResourceModel\Bank\Collection $resourceCollection,
        array $data = []
    ) {
        $this->bankDataFactory = $bankDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Retrieve bank model with bank data
     * @return BankInterface
     */
    public function getDataModel()
    {
        $bankData = $this->getData();
        
        $bankDataObject = $this->bankDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $bankDataObject,
            $bankData,
            BankInterface::class
        );
        
        return $bankDataObject;
    }
}

