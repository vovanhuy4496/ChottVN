<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Orderattr
 */


namespace Amasty\Orderattr\Model\Indexer\Mview;

class Changelog extends \Magento\Framework\Mview\View\Changelog
{
    public function __construct(\Magento\Framework\App\ResourceConnection $resource)
    {
        parent::__construct($resource);
        $this->connection = $this->resource->getConnection('sales');
    }
}
