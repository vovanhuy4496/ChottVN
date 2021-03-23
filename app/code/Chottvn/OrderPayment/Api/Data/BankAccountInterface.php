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

namespace Chottvn\OrderPayment\Api\Data;

/**
 * Interface BankAccountInterface
 *
 * @package Chottvn\OrderPayment\Api\Data
 */
interface BankAccountInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    const BANKACCOUNT_ID = 'bankaccount_id';
    const BANK_NAME = 'bank_name';
    const BANK_BRANCH = 'bank_branch';
    const BANK_IMAGE = 'bank_image';    
    const ACCOUNT_OWNER = 'account_owner';
    const ACCOUNT_NUMBER = 'account_number';
    const STATUS = 'status';
    const NOTE = 'note';
    const ORDER = 'order';
    const UPDATED_AT = 'updated_at';
    const CREATED_AT = 'created_at';
    const PATH_MEDIA_BANK = '/bank/web/image/';
     /**
     * Get path image
     * @return string|null
     */
    public function getImagePath();
    /**
     * Get bankaccount_id
     * @return string|null
     */
    public function getBankaccountId();
    /**
     * Set bankaccount_id
     * @param string $bankaccountId
     * @return \Chottvn\OrderPayment\Api\Data\BankAccountInterface
     */
    public function setBankaccountId($bankaccountId);

    /**
     * Get bank_name
     * @return string|null
     */
    public function getBankName();

    /**
     * Set bank_name
     * @param string $bankName
     * @return \Chottvn\OrderPayment\Api\Data\BankAccountInterface
     */
    public function setBankName($bankName);

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Chottvn\OrderPayment\Api\Data\BankAccountExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     * @param \Chottvn\OrderPayment\Api\Data\BankAccountExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Chottvn\OrderPayment\Api\Data\BankAccountExtensionInterface $extensionAttributes
    );

    /**
     * Get bank_image
     * @return string|null
     */
    public function getBankImage();

    /**
     * Set bank_image
     * @param string $bankImage
     * @return \Chottvn\OrderPayment\Api\Data\BankAccountInterface
     */
    public function setBankImage($bankImage);

    /**
     * Get bank_branch
     * @return string|null
     */
    public function getBankBranch();

    /**
     * Set bank_branch
     * @param string $bankBranch
     * @return \Chottvn\OrderPayment\Api\Data\BankAccountInterface
     */
    public function setBankBranch($bankBranch);

    /**
     * Get account_owner
     * @return string|null
     */
    public function getAccountOwner();

    /**
     * Set account_owner
     * @param string $accountOwner
     * @return \Chottvn\OrderPayment\Api\Data\BankAccountInterface
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
     * @return \Chottvn\OrderPayment\Api\Data\BankAccountInterface
     */
    public function setAccountNumber($accountNumber);

    /**
     * Get status
     * @return string|null
     */
    public function getStatus();

    /**
     * Set status
     * @param string $status
     * @return \Chottvn\OrderPayment\Api\Data\BankAccountInterface
     */
    public function setStatus($status);

    /**
     * Get note
     * @return string|null
     */
    public function getNote();

    /**
     * Set note
     * @param string $note
     * @return \Chottvn\OrderPayment\Api\Data\BankAccountInterface
     */
    public function setNote($note);

    /**
     * Get order
     * @return string|null
     */
    public function getOrder();

    /**
     * Set order
     * @param string $order
     * @return \Chottvn\OrderPayment\Api\Data\BankAccountInterface
     */
    public function setOrder($order);

    /**
     * Get created_at
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Set created_at
     * @param string $createdAt
     * @return \Chottvn\OrderPayment\Api\Data\BankAccountInterface
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
     * @return \Chottvn\OrderPayment\Api\Data\BankAccountInterface
     */
    public function setUpdatedAt($updatedAt);
}

