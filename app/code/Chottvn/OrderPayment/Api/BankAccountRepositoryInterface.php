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

namespace Chottvn\OrderPayment\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Interface BankAccountRepositoryInterface
 *
 * @package Chottvn\OrderPayment\Api
 */
interface BankAccountRepositoryInterface
{

    /**
     * Save BankAccount
     * @param \Chottvn\OrderPayment\Api\Data\BankAccountInterface $bankAccount
     * @return \Chottvn\OrderPayment\Api\Data\BankAccountInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Chottvn\OrderPayment\Api\Data\BankAccountInterface $bankAccount
    );

    /**
     * Retrieve BankAccount
     * @param string $bankaccountId
     * @return \Chottvn\OrderPayment\Api\Data\BankAccountInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($bankaccountId);

    /**
     * Retrieve BankAccount matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Chottvn\OrderPayment\Api\Data\BankAccountSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete BankAccount
     * @param \Chottvn\OrderPayment\Api\Data\BankAccountInterface $bankAccount
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Chottvn\OrderPayment\Api\Data\BankAccountInterface $bankAccount
    );

    /**
     * Delete BankAccount by ID
     * @param string $bankaccountId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($bankaccountId);
}

