<?php
declare(strict_types=1);

namespace Chottvn\Finance\Api\Data;

interface TransactionSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get Transaction list.
     * @return \Chottvn\Finance\Api\Data\TransactionInterface[]
     */
    public function getItems();

    /**
     * Set account_id list.
     * @param \Chottvn\Finance\Api\Data\TransactionInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}

