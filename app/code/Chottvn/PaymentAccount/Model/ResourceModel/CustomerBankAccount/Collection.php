<?php
/**
 * Copyright © (c) Cho Truc Tuyen 2020 All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Chottvn\PaymentAccount\Model\ResourceModel\CustomerBankAccount;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * @var string
     */
    protected $_idFieldName = 'customerba_id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Chottvn\PaymentAccount\Model\CustomerBankAccount::class,
            \Chottvn\PaymentAccount\Model\ResourceModel\CustomerBankAccount::class
        );
    }
    protected function _initSelect()
    {
        parent::_initSelect();
        // Get Customer info
        $this->getSelect()->joinLeft(
                ['customer' => $this->getTable('customer_entity')],
                'main_table.customer_id = customer.entity_id',
                ['customer_firstname'=>'customer.firstname']
        );
        // Get Bank info
        $this->getSelect()->joinLeft(
                ['bank' => $this->getTable('chottvn_paymentaccount_bank')],
                'main_table.paymentaccount_bank_id = bank.bank_id',
                ['bank_name'=>'bank.name']
            );
        $this->addFilterToMap('customer_firstname', 'customer.firstname');
        $this->addFilterToMap('bank_name', 'bank.name');
    }
}

