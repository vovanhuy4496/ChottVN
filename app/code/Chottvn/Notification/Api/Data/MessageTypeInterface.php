<?php
declare(strict_types=1);

namespace Chottvn\Notification\Api\Data;

interface MessageTypeInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    const ID = 'id';
    const NAME = 'name';
    const DESCRIPTION = 'description';
    const CODE = 'code';
    const STATUS = 'status';
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
     * @return \Chottvn\Notification\Api\Data\MessageTypeInterface
     */
    public function setId($id);

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Chottvn\Notification\Api\Data\MessageTypeExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     * @param \Chottvn\Notification\Api\Data\MessageTypeExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Chottvn\Notification\Api\Data\MessageTypeExtensionInterface $extensionAttributes
    );

    /**
     * Get name
     * @return string|null
     */
    public function getName();

    /**
     * Set name
     * @param string $name
     * @return \Chottvn\Notification\Api\Data\MessageTypeInterface
     */
    public function setName($name);

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
     * Get status
     * @return string|null
     */
    public function getStatus();

    /**
     * Set status
     * @param string $status
     * @return \Chottvn\Finance\Api\Data\RequestInterface
     */
    public function setStatus($status);

    /**
     * Get code
     * @return string|null
     */
    public function getCode();

    /**
     * Set code
     * @param string $code
     * @return \Chottvn\Finance\Api\Data\TransactionTypeInterface
     */
    public function setCode($code);
}

