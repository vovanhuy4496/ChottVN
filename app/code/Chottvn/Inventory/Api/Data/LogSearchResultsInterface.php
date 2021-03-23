<?php
/**
 * Copyright © © 2020 chotructuyen.co All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Chottvn\Inventory\Api\Data;

interface LogSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get Log list.
     * @return \Chottvn\Inventory\Api\Data\LogInterface[]
     */
    public function getItems();

    /**
     * Set user_id list.
     * @param \Chottvn\Inventory\Api\Data\LogInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}

