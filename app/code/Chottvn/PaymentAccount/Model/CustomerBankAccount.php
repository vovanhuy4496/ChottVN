<?php
/**
 * Copyright © (c) Cho Truc Tuyen 2020 All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Chottvn\PaymentAccount\Model;

use Chottvn\PaymentAccount\Api\Data\CustomerBankAccountInterface;
use Chottvn\PaymentAccount\Api\Data\CustomerBankAccountInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;

class CustomerBankAccount extends \Magento\Framework\Model\AbstractModel
{

    protected $customerbankaccountDataFactory;

    protected $_eventPrefix = 'chottvn_paymentaccount_customerbankaccount';
    protected $dataObjectHelper;


    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param CustomerBankAccountInterfaceFactory $customerbankaccountDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param \Chottvn\PaymentAccount\Model\ResourceModel\CustomerBankAccount $resource
     * @param \Chottvn\PaymentAccount\Model\ResourceModel\CustomerBankAccount\Collection $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        CustomerBankAccountInterfaceFactory $customerbankaccountDataFactory,
        DataObjectHelper $dataObjectHelper,
        \Chottvn\PaymentAccount\Model\ResourceModel\CustomerBankAccount $resource,
        \Chottvn\PaymentAccount\Model\ResourceModel\CustomerBankAccount\Collection $resourceCollection,
        array $data = []
    ) {
        $this->customerbankaccountDataFactory = $customerbankaccountDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Retrieve customerbankaccount model with customerbankaccount data
     * @return CustomerBankAccountInterface
     */
    public function getDataModel()
    {
        $customerbankaccountData = $this->getData();
        
        $customerbankaccountDataObject = $this->customerbankaccountDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $customerbankaccountDataObject,
            $customerbankaccountData,
            CustomerBankAccountInterface::class
        );
        
        return $customerbankaccountDataObject;
    }
}

