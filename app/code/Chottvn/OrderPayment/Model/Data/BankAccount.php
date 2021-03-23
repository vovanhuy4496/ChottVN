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

namespace Chottvn\OrderPayment\Model\Data;

use Chottvn\OrderPayment\Api\Data\BankAccountInterface;

/**
 * Class BankAccount
 *
 * @package Chottvn\OrderPayment\Model\Data
 */
class BankAccount extends \Magento\Framework\Api\AbstractExtensibleObject implements BankAccountInterface
{

    /**
     * Get bankaccount_id
     * @return string|null
     */
    public function getBankaccountId()
    {
        return $this->_get(self::BANKACCOUNT_ID);
    }

    /**
     * Set bankaccount_id
     * @param string $bankaccountId
     * @return \Chottvn\OrderPayment\Api\Data\BankAccountInterface
     */
    public function setBankaccountId($bankaccountId)
    {
        return $this->setData(self::BANKACCOUNT_ID, $bankaccountId);
    }

    /**
     * Get bank_name
     * @return string|null
     */
    public function getBankName()
    {
        return $this->_get(self::BANK_NAME);
    }

    /**
     * Set bank_name
     * @param string $bankName
     * @return \Chottvn\OrderPayment\Api\Data\BankAccountInterface
     */
    public function setBankName($bankName)
    {
        return $this->setData(self::BANK_NAME, $bankName);
    }

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Chottvn\OrderPayment\Api\Data\BankAccountExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * Set an extension attributes object.
     * @param \Chottvn\OrderPayment\Api\Data\BankAccountExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Chottvn\OrderPayment\Api\Data\BankAccountExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
    /**
     * Get path image
     * @return string|null
     */
    public function getImagePath()
    {
        return self::PATH_MEDIA_BANK;
    }
    /**
     * Get bank_image
     * @return string|null
     */
    public function getBankImage()
    {
        return $this->_get(self::BANK_IMAGE);
    }

    /**
     * Set bank_image
     * @param string $bankImage
     * @return \Chottvn\OrderPayment\Api\Data\BankAccountInterface
     */
    public function setBankImage($bankImage)
    {
        return $this->setData(self::BANK_IMAGE, $bankImage);
    }

    /**
     * Get bank_branch
     * @return string|null
     */
    public function getBankBranch()
    {
        return $this->_get(self::BANK_BRANCH);
    }

    /**
     * Set bank_branch
     * @param string $bankBranch
     * @return \Chottvn\OrderPayment\Api\Data\BankAccountInterface
     */
    public function setBankBranch($bankBranch)
    {
        return $this->setData(self::BANK_BRANCH, $bankBranch);
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
     * @return \Chottvn\OrderPayment\Api\Data\BankAccountInterface
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
     * @return \Chottvn\OrderPayment\Api\Data\BankAccountInterface
     */
    public function setAccountNumber($accountNumber)
    {
        return $this->setData(self::ACCOUNT_NUMBER, $accountNumber);
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
     * @return \Chottvn\OrderPayment\Api\Data\BankAccountInterface
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
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
     * @return \Chottvn\OrderPayment\Api\Data\BankAccountInterface
     */
    public function setNote($note)
    {
        return $this->setData(self::NOTE, $note);
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
     * @return \Chottvn\OrderPayment\Api\Data\BankAccountInterface
     */
    public function setOrder($order)
    {
        return $this->setData(self::ORDER, $order);
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
     * @return \Chottvn\OrderPayment\Api\Data\BankAccountInterface
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
     * @return \Chottvn\OrderPayment\Api\Data\BankAccountInterface
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }
}

