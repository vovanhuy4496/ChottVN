<?php
namespace Chottvn\Finance\Model\ResourceModel\Log;


use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Banner Collection
 */
class Collection extends AbstractCollection
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
        $this->_init('Chottvn\Finance\Model\Log', 'Chottvn\Finance\Model\ResourceModel\Log');
    }
}
