<?php
declare(strict_types=1);

namespace Chottvn\Finance\Api\Data;

interface RequestInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{

    const STATUS = 'status';
    const REQUEST_ID = 'request_id';
    const CREATED_AT = 'created_at';
    const ACCOUNT_ID = 'account_id';
    const PROCESSED_AT = 'processed_at';
    const NOTE = 'note';
    const PROCESSED_BY = 'processed_by';
    const TRANSACTION_TYPE_ID = 'transaction_type_id';
    const AMOUNT = 'amount';
    const REQUESTED_AT = 'requested_at';
    const UPDATED_AT = 'updated_at';

    /**
     * Get request_id
     * @return string|null
     */
    public function getRequestId();

    /**
     * Set request_id
     * @param string $requestId
     * @return \Chottvn\Finance\Api\Data\RequestInterface
     */
    public function setRequestId($requestId);

    /**
     * Get account_id
     * @return string|null
     */
    public function getAccountId();

    /**
     * Set account_id
     * @param string $accountId
     * @return \Chottvn\Finance\Api\Data\RequestInterface
     */
    public function setAccountId($accountId);

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Chottvn\Finance\Api\Data\RequestExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     * @param \Chottvn\Finance\Api\Data\RequestExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Chottvn\Finance\Api\Data\RequestExtensionInterface $extensionAttributes
    );

    /**
     * Get transaction_type_id
     * @return string|null
     */
    public function getTransactionTypeId();

    /**
     * Set transaction_type_id
     * @param string $transactionTypeId
     * @return \Chottvn\Finance\Api\Data\RequestInterface
     */
    public function setTransactionTypeId($transactionTypeId);

    /**
     * Get requested_at
     * @return string|null
     */
    public function getRequestedAt();

    /**
     * Set requested_at
     * @param string $requestedAt
     * @return \Chottvn\Finance\Api\Data\RequestInterface
     */
    public function setRequestedAt($requestedAt);

    /**
     * Get amount
     * @return string|null
     */
    public function getAmount();

    /**
     * Set amount
     * @param string $amount
     * @return \Chottvn\Finance\Api\Data\RequestInterface
     */
    public function setAmount($amount);

    /**
     * Get note
     * @return string|null
     */
    public function getNote();

    /**
     * Set note
     * @param string $note
     * @return \Chottvn\Finance\Api\Data\RequestInterface
     */
    public function setNote($note);

    /**
     * Get status
     * @return string|null
     */
    public function getStatus();

    /**
     * Set status
     * @param string $status
     * @return \Chottvn\Finance\Api\Data\RequestInterface
     */
    public function setStatus($status);

    /**
     * Get processed_by
     * @return string|null
     */
    public function getProcessedBy();

    /**
     * Set processed_by
     * @param string $processedBy
     * @return \Chottvn\Finance\Api\Data\RequestInterface
     */
    public function setProcessedBy($processedBy);

    /**
     * Get processed_at
     * @return string|null
     */
    public function getProcessedAt();

    /**
     * Set processed_at
     * @param string $processedAt
     * @return \Chottvn\Finance\Api\Data\RequestInterface
     */
    public function setProcessedAt($processedAt);

    /**
     * Get created_at
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Set created_at
     * @param string $createdAt
     * @return \Chottvn\Finance\Api\Data\RequestInterface
     */
    public function setCreatedAt($createdAt);

    /**
     * Get updated_at
     * @return string|null
     */
    public function getUpdatedAt();

    /**
     * Set updated_at
     * @param string $updatedAt
     * @return \Chottvn\Finance\Api\Data\RequestInterface
     */
    public function setUpdatedAt($updatedAt);
}

