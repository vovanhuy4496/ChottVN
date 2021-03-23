<?php
/**
 * Copyright © chotructuyen.co All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Chottvn\Affiliate\Api\Data;

interface LevelRuleSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get LevelRule list.
     * @return \Chottvn\Affiliate\Api\Data\LevelRuleInterface[]
     */
    public function getItems();

    /**
     * Set name list.
     * @param \Chottvn\Affiliate\Api\Data\LevelRuleInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}

