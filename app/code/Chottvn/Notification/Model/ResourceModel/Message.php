<?php
declare(strict_types=1);

namespace Chottvn\Notification\Model\ResourceModel;

class Message extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('chottvn_notification_message', 'id');
    }
}

