<?php
declare(strict_types=1);

namespace Chottvn\Finance\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface TransactionTypeRepositoryInterface
{

    /**
     * Save TransactionType
     * @param \Chottvn\Finance\Api\Data\TransactionTypeInterface $transactionType
     * @return \Chottvn\Finance\Api\Data\TransactionTypeInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Chottvn\Finance\Api\Data\TransactionTypeInterface $transactionType
    );

    /**
     * Retrieve TransactionType
     * @param string $transactiontypeId
     * @return \Chottvn\Finance\Api\Data\TransactionTypeInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($transactiontypeId);

    /**
     * Retrieve TransactionType matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Chottvn\Finance\Api\Data\TransactionTypeSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete TransactionType
     * @param \Chottvn\Finance\Api\Data\TransactionTypeInterface $transactionType
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Chottvn\Finance\Api\Data\TransactionTypeInterface $transactionType
    );

    /**
     * Delete TransactionType by ID
     * @param string $transactiontypeId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($transactiontypeId);
}

