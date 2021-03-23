<?php
declare(strict_types=1);

namespace Chottvn\Finance\Model\ResourceModel\TransactionType;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * @var string
     */
    protected $_idFieldName = 'transactiontype_id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Chottvn\Finance\Model\TransactionType::class,
            \Chottvn\Finance\Model\ResourceModel\TransactionType::class
        );
    }
}

