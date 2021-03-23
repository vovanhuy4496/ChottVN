<?php
declare(strict_types=1);

namespace Chottvn\Finance\Api\Data;

interface TransactionTypeInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{

    const TRANSACTIONTYPE_ID = 'transactiontype_id';
    const CREATED_AT = 'created_at';
    const DESCRIPTION = 'description';
    const CODE = 'code';
    const NAME = 'name';
    const RATE = 'rate';
    const UPDATED_AT = 'updated_at';

    /**
     * Get transactiontype_id
     * @return string|null
     */
    public function getTransactiontypeId();

    /**
     * Set transactiontype_id
     * @param string $transactiontypeId
     * @return \Chottvn\Finance\Api\Data\TransactionTypeInterface
     */
    public function setTransactiontypeId($transactiontypeId);

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

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Chottvn\Finance\Api\Data\TransactionTypeExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     * @param \Chottvn\Finance\Api\Data\TransactionTypeExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Chottvn\Finance\Api\Data\TransactionTypeExtensionInterface $extensionAttributes
    );

    /**
     * Get rate
     * @return string|null
     */
    public function getRate();

    /**
     * Set rate
     * @param string $rate
     * @return \Chottvn\Finance\Api\Data\TransactionTypeInterface
     */
    public function setRate($rate);

    /**
     * Get name
     * @return string|null
     */
    public function getName();

    /**
     * Set name
     * @param string $name
     * @return \Chottvn\Finance\Api\Data\TransactionTypeInterface
     */
    public function setName($name);

    /**
     * Get description
     * @return string|null
     */
    public function getDescription();

    /**
     * Set description
     * @param string $description
     * @return \Chottvn\Finance\Api\Data\TransactionTypeInterface
     */
    public function setDescription($description);

    /**
     * Get created_at
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Set created_at
     * @param string $createdAt
     * @return \Chottvn\Finance\Api\Data\TransactionTypeInterface
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
     * @return \Chottvn\Finance\Api\Data\TransactionTypeInterface
     */
    public function setUpdatedAt($updatedAt);
}

