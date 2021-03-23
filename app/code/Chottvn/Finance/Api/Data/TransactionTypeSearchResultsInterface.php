<?php
declare(strict_types=1);

namespace Chottvn\Finance\Api\Data;

interface TransactionTypeSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get TransactionType list.
     * @return \Chottvn\Finance\Api\Data\TransactionTypeInterface[]
     */
    public function getItems();

    /**
     * Set code list.
     * @param \Chottvn\Finance\Api\Data\TransactionTypeInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}

