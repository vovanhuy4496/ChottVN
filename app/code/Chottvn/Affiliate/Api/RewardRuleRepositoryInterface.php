<?php
/**
 * Copyright © chotructuyen.co All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Chottvn\Affiliate\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface RewardRuleRepositoryInterface
{

    /**
     * Save RewardRule
     * @param \Chottvn\Affiliate\Api\Data\RewardRuleInterface $rewardRule
     * @return \Chottvn\Affiliate\Api\Data\RewardRuleInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Chottvn\Affiliate\Api\Data\RewardRuleInterface $rewardRule
    );

    /**
     * Retrieve RewardRule
     * @param string $rewardruleId
     * @return \Chottvn\Affiliate\Api\Data\RewardRuleInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($rewardruleId);

    /**
     * Retrieve RewardRule matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Chottvn\Affiliate\Api\Data\RewardRuleSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete RewardRule
     * @param \Chottvn\Affiliate\Api\Data\RewardRuleInterface $rewardRule
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Chottvn\Affiliate\Api\Data\RewardRuleInterface $rewardRule
    );

    /**
     * Delete RewardRule by ID
     * @param string $rewardruleId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($rewardruleId);
}

