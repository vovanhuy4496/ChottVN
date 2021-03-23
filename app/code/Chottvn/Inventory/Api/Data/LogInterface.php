<?php
/**
 * Copyright © © 2020 chotructuyen.co All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Chottvn\Inventory\Api\Data;

interface LogInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{

    const LOG_TYPE = 'log_type';
    const FILE_NAME = 'file_name';
    const STATUS = 'status';
    const FEATURE_TYPE = 'feature_type';
    const DETAILS = 'details';
    const CREATED_AT = 'created_at';
    const USER_ID = 'user_id';
    const LOG_ID = 'log_id';
    const FILE_ROW_COUNT = 'file_row_count';
    const UPDATED_AT = 'updated_at';
    const AFFECTED_ROW_COUNT = 'affected_row_count';

    /**
     * Get log_id
     * @return string|null
     */
    public function getLogId();

    /**
     * Set log_id
     * @param string $logId
     * @return \Chottvn\Inventory\Api\Data\LogInterface
     */
    public function setLogId($logId);

    /**
     * Get user_id
     * @return string|null
     */
    public function getUserId();

    /**
     * Set user_id
     * @param string $userId
     * @return \Chottvn\Inventory\Api\Data\LogInterface
     */
    public function setUserId($userId);

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Chottvn\Inventory\Api\Data\LogExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     * @param \Chottvn\Inventory\Api\Data\LogExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Chottvn\Inventory\Api\Data\LogExtensionInterface $extensionAttributes
    );

    /**
     * Get log_type
     * @return string|null
     */
    public function getLogType();

    /**
     * Set log_type
     * @param string $logType
     * @return \Chottvn\Inventory\Api\Data\LogInterface
     */
    public function setLogType($logType);

    /**
     * Get file_name
     * @return string|null
     */
    public function getFileName();

    /**
     * Set file_name
     * @param string $fileName
     * @return \Chottvn\Inventory\Api\Data\LogInterface
     */
    public function setFileName($fileName);

    /**
     * Get file_row_count
     * @return string|null
     */
    public function getFileRowCount();

    /**
     * Set file_row_count
     * @param string $fileRowCount
     * @return \Chottvn\Inventory\Api\Data\LogInterface
     */
    public function setFileRowCount($fileRowCount);

    /**
     * Get feature_type
     * @return string|null
     */
    public function getFeatureType();

    /**
     * Set feature_type
     * @param string $featureType
     * @return \Chottvn\Inventory\Api\Data\LogInterface
     */
    public function setFeatureType($featureType);

    /**
     * Get status
     * @return string|null
     */
    public function getStatus();

    /**
     * Set status
     * @param string $status
     * @return \Chottvn\Inventory\Api\Data\LogInterface
     */
    public function setStatus($status);

    /**
     * Get affected_row_count
     * @return string|null
     */
    public function getAffectedRowCount();

    /**
     * Set affected_row_count
     * @param string $affectedRowCount
     * @return \Chottvn\Inventory\Api\Data\LogInterface
     */
    public function setAffectedRowCount($affectedRowCount);

    /**
     * Get details
     * @return string|null
     */
    public function getDetails();

    /**
     * Set details
     * @param string $details
     * @return \Chottvn\Inventory\Api\Data\LogInterface
     */
    public function setDetails($details);

    /**
     * Get created_at
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Set created_at
     * @param string $createdAt
     * @return \Chottvn\Inventory\Api\Data\LogInterface
     */
    public function setCreatedAt($createdAt);

    /**
     * Get updated_at
     * @return string|null
     */
    public function getUpdatedAt();

    /**
     * Set updated_at
     * @param string $updatedAt
     * @return \Chottvn\Inventory\Api\Data\LogInterface
     */
    public function setUpdatedAt($updatedAt);
}

