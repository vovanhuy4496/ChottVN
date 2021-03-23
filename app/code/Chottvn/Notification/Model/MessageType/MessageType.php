<?php
declare(strict_types=1);

namespace Chottvn\Notification\Model\MessageType;

class MessageType extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    protected $messageTypeCollectionFactory;
    
    /**
     * * @param CollectionFactory $messageTypeCollectionFactory
     */

    public function __construct(
        \Chottvn\Notification\Model\ResourceModel\MessageType\CollectionFactory $messageTypeCollectionFactory
    ) {
        $this->messagetypeCollectionFactory = $messageTypeCollectionFactory;
    }

    /**
     * getAllOptions
     *
     * @return array
     */
    public function getAllOptions()
    {
        if ($this->_options === null) {
            $resultPage = $this->messagetypeCollectionFactory->create()->addFieldToFilter("status", array('eq' => 1));
            $collection = $resultPage->getData();
            $setData = [];
            foreach ($collection as $item) {
                $getData = ['value' => $item["id"], 'label' => $item["name"], 'sort' => $item["id"]];
                array_push($setData, $getData);
            }
            $this->_options = $setData;
        }
        return $this->_options;
    }
}

