<?php
/**
 * Copyright © (c) Cho Truc Tuyen 2020 All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Chottvn\PaymentAccount\Api\Data;

interface CustomerBankAccountSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get CustomerBankAccount list.
     * @return \Chottvn\PaymentAccount\Api\Data\CustomerBankAccountInterface[]
     */
    public function getItems();

    /**
     * Set customer_id list.
     * @param \Chottvn\PaymentAccount\Api\Data\CustomerBankAccountInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}

