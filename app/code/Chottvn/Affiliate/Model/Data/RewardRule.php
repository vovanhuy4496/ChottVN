<?php
/**
 * Copyright © chotructuyen.co All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Chottvn\Affiliate\Model\Data;

use Chottvn\Affiliate\Api\Data\RewardRuleInterface;

class RewardRule extends \Magento\Framework\Api\AbstractExtensibleObject implements RewardRuleInterface
{

    /**
     * Get id
     * @return string|null
     */
    public function getRewardruleId()
    {
        return $this->_get(self::REWARDRULE_ID);
    }

    /**
     * Set id
     * @param string $rewardruleId
     * @return \Chottvn\Affiliate\Api\Data\RewardRuleInterface
     */
    public function setRewardruleId($rewardruleId)
    {
        return $this->setData(self::REWARDRULE_ID, $rewardruleId);
    }

    /**
     * Get name
     * @return string|null
     */
    public function getName()
    {
        return $this->_get(self::NAME);
    }

    /**
     * Set name
     * @param string $name
     * @return \Chottvn\Affiliate\Api\Data\RewardRuleInterface
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Chottvn\Affiliate\Api\Data\RewardRuleExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * Set an extension attributes object.
     * @param \Chottvn\Affiliate\Api\Data\RewardRuleExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Chottvn\Affiliate\Api\Data\RewardRuleExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }

    /**
     * Get status
     * @return string|null
     */
    public function getStatus()
    {
        return $this->_get(self::STATUS);
    }

    /**
     * Set status
     * @param string $status
     * @return \Chottvn\Affiliate\Api\Data\RewardRuleInterface
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * Get start_date
     * @return string|null
     */
    public function getStartDate()
    {
        return $this->_get(self::START_DATE);
    }

    /**
     * Set start_date
     * @param string $startDate
     * @return \Chottvn\Affiliate\Api\Data\RewardRuleInterface
     */
    public function setStartDate($startDate)
    {
        return $this->setData(self::START_DATE, $startDate);
    }

    /**
     * Get end_date
     * @return string|null
     */
    public function getEndDate()
    {
        return $this->_get(self::END_DATE);
    }

    /**
     * Set end_date
     * @param string $endDate
     * @return \Chottvn\Affiliate\Api\Data\RewardRuleInterface
     */
    public function setEndDate($endDate)
    {
        return $this->setData(self::END_DATE, $endDate);
    }

    /**
     * Get reward_level
     * @return string|null
     */
    public function getRewardLevel()
    {
        return $this->_get(self::REWARD_LEVEL);
    }

    /**
     * Set reward_level
     * @param string $rewardLevel
     * @return \Chottvn\Affiliate\Api\Data\RewardRuleInterface
     */
    public function setRewardLevel($rewardLevel)
    {
        return $this->setData(self::REWARD_LEVEL, $rewardLevel);
    }

    /**
     * Get affiliate_level
     * @return string|null
     */
    public function getAffiliateLevel()
    {
        return $this->_get(self::AFFILIATE_LEVEL);
    }

    /**
     * Set affiliate_level
     * @param string $affiliateLevel
     * @return \Chottvn\Affiliate\Api\Data\RewardRuleInterface
     */
    public function setAffiliateLevel($affiliateLevel)
    {
        return $this->setData(self::AFFILIATE_LEVEL, $affiliateLevel);
    }

    /**
     * Get product_brand_id
     * @return string|null
     */
    public function getProductBrandId()
    {
        return $this->_get(self::PRODUCT_BRAND_ID);
    }

    /**
     * Set product_brand_id
     * @param string $productBrandId
     * @return \Chottvn\Affiliate\Api\Data\RewardRuleInterface
     */
    public function setProductBrandId($productBrandId)
    {
        return $this->setData(self::PRODUCT_BRAND_ID, $productBrandId);
    }

    /**
     * Get product_kind
     * @return string|null
     */
    public function getProductKind()
    {
        return $this->_get(self::PRODUCT_KIND);
    }

    /**
     * Set product_kind
     * @param string $productKind
     * @return \Chottvn\Affiliate\Api\Data\RewardRuleInterface
     */
    public function setProductKind($productKind)
    {
        return $this->setData(self::PRODUCT_KIND, $productKind);
    }

    /**
     * Get conditions
     * @return string|null
     */
    public function getConditions()
    {
        return $this->_get(self::CONDITIONS);
    }

    /**
     * Set conditions
     * @param string $conditions
     * @return \Chottvn\Affiliate\Api\Data\RewardRuleInterface
     */
    public function setConditions($conditions)
    {
        return $this->setData(self::CONDITIONS, $conditions);
    }

    /**
     * Get description
     * @return string|null
     */
    public function getDescription()
    {
        return $this->_get(self::DESCRIPTION);
    }

    /**
     * Set description
     * @param string $description
     * @return \Chottvn\Affiliate\Api\Data\RewardRuleInterface
     */
    public function setDescription($description)
    {
        return $this->setData(self::DESCRIPTION, $description);
    }

    /**
     * Get priority
     * @return string|null
     */
    public function getPriority()
    {
        return $this->_get(self::PRIORITY);
    }

    /**
     * Set priority
     * @param string $priority
     * @return \Chottvn\Affiliate\Api\Data\RewardRuleInterface
     */
    public function setPriority($priority)
    {
        return $this->setData(self::PRIORITY, $priority);
    }

    /**
     * Get discard_subsequent_rules
     * @return string|null
     */
    public function getDiscardSubsequentRules()
    {
        return $this->_get(self::DISCARD_SUBSEQUENT_RULES);
    }

    /**
     * Set discard_subsequent_rules
     * @param string $discardSubsequentRules
     * @return \Chottvn\Affiliate\Api\Data\RewardRuleInterface
     */
    public function setDiscardSubsequentRules($discardSubsequentRules)
    {
        return $this->setData(self::DISCARD_SUBSEQUENT_RULES, $discardSubsequentRules);
    }

    /**
     * Get notes
     * @return string|null
     */
    public function getNotes()
    {
        return $this->_get(self::NOTES);
    }

    /**
     * Set notes
     * @param string $notes
     * @return \Chottvn\Affiliate\Api\Data\RewardRuleInterface
     */
    public function setNotes($notes)
    {
        return $this->setData(self::NOTES, $notes);
    }
}

