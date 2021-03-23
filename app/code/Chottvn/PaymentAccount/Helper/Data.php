<?php
/**
 * Copyright © (c) Cho Truc Tuyen 2020 All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Chottvn\PaymentAccount\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Chottvn\PaymentAccount\Model\BankFactory;

class Data extends AbstractHelper
{
    /**
     * @var BankFactory
     */
    public $bankFactory;

     /**
     * @var BankFactory
     */
    public $_resource;
    
    /**
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\ResourceConnection $Resource,
        BankFactory $bankFactory
    ) {
        
        parent::__construct($context);
        $this->bankFactory = $bankFactory;
        $this->_resource = $Resource;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return true;
    }

    /**
     * @param 
     *
     * @return Collection
     */
    public function getBankCollection()
    {
        $collection = $this->bankFactory->create()->getCollection();

        return $collection;
    }
     /**
     * @param 
     *
     * @return Collection
     */
    public function getPaymentAccountCollection($customerId)
    {
        $collection = $this->bankFactory->create()->getCollection();
        $second_table_name = $this->_resource->getTableName('chottvn_paymentaccount_customerba');
        $collection->getSelect()
        ->reset(\Zend_Db_Select::COLUMNS)
        ->columns(['chottvn_paymentaccount_customerba.paymentaccount_bank_id','chottvn_paymentaccount_customerba.account_number'])
        ->joinLeft(array('chottvn_paymentaccount_customerba' => $second_table_name),
        'main_table.bank_id = chottvn_paymentaccount_customerba.paymentaccount_bank_id',null)
        ->where("chottvn_paymentaccount_customerba.customer_id = ?", $customerId)
        ->where("chottvn_paymentaccount_customerba.status = ?", '1');
        $collection = $collection->getFirstItem();
        return $collection;
    }
}

