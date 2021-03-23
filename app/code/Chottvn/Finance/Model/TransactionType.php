<?php
declare(strict_types=1);

namespace Chottvn\Finance\Model;

use Chottvn\Finance\Api\Data\TransactionTypeInterface;
use Chottvn\Finance\Api\Data\TransactionTypeInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;

class TransactionType extends \Magento\Framework\Model\AbstractModel
{

    protected $dataObjectHelper;

    protected $_eventPrefix = 'chottvn_finance_transactiontype';
    protected $transactiontypeDataFactory;


    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param TransactionTypeInterfaceFactory $transactiontypeDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param \Chottvn\Finance\Model\ResourceModel\TransactionType $resource
     * @param \Chottvn\Finance\Model\ResourceModel\TransactionType\Collection $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        TransactionTypeInterfaceFactory $transactiontypeDataFactory,
        DataObjectHelper $dataObjectHelper,
        \Chottvn\Finance\Model\ResourceModel\TransactionType $resource,
        \Chottvn\Finance\Model\ResourceModel\TransactionType\Collection $resourceCollection,
        array $data = []
    ) {
        $this->transactiontypeDataFactory = $transactiontypeDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Retrieve transactiontype model with transactiontype data
     * @return TransactionTypeInterface
     */
    public function getDataModel()
    {
        $transactiontypeData = $this->getData();
        
        $transactiontypeDataObject = $this->transactiontypeDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $transactiontypeDataObject,
            $transactiontypeData,
            TransactionTypeInterface::class
        );
        
        return $transactiontypeDataObject;
    }
}

