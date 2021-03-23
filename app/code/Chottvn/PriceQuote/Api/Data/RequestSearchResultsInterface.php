<?php
/**
 * Copyright © (c) chotructuyen.vn All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Chottvn\PriceQuote\Api\Data;

interface RequestSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get Request list.
     * @return \Chottvn\PriceQuote\Api\Data\RequestInterface[]
     */
    public function getItems();

    /**
     * Set customer_id list.
     * @param \Chottvn\PriceQuote\Api\Data\RequestInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}

