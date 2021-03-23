<?php
/**
 * Copyright (c) 2019 2020 ChottVN
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace Chottvn\OrderPayment\Model;

use Chottvn\OrderPayment\Api\Data\BankAccountInterface;
use Magento\Framework\Api\DataObjectHelper;
use Chottvn\OrderPayment\Api\Data\BankAccountInterfaceFactory;
use Chottvn\OrderPayment\Model\ResourceModel\BankAccount\CollectionFactory as BankCollectionFactory;
/**
 * Class BankAccount
 *
 * @package Chottvn\OrderPayment\Model
 */
class BankAccount extends \Magento\Framework\Model\AbstractModel
{

    protected $dataObjectHelper;

    protected $_eventPrefix = 'chottvn_orderpayment_bankaccount';
    protected $bankaccountDataFactory;
    public $bankCollectionFactory;


    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param BankAccountInterfaceFactory $bankaccountDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param \Chottvn\OrderPayment\Model\ResourceModel\BankAccount $resource
     * @param \Chottvn\OrderPayment\Model\ResourceModel\BankAccount\Collection $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        BankAccountInterfaceFactory $bankaccountDataFactory,
        DataObjectHelper $dataObjectHelper,
        BankCollectionFactory $bankCollectionFactory,
        \Chottvn\OrderPayment\Model\ResourceModel\BankAccount $resource,
        \Chottvn\OrderPayment\Model\ResourceModel\BankAccount\Collection $resourceCollection,
        array $data = []
    ) {
        $this->bankaccountDataFactory = $bankaccountDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->bankCollectionFactory = $bankCollectionFactory;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Retrieve bankaccount model with bankaccount data
     * @return BankAccountInterface
     */
    public function getDataModel()
    {
        $bankaccountData = $this->getData();
        
        $bankaccountDataObject = $this->bankaccountDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $bankaccountDataObject,
            $bankaccountData,
            BankAccountInterface::class
        );
        
        return $bankaccountDataObject;
    }
    /**
     * @return \Mageplaza\Blog\Model\ResourceModel\Tag\Collection
     */
    public function getSelectedbankCollection()
    {
        $collection = $this->bankCollectionFactory->create();
        return $collection;
    }

}

