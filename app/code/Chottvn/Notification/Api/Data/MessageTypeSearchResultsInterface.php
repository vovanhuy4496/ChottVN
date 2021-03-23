<?php
declare(strict_types=1);

namespace Chottvn\Notification\Api\Data;

interface MessageTypeSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get MessageType list.
     * @return \Chottvn\Notification\Api\Data\MessageTypeInterface[]
     */
    public function getItems();

    /**
     * Set code list.
     * @param \Chottvn\Notification\Api\Data\MessageTypeInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}

