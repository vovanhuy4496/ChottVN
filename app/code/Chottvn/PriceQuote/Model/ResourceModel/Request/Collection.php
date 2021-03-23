<?php
/**
 * Copyright © (c) chotructuyen.vn All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Chottvn\PriceQuote\Model\ResourceModel\Request;

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
            \Chottvn\PriceQuote\Model\Request::class,
            \Chottvn\PriceQuote\Model\ResourceModel\Request::class
        );
    }
}

