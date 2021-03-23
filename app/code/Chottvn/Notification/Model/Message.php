<?php
declare(strict_types=1);

namespace Chottvn\Notification\Model;

use Chottvn\Notification\Api\Data\MessageInterface;
use Chottvn\Notification\Api\Data\MessageInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;

class Message extends \Magento\Framework\Model\AbstractModel
{

    protected $messageDataFactory;

    protected $dataObjectHelper;

    // protected $_eventPrefix = 'chottvn_notification_message';

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param MessageInterfaceFactory $messageDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param \Chottvn\Notification\Model\ResourceModel\Message $resource
     * @param \Chottvn\Notification\Model\ResourceModel\Message\Collection $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        MessageInterfaceFactory $messageDataFactory,
        DataObjectHelper $dataObjectHelper,
        \Chottvn\Notification\Model\ResourceModel\Message $resource,
        \Chottvn\Notification\Model\ResourceModel\Message\Collection $resourceCollection,
        array $data = []
    ) {
        $this->messageDataFactory = $messageDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Retrieve message model with message data
     * @return MessageInterface
     */
    public function getDataModel()
    {
        $messageData = $this->getData();
        
        $messageDataObject = $this->messageDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $messageDataObject,
            $messageData,
            MessageInterface::class
        );
        
        return $messageDataObject;
    }
}

