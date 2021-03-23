<?php
/**
 * Copyright © (c) Cho Truc Tuyen 2020 All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Chottvn\PaymentAccount\Api\Data;

interface BankSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get Bank list.
     * @return \Chottvn\PaymentAccount\Api\Data\BankInterface[]
     */
    public function getItems();

    /**
     * Set name list.
     * @param \Chottvn\PaymentAccount\Api\Data\BankInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}

