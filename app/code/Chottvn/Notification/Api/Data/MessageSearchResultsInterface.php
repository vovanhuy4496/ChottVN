<?php
declare(strict_types=1);

namespace Chottvn\Notification\Api\Data;

interface MessageSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get Message list.
     * @return \Chottvn\Notification\Api\Data\MessageInterface[]
     */
    public function getItems();

    /**
     * Set Message list.
     * @param \Chottvn\Notification\Api\Data\MessageInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}

