<?php
declare(strict_types=1);

namespace Chottvn\Notification\Model\Data;

use Chottvn\Notification\Api\Data\MessageInterface;

class Message extends \Magento\Framework\Api\AbstractExtensibleObject implements MessageInterface
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
     * @return \Chottvn\Notification\Api\Data\MessageExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * Set an extension attributes object.
     * @param \Chottvn\Notification\Api\Data\MessageExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Chottvn\Notification\Api\Data\MessageExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }

    /**
     * Get messagetype_id
     * @return string|null
     */
    public function getMessagetypeId()
    {
        return $this->_get(self::MESSAGETYPE_ID);
    }

    /**
     * Set messagetype_id
     * @param string $messagetypeId
     * @return \Chottvn\Notification\Api\Data\MessageInterface
     */
    public function setMessagetypeId($messagetypeId)
    {
        return $this->setData(self::MESSAGETYPE_ID, $messagetypeId);
    }

    /**
     * Get created_by
     * @return string|null
     */
    public function getCreatedBy()
    {
        return $this->_get(self::CREATED_BY);
    }

    /**
     * Set created_by
     * @param string $created_by
     * @return \Chottvn\Notification\Api\Data\MessageInterface
     */
    public function setCreatedBy($created_by)
    {
        return $this->setData(self::CREATED_BY, $created_by);
    }

    /**
     * Get customer_group_ids
     * @return string|null
     */
    public function getCustomerGroupIds()
    {
        return $this->_get(self::CUSTOMER_GROUP_IDS);
    }

    /**
     * Set customer_group_ids
     * @param string $customer_group_ids
     * @return \Chottvn\Notification\Api\Data\MessageInterface
     */
    public function setCustomerGroupIds($customer_group_ids)
    {
        return $this->setData(self::CUSTOMER_GROUP_IDS, $customer_group_ids);
    }

    /**
     * Get title
     * @return string|null
     */
    public function getTitle()
    {
        return $this->_get(self::TITLE);
    }

    /**
     * Set title
     * @param string $title
     * @return \Chottvn\Notification\Api\Data\MessageInterface
     */
    public function setTitle($title)
    {
        return $this->setData(self::TITLE, $title);
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
     * Get image
     * @return string|null
     */
    public function getImage()
    {
        return $this->_get(self::IMAGE);
    }

    /**
     * Set image
     * @param string $image
     * @return \Chottvn\Notification\Api\Data\MessageInterface
     */
    public function setImage($image)
    {
        return $this->setData(self::IMAGE, $image);
    }

    /**
     * Get note
     * @return string|null
     */
    public function getNote()
    {
        return $this->_get(self::NOTE);
    }

    /**
     * Set note
     * @param string $note
     * @return \Chottvn\Notification\Api\Data\MessageInterface
     */
    public function setNote($note)
    {
        return $this->setData(self::NOTE, $note);
    }

    /**
     * Get started_at
     * @return string|null
     */
    public function getStartedAt()
    {
        return $this->_get(self::STARTED_AT);
    }

    /**
     * Set started_at
     * @param string $started_at
     * @return \Chottvn\Notification\Api\Data\MessageInterface
     */
    public function setStartedAt($started_at)
    {
        return $this->setData(self::STARTED_AT, $started_at);
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
     * Get taget_url
     * @return string|null
     */
    public function getTagetUrl()
    {
        return $this->_get(self::TAGET_URL);
    }

    /**
     * Set taget_url
     * @param string $taget_url
     * @return \Chottvn\Notification\Api\Data\MessageTypeInterface
     */
    public function setTagetUrl($taget_url)
    {
        return $this->setData(self::TAGET_URL, $taget_url);
    }
}
