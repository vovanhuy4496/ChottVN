<?php
/**
 * Copyright © © 2020 chotructuyen.co All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Chottvn\Inventory\Model\Data;

use Chottvn\Inventory\Api\Data\LogInterface;

class Log extends \Magento\Framework\Api\AbstractExtensibleObject implements LogInterface
{

    /**
     * Get log_id
     * @return string|null
     */
    public function getLogId()
    {
        return $this->_get(self::LOG_ID);
    }

    /**
     * Set log_id
     * @param string $logId
     * @return \Chottvn\Inventory\Api\Data\LogInterface
     */
    public function setLogId($logId)
    {
        return $this->setData(self::LOG_ID, $logId);
    }

    /**
     * Get user_id
     * @return string|null
     */
    public function getUserId()
    {
        return $this->_get(self::USER_ID);
    }

    /**
     * Set user_id
     * @param string $userId
     * @return \Chottvn\Inventory\Api\Data\LogInterface
     */
    public function setUserId($userId)
    {
        return $this->setData(self::USER_ID, $userId);
    }

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Chottvn\Inventory\Api\Data\LogExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * Set an extension attributes object.
     * @param \Chottvn\Inventory\Api\Data\LogExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Chottvn\Inventory\Api\Data\LogExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }

    /**
     * Get log_type
     * @return string|null
     */
    public function getLogType()
    {
        return $this->_get(self::LOG_TYPE);
    }

    /**
     * Set log_type
     * @param string $logType
     * @return \Chottvn\Inventory\Api\Data\LogInterface
     */
    public function setLogType($logType)
    {
        return $this->setData(self::LOG_TYPE, $logType);
    }

    /**
     * Get file_name
     * @return string|null
     */
    public function getFileName()
    {
        return $this->_get(self::FILE_NAME);
    }

    /**
     * Set file_name
     * @param string $fileName
     * @return \Chottvn\Inventory\Api\Data\LogInterface
     */
    public function setFileName($fileName)
    {
        return $this->setData(self::FILE_NAME, $fileName);
    }

    /**
     * Get file_row_count
     * @return string|null
     */
    public function getFileRowCount()
    {
        return $this->_get(self::FILE_ROW_COUNT);
    }

    /**
     * Set file_row_count
     * @param string $fileRowCount
     * @return \Chottvn\Inventory\Api\Data\LogInterface
     */
    public function setFileRowCount($fileRowCount)
    {
        return $this->setData(self::FILE_ROW_COUNT, $fileRowCount);
    }

    /**
     * Get feature_type
     * @return string|null
     */
    public function getFeatureType()
    {
        return $this->_get(self::FEATURE_TYPE);
    }

    /**
     * Set feature_type
     * @param string $featureType
     * @return \Chottvn\Inventory\Api\Data\LogInterface
     */
    public function setFeatureType($featureType)
    {
        return $this->setData(self::FEATURE_TYPE, $featureType);
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
     * @return \Chottvn\Inventory\Api\Data\LogInterface
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * Get affected_row_count
     * @return string|null
     */
    public function getAffectedRowCount()
    {
        return $this->_get(self::AFFECTED_ROW_COUNT);
    }

    /**
     * Set affected_row_count
     * @param string $affectedRowCount
     * @return \Chottvn\Inventory\Api\Data\LogInterface
     */
    public function setAffectedRowCount($affectedRowCount)
    {
        return $this->setData(self::AFFECTED_ROW_COUNT, $affectedRowCount);
    }

    /**
     * Get details
     * @return string|null
     */
    public function getDetails()
    {
        return $this->_get(self::DETAILS);
    }

    /**
     * Set details
     * @param string $details
     * @return \Chottvn\Inventory\Api\Data\LogInterface
     */
    public function setDetails($details)
    {
        return $this->setData(self::DETAILS, $details);
    }

    /**
     * Get created_at
     * @return string|null
     */
    public function getCreatedAt()
    {
        return $this->_get(self::CREATED_AT);
    }

    /**
     * Set created_at
     * @param string $createdAt
     * @return \Chottvn\Inventory\Api\Data\LogInterface
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * Get updated_at
     * @return string|null
     */
    public function getUpdatedAt()
    {
        return $this->_get(self::UPDATED_AT);
    }

    /**
     * Set updated_at
     * @param string $updatedAt
     * @return \Chottvn\Inventory\Api\Data\LogInterface
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }
}

