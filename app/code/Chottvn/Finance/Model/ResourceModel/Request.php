<?php
declare(strict_types=1);

namespace Chottvn\Finance\Model\ResourceModel;

class Request extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('chottvn_finance_request', 'request_id');
    }
}

