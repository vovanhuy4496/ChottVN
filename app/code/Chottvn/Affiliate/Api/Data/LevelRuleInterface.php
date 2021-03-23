<?php
/**
 * Copyright © chotructuyen.co All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Chottvn\Affiliate\Api\Data;

interface LevelRuleInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{

    const STATUS = 'status';
    const CONDITIONS = 'conditions';
    const END_DATE = 'end_date';
    const DESCRIPTION = 'description';
    const NOTES = 'notes';
    const NAME = 'name';
    const LEVELRULE_ID = 'id';
    const AFFILIATE_LEVEL = 'affiliate_level';
    const START_DATE = 'start_date';
    const PRIORITY = 'priority';
    const DISCARD_SUBSEQUENT_RULES = 'discard_subsequent_rules';

    /**
     * Get id
     * @return string|null
     */
    public function getLevelruleId();

    /**
     * Set id
     * @param string $levelruleId
     * @return \Chottvn\Affiliate\Api\Data\LevelRuleInterface
     */
    public function setLevelruleId($levelruleId);

    /**
     * Get name
     * @return string|null
     */
    public function getName();

    /**
     * Set name
     * @param string $name
     * @return \Chottvn\Affiliate\Api\Data\LevelRuleInterface
     */
    public function setName($name);

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Chottvn\Affiliate\Api\Data\LevelRuleExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     * @param \Chottvn\Affiliate\Api\Data\LevelRuleExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Chottvn\Affiliate\Api\Data\LevelRuleExtensionInterface $extensionAttributes
    );

    /**
     * Get status
     * @return string|null
     */
    public function getStatus();

    /**
     * Set status
     * @param string $status
     * @return \Chottvn\Affiliate\Api\Data\LevelRuleInterface
     */
    public function setStatus($status);

    /**
     * Get start_date
     * @return string|null
     */
    public function getStartDate();

    /**
     * Set start_date
     * @param string $startDate
     * @return \Chottvn\Affiliate\Api\Data\LevelRuleInterface
     */
    public function setStartDate($startDate);

    /**
     * Get end_date
     * @return string|null
     */
    public function getEndDate();

    /**
     * Set end_date
     * @param string $endDate
     * @return \Chottvn\Affiliate\Api\Data\LevelRuleInterface
     */
    public function setEndDate($endDate);

    /**
     * Get affiliate_level
     * @return string|null
     */
    public function getAffiliateLevel();

    /**
     * Set affiliate_level
     * @param string $affiliateLevel
     * @return \Chottvn\Affiliate\Api\Data\LevelRuleInterface
     */
    public function setAffiliateLevel($affiliateLevel);

    /**
     * Get conditions
     * @return string|null
     */
    public function getConditions();

    /**
     * Set conditions
     * @param string $conditions
     * @return \Chottvn\Affiliate\Api\Data\LevelRuleInterface
     */
    public function setConditions($conditions);

    /**
     * Get description
     * @return string|null
     */
    public function getDescription();

    /**
     * Set description
     * @param string $description
     * @return \Chottvn\Affiliate\Api\Data\LevelRuleInterface
     */
    public function setDescription($description);

    /**
     * Get priority
     * @return string|null
     */
    public function getPriority();

    /**
     * Set priority
     * @param string $priority
     * @return \Chottvn\Affiliate\Api\Data\LevelRuleInterface
     */
    public function setPriority($priority);

    /**
     * Get discard_subsequent_rules
     * @return string|null
     */
    public function getDiscardSubsequentRules();

    /**
     * Set discard_subsequent_rules
     * @param string $discardSubsequentRules
     * @return \Chottvn\Affiliate\Api\Data\LevelRuleInterface
     */
    public function setDiscardSubsequentRules($discardSubsequentRules);

    /**
     * Get notes
     * @return string|null
     */
    public function getNotes();

    /**
     * Set notes
     * @param string $notes
     * @return \Chottvn\Affiliate\Api\Data\LevelRuleInterface
     */
    public function setNotes($notes);
}

