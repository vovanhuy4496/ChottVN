<?php
declare(strict_types=1);

namespace Chottvn\Finance\Model\Data;

use Chottvn\Finance\Api\Data\TransactionInterface;

class Transaction extends \Magento\Framework\Api\AbstractExtensibleObject implements TransactionInterface
{

    /**
     * Get transaction_id
     * @return string|null
     */
    public function getTransactionId()
    {
        return $this->_get(self::TRANSACTION_ID);
    }

    /**
     * Set transaction_id
     * @param string $transactionId
     * @return \Chottvn\Finance\Api\Data\TransactionInterface
     */
    public function setTransactionId($transactionId)
    {
        return $this->setData(self::TRANSACTION_ID, $transactionId);
    }

    /**
     * Get account_id
     * @return string|null
     */
    public function getAccountId()
    {
        return $this->_get(self::ACCOUNT_ID);
    }

    /**
     * Set account_id
     * @param string $accountId
     * @return \Chottvn\Finance\Api\Data\TransactionInterface
     */
    public function setAccountId($accountId)
    {
        return $this->setData(self::ACCOUNT_ID, $accountId);
    }

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Chottvn\Finance\Api\Data\TransactionExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * Set an extension attributes object.
     * @param \Chottvn\Finance\Api\Data\TransactionExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Chottvn\Finance\Api\Data\TransactionExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }

    /**
     * Get request_id
     * @return string|null
     */
    public function getRequestId()
    {
        return $this->_get(self::REQUEST_ID);
    }

    /**
     * Set request_id
     * @param string $requestId
     * @return \Chottvn\Finance\Api\Data\TransactionInterface
     */
    public function setRequestId($requestId)
    {
        return $this->setData(self::REQUEST_ID, $requestId);
    }

    /**
     * Get transaction_type_id
     * @return string|null
     */
    public function getTransactionTypeId()
    {
        return $this->_get(self::TRANSACTION_TYPE_ID);
    }

    /**
     * Set transaction_type_id
     * @param string $transactionTypeId
     * @return \Chottvn\Finance\Api\Data\TransactionInterface
     */
    public function setTransactionTypeId($transactionTypeId)
    {
        return $this->setData(self::TRANSACTION_TYPE_ID, $transactionTypeId);
    }

    /**
     * Get amount
     * @return string|null
     */
    public function getAmount()
    {
        return $this->_get(self::AMOUNT);
    }

    /**
     * Set amount
     * @param string $amount
     * @return \Chottvn\Finance\Api\Data\TransactionInterface
     */
    public function setAmount($amount)
    {
        return $this->setData(self::AMOUNT, $amount);
    }

    /**
     * Get rate
     * @return string|null
     */
    public function getRate()
    {
        return $this->_get(self::RATE);
    }

    /**
     * Set rate
     * @param string $rate
     * @return \Chottvn\Finance\Api\Data\TransactionInterface
     */
    public function setRate($rate)
    {
        return $this->setData(self::RATE, $rate);
    }

    /**
     * Get start_date
     * @return string|null
     */
    public function getStartDate()
    {
        return $this->_get(self::START_DATE);
    }

    /**
     * Set start_date
     * @param string $startDate
     * @return \Chottvn\Finance\Api\Data\TransactionInterface
     */
    public function setStartDate($startDate)
    {
        return $this->setData(self::START_DATE, $startDate);
    }

    /**
     * Get end_date
     * @return string|null
     */
    public function getEndDate()
    {
        return $this->_get(self::END_DATE);
    }

    /**
     * Set end_date
     * @param string $endDate
     * @return \Chottvn\Finance\Api\Data\TransactionInterface
     */
    public function setEndDate($endDate)
    {
        return $this->setData(self::END_DATE, $endDate);
    }

    /**
     * Get transaction_date
     * @return string|null
     */
    public function getTransactionDate()
    {
        return $this->_get(self::TRANSACTION_DATE);
    }

    /**
     * Set transaction_date
     * @param string $transactionDate
     * @return \Chottvn\Finance\Api\Data\TransactionInterface
     */
    public function setTransactionDate($transactionDate)
    {
        return $this->setData(self::TRANSACTION_DATE, $transactionDate);
    }

    /**
     * Get note
     * @return string|null
     */
    public function getNote()
    {
        return $this->_get(self::NOTE);
    }

    /**
     * Set note
     * @param string $note
     * @return \Chottvn\Finance\Api\Data\TransactionInterface
     */
    public function setNote($note)
    {
        return $this->setData(self::NOTE, $note);
    }
}

