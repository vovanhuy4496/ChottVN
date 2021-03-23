<?php
/**
 * Copyright © (c) chotructuyen.vn All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Chottvn\PriceQuote\Model;

use Chottvn\PriceQuote\Api\Data\RequestInterface;
use Chottvn\PriceQuote\Api\Data\RequestInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;

class Request extends \Magento\Framework\Model\AbstractModel
{

    protected $dataObjectHelper;

    protected $requestDataFactory;

    protected $_eventPrefix = 'chottvn_pricequote_request';

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param RequestInterfaceFactory $requestDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param \Chottvn\PriceQuote\Model\ResourceModel\Request $resource
     * @param \Chottvn\PriceQuote\Model\ResourceModel\Request\Collection $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        RequestInterfaceFactory $requestDataFactory,
        DataObjectHelper $dataObjectHelper,
        \Chottvn\PriceQuote\Model\ResourceModel\Request $resource,
        \Chottvn\PriceQuote\Model\ResourceModel\Request\Collection $resourceCollection,
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

