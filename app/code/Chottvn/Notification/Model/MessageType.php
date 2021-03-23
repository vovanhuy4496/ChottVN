<?php
declare(strict_types=1);

namespace Chottvn\Notification\Model;

use Chottvn\Notification\Api\Data\MessageTypeInterface;
use Chottvn\Notification\Api\Data\MessageTypeInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;

class MessageType extends \Magento\Framework\Model\AbstractModel
{

    protected $dataObjectHelper;

    // protected $_eventPrefix = 'chottvn_notification_messagetype';
    protected $messageTypeDataFactory;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param MessageTypeInterfaceFactory $messageTypeDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param \Chottvn\Notification\Model\ResourceModel\MessageType $resource
     * @param \Chottvn\Notification\Model\ResourceModel\MessageType\Collection $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        MessageTypeInterfaceFactory $messageTypeDataFactory,
        DataObjectHelper $dataObjectHelper,
        \Chottvn\Notification\Model\ResourceModel\MessageType $resource,
        \Chottvn\Notification\Model\ResourceModel\MessageType\Collection $resourceCollection,
        array $data = []
    ) {
        $this->messagetypeDataFactory = $messageTypeDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Retrieve messagetype model with messagetype data
     * @return MessageTypeInterface
     */
    public function getDataModel()
    {
        $messageTypeData = $this->getData();
        
        $messageTypeDataObject = $this->messagetypeDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $messageTypeDataObject,
            $messageTypeData,
            MessageTypeInterface::class
        );
        
        return $messageTypeDataObject;
    }
}

