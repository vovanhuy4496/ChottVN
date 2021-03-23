<?php
declare(strict_types=1);

namespace Chottvn\Finance\Api\Data;

interface TransactionInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{

    const REQUEST_ID = 'request_id';
    const END_DATE = 'end_date';
    const ACCOUNT_ID = 'account_id';
    const TRANSACTION_DATE = 'transaction_date';
    const NOTE = 'note';
    const TRANSACTION_TYPE_ID = 'transaction_type_id';
    const TRANSACTION_ID = 'transaction_id';
    const AMOUNT = 'amount';
    const RATE = 'rate';
    const START_DATE = 'start_date';

    /**
     * Get transaction_id
     * @return string|null
     */
    public function getTransactionId();

    /**
     * Set transaction_id
     * @param string $transactionId
     * @return \Chottvn\Finance\Api\Data\TransactionInterface
     */
    public function setTransactionId($transactionId);

    /**
     * Get account_id
     * @return string|null
     */
    public function getAccountId();

    /**
     * Set account_id
     * @param string $accountId
     * @return \Chottvn\Finance\Api\Data\TransactionInterface
     */
    public function setAccountId($accountId);

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Chottvn\Finance\Api\Data\TransactionExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     * @param \Chottvn\Finance\Api\Data\TransactionExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Chottvn\Finance\Api\Data\TransactionExtensionInterface $extensionAttributes
    );

    /**
     * Get request_id
     * @return string|null
     */
    public function getRequestId();

    /**
     * Set request_id
     * @param string $requestId
     * @return \Chottvn\Finance\Api\Data\TransactionInterface
     */
    public function setRequestId($requestId);

    /**
     * Get transaction_type_id
     * @return string|null
     */
    public function getTransactionTypeId();

    /**
     * Set transaction_type_id
     * @param string $transactionTypeId
     * @return \Chottvn\Finance\Api\Data\TransactionInterface
     */
    public function setTransactionTypeId($transactionTypeId);

    /**
     * Get amount
     * @return string|null
     */
    public function getAmount();

    /**
     * Set amount
     * @param string $amount
     * @return \Chottvn\Finance\Api\Data\TransactionInterface
     */
    public function setAmount($amount);

    /**
     * Get rate
     * @return string|null
     */
    public function getRate();

    /**
     * Set rate
     * @param string $rate
     * @return \Chottvn\Finance\Api\Data\TransactionInterface
     */
    public function setRate($rate);

    /**
     * Get start_date
     * @return string|null
     */
    public function getStartDate();

    /**
     * Set start_date
     * @param string $startDate
     * @return \Chottvn\Finance\Api\Data\TransactionInterface
     */
    public function setStartDate($startDate);

    /**
     * Get end_date
     * @return string|null
     */
    public function getEndDate();

    /**
     * Set end_date
     * @param string $endDate
     * @return \Chottvn\Finance\Api\Data\TransactionInterface
     */
    public function setEndDate($endDate);

    /**
     * Get transaction_date
     * @return string|null
     */
    public function getTransactionDate();

    /**
     * Set transaction_date
     * @param string $transactionDate
     * @return \Chottvn\Finance\Api\Data\TransactionInterface
     */
    public function setTransactionDate($transactionDate);

    /**
     * Get note
     * @return string|null
     */
    public function getNote();

    /**
     * Set note
     * @param string $note
     * @return \Chottvn\Finance\Api\Data\TransactionInterface
     */
    public function setNote($note);
}

