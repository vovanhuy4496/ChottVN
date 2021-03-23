<?php
declare(strict_types=1);

namespace Chottvn\Notification\Model\Data;

use Chottvn\Notification\Api\Data\MessageTypeInterface;

class MessageType extends \Magento\Framework\Api\AbstractExtensibleObject implements MessageTypeInterface
{

    /**
     * Get id
     * @return string|null
     */
    public function getId()
    {
        return $this->_get(self::ID);
    }

    /**
     * Set id
     * @param string $id
     * @return \Chottvn\Notification\Api\Data\MessageInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }
    
    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Chottvn\Notification\Api\Data\MessageTypeExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * Set an extension attributes object.
     * @param \Chottvn\Notification\Api\Data\MessageTypeExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Chottvn\Notification\Api\Data\MessageTypeExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
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
     * @return \Chottvn\Notification\Api\Data\MessageTypeInterface
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
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
     * @return \Chottvn\Notification\Api\Data\MessageTypeInterface
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
     * @return \Chottvn\Notification\Api\Data\MessageTypeInterface
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
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
     * @return \Chottvn\Notification\Api\Data\MessageInterface
     */
    public function setDescription($description)
    {
        return $this->setData(self::DESCRIPTION, $description);
    }

    /**
     * Get deleted_at
     * @return string|null
     */
    public function getDeletedAt()
    {
        return $this->_get(self::DELETED_AT);
    }

    /**
     * Set deleted_at
     * @param string $deletedAt
     * @return \Chottvn\Notification\Api\Data\MessageTypeInterface
     */
    public function setDeletedAt($deletedAt)
    {
        return $this->setData(self::DELETED_AT, $deletedAt);
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
     * @return \Chottvn\Finance\Api\Data\RequestInterface
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * Get code
     * @return string|null
     */
    public function getCode()
    {
        return $this->_get(self::CODE);
    }

    /**
     * Set code
     * @param string $code
     * @return \Chottvn\Finance\Api\Data\TransactionTypeInterface
     */
    public function setCode($code)
    {
        return $this->setData(self::CODE, $code);
    }
}

