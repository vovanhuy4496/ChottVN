<?php
declare(strict_types=1);

namespace Chottvn\Notification\Api\Data;

interface MessageInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    const ID = 'id';
    const MESSAGETYPE_ID = 'messagetype_id';
    const CUSTOMER_GROUP_IDS = 'customer_group_ids';
    const TITLE = 'title';
    const DESCRIPTION = 'description';
    const IMAGE = 'image';
    const TAGET_URL = 'taget_url';
    const NOTE = 'note';
    const CREATED_BY = 'created_by';
    const STARTED_AT = 'started_at';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const DELETED_AT = 'deleted_at';

    /**
     * Get id
     * @return string|null
     */
    public function getId();

    /**
     * Set id
     * @param string $id
     * @return \Chottvn\Notification\Api\Data\MessageInterface
     */
    public function setId($id);

    /**
     * Get created_by
     * @return string|null
     */
    public function getCreatedBy();

    /**
     * Set created_by
     * @param string $created_by
     * @return \Chottvn\Notification\Api\Data\MessageInterface
     */
    public function setCreatedBy($created_by);

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Chottvn\Notification\Api\Data\MessageExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     * @param \Chottvn\Notification\Api\Data\MessageExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Chottvn\Notification\Api\Data\MessageExtensionInterface $extensionAttributes
    );

    /**
     * Get description
     * @return string|null
     */
    public function getDescription();

    /**
     * Set description
     * @param string $description
     * @return \Chottvn\Notification\Api\Data\MessageInterface
     */
    public function setDescription($description);

    /**
     * Get image
     * @return string|null
     */
    public function getImage();

    /**
     * Set image
     * @param string $image
     * @return \Chottvn\Notification\Api\Data\MessageInterface
     */
    public function setImage($image);

    /**
     * Get note
     * @return string|null
     */
    public function getNote();

    /**
     * Set note
     * @param string $note
     * @return \Chottvn\Notification\Api\Data\MessageInterface
     */
    public function setNote($note);

    /**
     * Get started_at
     * @return string|null
     */
    public function getStartedAt();

    /**
     * Set started_at
     * @param string $started_at
     * @return \Chottvn\Notification\Api\Data\MessageInterface
     */
    public function setStartedAt($started_at);

    /**
     * Get messagetype_id
     * @return string|null
     */
    public function getMessagetypeId();

    /**
     * Set messagetype_id
     * @param string $messagetype_id
     * @return \Chottvn\Notification\Api\Data\MessageInterface
     */
    public function setMessagetypeId($messagetype_id);

    /**
     * Get customer_group_ids
     * @return string|null
     */
    public function getCustomerGroupIds();

    /**
     * Set customer_group_ids
     * @param string $customer_group_ids
     * @return \Chottvn\Notification\Api\Data\MessageInterface
     */
    public function setCustomerGroupIds($customer_group_ids);

    /**
     * Get title
     * @return string|null
     */
    public function getTitle();

    /**
     * Set title
     * @param string $title
     * @return \Chottvn\Notification\Api\Data\MessageInterface
     */
    public function setTitle($title);

    /**
     * Get created_at
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Set created_at
     * @param string $createdAt
     * @return \Chottvn\Notification\Api\Data\MessageTypeInterface
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
     * @return \Chottvn\Notification\Api\Data\MessageTypeInterface
     */
    public function setUpdatedAt($updatedAt);

    /**
     * Get deleted_at
     * @return string|null
     */
    public function getDeletedAt();

    /**
     * Set deleted_at
     * @param string $updatedAt
     * @return \Chottvn\Notification\Api\Data\MessageTypeInterface
     */
    public function setDeletedAt($deletedAt);

    /**
     * Get taget_url
     * @return string|null
     */
    public function getTagetUrl();

    /**
     * Set taget_url
     * @param string $updatedAt
     * @return \Chottvn\Notification\Api\Data\MessageTypeInterface
     */
    public function setTagetUrl($taget_url);
}

