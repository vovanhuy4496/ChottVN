<?php
/**
 * Copyright © chotructuyen.co All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Chottvn\Affiliate\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface LevelRuleRepositoryInterface
{

    /**
     * Save LevelRule
     * @param \Chottvn\Affiliate\Api\Data\LevelRuleInterface $levelRule
     * @return \Chottvn\Affiliate\Api\Data\LevelRuleInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Chottvn\Affiliate\Api\Data\LevelRuleInterface $levelRule
    );

    /**
     * Retrieve LevelRule
     * @param string $levelruleId
     * @return \Chottvn\Affiliate\Api\Data\LevelRuleInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($levelruleId);

    /**
     * Retrieve LevelRule matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Chottvn\Affiliate\Api\Data\LevelRuleSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete LevelRule
     * @param \Chottvn\Affiliate\Api\Data\LevelRuleInterface $levelRule
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Chottvn\Affiliate\Api\Data\LevelRuleInterface $levelRule
    );

    /**
     * Delete LevelRule by ID
     * @param string $levelruleId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($levelruleId);
}

