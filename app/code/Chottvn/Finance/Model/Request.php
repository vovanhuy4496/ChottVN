<?php
declare(strict_types=1);

namespace Chottvn\Finance\Model;

use Chottvn\Finance\Api\Data\RequestInterface;
use Chottvn\Finance\Api\Data\RequestInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;

class Request extends \Magento\Framework\Model\AbstractModel
{

    protected $_eventPrefix = 'chottvn_finance_request';
    protected $dataObjectHelper;

    protected $requestDataFactory;


    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param RequestInterfaceFactory $requestDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param \Chottvn\Finance\Model\ResourceModel\Request $resource
     * @param \Chottvn\Finance\Model\ResourceModel\Request\Collection $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        RequestInterfaceFactory $requestDataFactory,
        DataObjectHelper $dataObjectHelper,
        \Chottvn\Finance\Model\ResourceModel\Request $resource,
        \Chottvn\Finance\Model\ResourceModel\Request\Collection $resourceCollection,
        array $data = []
    ) {
        $this->requestDataFactory = $requestDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Retrieve request model with request data
     * @return RequestInterface
     */
    public function getDataModel()
    {
        $requestData = $this->getData();
        
        $requestDataObject = $this->requestDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $requestDataObject,
            $requestData,
            RequestInterface::class
        );
        
        return $requestDataObject;
    }
}

