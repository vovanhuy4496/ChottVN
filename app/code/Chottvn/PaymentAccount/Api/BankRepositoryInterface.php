<?php
/**
 * Copyright © (c) Cho Truc Tuyen 2020 All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Chottvn\PaymentAccount\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface BankRepositoryInterface
{

    /**
     * Save Bank
     * @param \Chottvn\PaymentAccount\Api\Data\BankInterface $bank
     * @return \Chottvn\PaymentAccount\Api\Data\BankInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Chottvn\PaymentAccount\Api\Data\BankInterface $bank
    );

    /**
     * Retrieve Bank
     * @param string $bankId
     * @return \Chottvn\PaymentAccount\Api\Data\BankInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($bankId);

    /**
     * Retrieve Bank matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Chottvn\PaymentAccount\Api\Data\BankSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete Bank
     * @param \Chottvn\PaymentAccount\Api\Data\BankInterface $bank
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Chottvn\PaymentAccount\Api\Data\BankInterface $bank
    );

    /**
     * Delete Bank by ID
     * @param string $bankId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($bankId);
}

