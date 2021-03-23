<?php
/**
 * Copyright © chotructuyen.co All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Chottvn\Affiliate\Api\Data;

interface RewardRuleSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get RewardRule list.
     * @return \Chottvn\Affiliate\Api\Data\RewardRuleInterface[]
     */
    public function getItems();

    /**
     * Set name list.
     * @param \Chottvn\Affiliate\Api\Data\RewardRuleInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}

