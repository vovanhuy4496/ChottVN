<?php
/**
 * Copyright © (c) Cho Truc Tuyen 2020 All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Chottvn\PaymentAccount\Api\Data;

interface CustomerBankAccountInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{

    const UPDATED_AT = 'updated_at';
    const CUSTOMER_ID = 'customer_id';
    const ACCOUNT_NUMBER = 'account_number';
    const STATUS = 'status';
    const customerba_id = 'customerba_id';
    const ACCOUNT_OWNER = 'account_owner';
    const ORDER = 'order';
    const NOTE = 'note';
    const PAYMENTACCOUNT_BANK_ID = 'paymentaccount_bank_id';
    const BANK_BRANCH = 'bank_branch';
    const CREATED_AT = 'created_at';

    /**
     * Get customerba_id
     * @return string|null
     */
    public function getCustomerbankaccountId();

    /**
     * Set customerba_id
     * @param string $customerbankaccountId
     * @return \Chottvn\PaymentAccount\Api\Data\CustomerBankAccountInterface
     */
    public function setCustomerbankaccountId($customerbankaccountId);

    /**
     * Get customer_id
     * @return string|null
     */
    public function getCustomerId();

    /**
     * Set customer_id
     * @param string $customerId
     * @return \Chottvn\PaymentAccount\Api\Data\CustomerBankAccountInterface
     */
    public function setCustomerId($customerId);

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Chottvn\PaymentAccount\Api\Data\CustomerBankAccountExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     * @param \Chottvn\PaymentAccount\Api\Data\CustomerBankAccountExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Chottvn\PaymentAccount\Api\Data\CustomerBankAccountExtensionInterface $extensionAttributes
    );

    /**
     * Get paymentaccount_bank_id
     * @return string|null
     */
    public function getPaymentAccountBankId();

    /**
     * Set paymentaccount_bank_id
     * @param string $paymentAccountBankId
     * @return \Chottvn\PaymentAccount\Api\Data\CustomerBankAccountInterface
     */
    public function setPaymentAccountBankId($paymentAccountBankId);

    /**
     * Get account_owner
     * @return string|null
     */
    public function getAccountOwner();

    /**
     * Set account_owner
     * @param string $accountOwner
     * @return \Chottvn\PaymentAccount\Api\Data\CustomerBankAccountInterface
     */
    public function setAccountOwner($accountOwner);

    /**
     * Get account_number
     * @return string|null
     */
    public function getAccountNumber();

    /**
     * Set account_number
     * @param string $accountNumber
     * @return \Chottvn\PaymentAccount\Api\Data\CustomerBankAccountInterface
     */
    public function setAccountNumber($accountNumber);

    /**
     * Get bank_branch
     * @return string|null
     */
    public function getBrankBranch();

    /**
     * Set bank_branch
     * @param string $brankBranch
     * @return \Chottvn\PaymentAccount\Api\Data\CustomerBankAccountInterface
     */
    public function setBrankBranch($brankBranch);

    /**
     * Get order
     * @return string|null
     */
    public function getOrder();

    /**
     * Set order
     * @param string $order
     * @return \Chottvn\PaymentAccount\Api\Data\CustomerBankAccountInterface
     */
    public function setOrder($order);

    /**
     * Get note
     * @return string|null
     */
    public function getNote();

    /**
     * Set note
     * @param string $note
     * @return \Chottvn\PaymentAccount\Api\Data\CustomerBankAccountInterface
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
     * @return \Chottvn\PaymentAccount\Api\Data\CustomerBankAccountInterface
     */
    public function setStatus($status);

    /**
     * Get created_at
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Set created_at
     * @param string $createdAt
     * @return \Chottvn\PaymentAccount\Api\Data\CustomerBankAccountInterface
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
     * @return \Chottvn\PaymentAccount\Api\Data\CustomerBankAccountInterface
     */
    public function setUpdatedAt($updatedAt);
}

