<?php
declare(strict_types=1);

namespace Chottvn\Finance\Api\Data;

interface RequestSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get Request list.
     * @return \Chottvn\Finance\Api\Data\RequestInterface[]
     */
    public function getItems();

    /**
     * Set account_id list.
     * @param \Chottvn\Finance\Api\Data\RequestInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}

