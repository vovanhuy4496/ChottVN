<?php
namespace Chottvn\Rma\Model\ResourceModel\Log;


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
        $this->_init('Chottvn\Rma\Model\Log', 'Chottvn\Rma\Model\ResourceModel\Log');
    }
}
