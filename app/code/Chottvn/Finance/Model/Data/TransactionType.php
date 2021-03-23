<?php
declare(strict_types=1);

namespace Chottvn\Finance\Model\Data;

use Chottvn\Finance\Api\Data\TransactionTypeInterface;

class TransactionType extends \Magento\Framework\Api\AbstractExtensibleObject implements TransactionTypeInterface
{

    /**
     * Get transactiontype_id
     * @return string|null
     */
    public function getTransactiontypeId()
    {
        return $this->_get(self::TRANSACTIONTYPE_ID);
    }

    /**
     * Set transactiontype_id
     * @param string $transactiontypeId
     * @return \Chottvn\Finance\Api\Data\TransactionTypeInterface
     */
    public function setTransactiontypeId($transactiontypeId)
    {
        return $this->setData(self::TRANSACTIONTYPE_ID, $transactiontypeId);
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

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Chottvn\Finance\Api\Data\TransactionTypeExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * Set an extension attributes object.
     * @param \Chottvn\Finance\Api\Data\TransactionTypeExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Chottvn\Finance\Api\Data\TransactionTypeExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }

    /**
     * Get rate
     * @return string|null
     */
    public function getRate()
    {
        return $this->_get(self::RATE);
    }

    /**
     * Set rate
     * @param string $rate
     * @return \Chottvn\Finance\Api\Data\TransactionTypeInterface
     */
    public function setRate($rate)
    {
        return $this->setData(self::RATE, $rate);
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
     * @return \Chottvn\Finance\Api\Data\TransactionTypeInterface
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
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
     * @return \Chottvn\Finance\Api\Data\TransactionTypeInterface
     */
    public function setDescription($description)
    {
        return $this->setData(self::DESCRIPTION, $description);
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
     * @return \Chottvn\Finance\Api\Data\TransactionTypeInterface
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
     * @return \Chottvn\Finance\Api\Data\TransactionTypeInterface
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }
}

