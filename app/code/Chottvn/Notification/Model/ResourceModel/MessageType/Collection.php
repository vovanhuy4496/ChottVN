<?php
declare(strict_types=1);

namespace Chottvn\Notification\Model\ResourceModel\MessageType;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * @var string
     */
    protected $_idFieldName = 'id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Chottvn\Notification\Model\MessageType::class,
            \Chottvn\Notification\Model\ResourceModel\MessageType::class
        );
    }
}

