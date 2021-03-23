<?php
declare(strict_types=1);

namespace Chottvn\Finance\Model\ResourceModel\Request;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * @var string
     */
    protected $_idFieldName = 'request_id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Chottvn\Finance\Model\Request::class,
            \Chottvn\Finance\Model\ResourceModel\Request::class
        );
    }
}

