<?php
/**
 * Copyright © chotructuyen.co All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Chottvn\Affiliate\Model\ResourceModel\RewardRule;

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
            \Chottvn\Affiliate\Model\RewardRule::class,
            \Chottvn\Affiliate\Model\ResourceModel\RewardRule::class
        );
    }

    /**
     * Filter Active Items
     *
     * @return $this
     */
    public function filterActive(){
       $this->addFieldToFilter('main_table.status', ["eq" => 1]);
       return $this;
    }

    /**
     * Filter Not Deleted
     *
     * @return $this
     */
    public function filterNotDeleted(){
       $this->addFieldToFilter('main_table.deleted_at', ['null' => true]);
       return $this;
    }
}

