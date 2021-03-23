<?php
/**
 * Copyright © (c) Cho Truc Tuyen 2020 All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Chottvn\PaymentAccount\Model\Data;

use Chottvn\PaymentAccount\Api\Data\CustomerBankAccountInterface;

class CustomerBankAccount extends \Magento\Framework\Api\AbstractExtensibleObject implements CustomerBankAccountInterface
{

    /**
     * Get customerba_id
     * @return string|null
     */
    public function getCustomerbankaccountId()
    {
        return $this->_get(self::customerba_id);
    }

    /**
     * Set customerba_id
     * @param string $customerbankaccountId
     * @return \Chottvn\PaymentAccount\Api\Data\CustomerBankAccountInterface
     */
    public function setCustomerbankaccountId($customerbankaccountId)
    {
        return $this->setData(self::customerba_id, $customerbankaccountId);
    }

    /**
     * Get customer_id
     * @return string|null
     */
    public function getCustomerId()
    {
        return $this->_get(self::CUSTOMER_ID);
    }

    /**
     * Set customer_id
     * @param string $customerId
     * @return \Chottvn\PaymentAccount\Api\Data\CustomerBankAccountInterface
     */
    public function setCustomerId($customerId)
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Chottvn\PaymentAccount\Api\Data\CustomerBankAccountExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * Set an extension attributes object.
     * @param \Chottvn\PaymentAccount\Api\Data\CustomerBankAccountExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Chottvn\PaymentAccount\Api\Data\CustomerBankAccountExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }

    /**
     * Get paymentaccount_bank_id
     * @return string|null
     */
    public function getPaymentAccountBankId()
    {
        return $this->_get(self::PAYMENTACCOUNT_BANK_ID);
    }

    /**
     * Set paymentaccount_bank_id
     * @param string $paymentAccountBankId
     * @return \Chottvn\PaymentAccount\Api\Data\CustomerBankAccountInterface
     */
    public function setPaymentAccountBankId($paymentAccountBankId)
    {
        return $this->setData(self::PAYMENTACCOUNT_BANK_ID, $paymentAccountBankId);
    }

    /**
     * Get account_owner
     * @return string|null
     */
    public function getAccountOwner()
    {
        return $this->_get(self::ACCOUNT_OWNER);
    }

    /**
     * Set account_owner
     * @param string $accountOwner
     * @return \Chottvn\PaymentAccount\Api\Data\CustomerBankAccountInterface
     */
    public function setAccountOwner($accountOwner)
    {
        return $this->setData(self::ACCOUNT_OWNER, $accountOwner);
    }

    /**
     * Get account_number
     * @return string|null
     */
    public function getAccountNumber()
    {
        return $this->_get(self::ACCOUNT_NUMBER);
    }

    /**
     * Set account_number
     * @param string $accountNumber
     * @return \Chottvn\PaymentAccount\Api\Data\CustomerBankAccountInterface
     */
    public function setAccountNumber($accountNumber)
    {
        return $this->setData(self::ACCOUNT_NUMBER, $accountNumber);
    }

    /**
     * Get bank_branch
     * @return string|null
     */
    public function getBrankBranch()
    {
        return $this->_get(self::BRANK_BRANCH);
    }

    /**
     * Set bank_branch
     * @param string $brankBranch
     * @return \Chottvn\PaymentAccount\Api\Data\CustomerBankAccountInterface
     */
    public function setBrankBranch($brankBranch)
    {
        return $this->setData(self::BRANK_BRANCH, $brankBranch);
    }

    /**
     * Get order
     * @return string|null
     */
    public function getOrder()
    {
        return $this->_get(self::ORDER);
    }

    /**
     * Set order
     * @param string $order
     * @return \Chottvn\PaymentAccount\Api\Data\CustomerBankAccountInterface
     */
    public function setOrder($order)
    {
        return $this->setData(self::ORDER, $order);
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
     * @return \Chottvn\PaymentAccount\Api\Data\CustomerBankAccountInterface
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
     * @return \Chottvn\PaymentAccount\Api\Data\CustomerBankAccountInterface
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
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
     * @return \Chottvn\PaymentAccount\Api\Data\CustomerBankAccountInterface
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
     * @return \Chottvn\PaymentAccount\Api\Data\CustomerBankAccountInterface
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }


    /**
     * Get cCustomerName
     * @return string|null
     */
    public function getCustomerName()
    {
        return $this->_get("customer_firstname");
    }
    /**
     * Get cCustomerName
     * @return string|null
     */
    public function getBankName()
    {
        return $this->_get("bank_name");
    }
}

