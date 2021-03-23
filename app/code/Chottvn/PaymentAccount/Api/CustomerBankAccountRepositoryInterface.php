<?php
/**
 * Copyright © (c) Cho Truc Tuyen 2020 All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Chottvn\PaymentAccount\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface CustomerBankAccountRepositoryInterface
{

    /**
     * Save CustomerBankAccount
     * @param \Chottvn\PaymentAccount\Api\Data\CustomerBankAccountInterface $customerBankAccount
     * @return \Chottvn\PaymentAccount\Api\Data\CustomerBankAccountInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Chottvn\PaymentAccount\Api\Data\CustomerBankAccountInterface $customerBankAccount
    );

    /**
     * Retrieve CustomerBankAccount
     * @param string $customerbankaccountId
     * @return \Chottvn\PaymentAccount\Api\Data\CustomerBankAccountInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($customerbankaccountId);

    /**
     * Retrieve CustomerBankAccount matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Chottvn\PaymentAccount\Api\Data\CustomerBankAccountSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete CustomerBankAccount
     * @param \Chottvn\PaymentAccount\Api\Data\CustomerBankAccountInterface $customerBankAccount
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Chottvn\PaymentAccount\Api\Data\CustomerBankAccountInterface $customerBankAccount
    );

    /**
     * Delete CustomerBankAccount by ID
     * @param string $customerbankaccountId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($customerbankaccountId);
}

