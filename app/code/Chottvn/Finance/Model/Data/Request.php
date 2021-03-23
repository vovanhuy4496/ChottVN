<?php
declare(strict_types=1);

namespace Chottvn\Finance\Model\Data;

use Chottvn\Finance\Api\Data\RequestInterface;

class Request extends \Magento\Framework\Api\AbstractExtensibleObject implements RequestInterface
{

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
     * @return \Chottvn\Finance\Api\Data\RequestInterface
     */
    public function setRequestId($requestId)
    {
        return $this->setData(self::REQUEST_ID, $requestId);
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
     * @return \Chottvn\Finance\Api\Data\RequestInterface
     */
    public function setAccountId($accountId)
    {
        return $this->setData(self::ACCOUNT_ID, $accountId);
    }

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Chottvn\Finance\Api\Data\RequestExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * Set an extension attributes object.
     * @param \Chottvn\Finance\Api\Data\RequestExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Chottvn\Finance\Api\Data\RequestExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
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
     * @return \Chottvn\Finance\Api\Data\RequestInterface
     */
    public function setTransactionTypeId($transactionTypeId)
    {
        return $this->setData(self::TRANSACTION_TYPE_ID, $transactionTypeId);
    }

    /**
     * Get requested_at
     * @return string|null
     */
    public function getRequestedAt()
    {
        return $this->_get(self::REQUESTED_AT);
    }

    /**
     * Set requested_at
     * @param string $requestedAt
     * @return \Chottvn\Finance\Api\Data\RequestInterface
     */
    public function setRequestedAt($requestedAt)
    {
        return $this->setData(self::REQUESTED_AT, $requestedAt);
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
     * @return \Chottvn\Finance\Api\Data\RequestInterface
     */
    public function setAmount($amount)
    {
        return $this->setData(self::AMOUNT, $amount);
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
     * @return \Chottvn\Finance\Api\Data\RequestInterface
     */
    public function setNote($note)
    {
        return $this->setData(self::NOTE, $note);
    }

    /**
     * Get status
     * @return string|null
     */
    public function getStatus()
    {
        return $this->_get(self::STATUS);
    }

    /**
     * Set status
     * @param string $status
     * @return \Chottvn\Finance\Api\Data\RequestInterface
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * Get processed_by
     * @return string|null
     */
    public function getProcessedBy()
    {
        return $this->_get(self::PROCESSED_BY);
    }

    /**
     * Set processed_by
     * @param string $processedBy
     * @return \Chottvn\Finance\Api\Data\RequestInterface
     */
    public function setProcessedBy($processedBy)
    {
        return $this->setData(self::PROCESSED_BY, $processedBy);
    }

    /**
     * Get processed_at
     * @return string|null
     */
    public function getProcessedAt()
    {
        return $this->_get(self::PROCESSED_AT);
    }

    /**
     * Set processed_at
     * @param string $processedAt
     * @return \Chottvn\Finance\Api\Data\RequestInterface
     */
    public function setProcessedAt($processedAt)
    {
        return $this->setData(self::PROCESSED_AT, $processedAt);
    }

    /**
     * Get created_at
     * @return string|null
     */
    public function getCreatedAt()
    {
        return $this->_get(self::CREATED_AT);
    }

    /**
     * Set created_at
     * @param string $createdAt
     * @return \Chottvn\Finance\Api\Data\RequestInterface
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * Get updated_at
     * @return string|null
     */
    public function getUpdatedAt()
    {
        return $this->_get(self::UPDATED_AT);
    }

    /**
     * Set updated_at
     * @param string $updatedAt
     * @return \Chottvn\Finance\Api\Data\RequestInterface
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }
}

