<?php
/**
 * Copyright © (c) Cho Truc Tuyen 2020 All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Chottvn\PaymentAccount\Model\Data;

use Chottvn\PaymentAccount\Api\Data\BankInterface;

class Bank extends \Magento\Framework\Api\AbstractExtensibleObject implements BankInterface
{

    /**
     * Get bank_id
     * @return string|null
     */
    public function getBankId()
    {
        return $this->_get(self::BANK_ID);
    }

    /**
     * Set bank_id
     * @param string $bankId
     * @return \Chottvn\PaymentAccount\Api\Data\BankInterface
     */
    public function setBankId($bankId)
    {
        return $this->setData(self::BANK_ID, $bankId);
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
     * @return \Chottvn\PaymentAccount\Api\Data\BankInterface
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Chottvn\PaymentAccount\Api\Data\BankExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * Set an extension attributes object.
     * @param \Chottvn\PaymentAccount\Api\Data\BankExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Chottvn\PaymentAccount\Api\Data\BankExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
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
     * @return \Chottvn\PaymentAccount\Api\Data\BankInterface
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
     * @return \Chottvn\PaymentAccount\Api\Data\BankInterface
     */
    public function setImage($image)
    {
        return $this->setData(self::IMAGE, $image);
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
     * @return \Chottvn\PaymentAccount\Api\Data\BankInterface
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * Get order
     * @return string|null
     */
    public function getOrder()
    {
        return $this->_get(self::ORDER);
    }

    /**
     * Set order
     * @param string $order
     * @return \Chottvn\PaymentAccount\Api\Data\BankInterface
     */
    public function setOrder($order)
    {
        return $this->setData(self::ORDER, $order);
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
     * @return \Chottvn\PaymentAccount\Api\Data\BankInterface
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
     * @return \Chottvn\PaymentAccount\Api\Data\BankInterface
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }

    /**
     * Get name_short
     * @return string|null
     */
    public function getNameShort()
    {
        return $this->_get(self::NAME_SHORT);
    }

    /**
     * Set name_short
     * @param string $nameShort
     * @return \Chottvn\PaymentAccount\Api\Data\BankInterface
     */
    public function setNameShort($nameShort)
    {
        return $this->setData(self::NAME_SHORT, $nameShort);
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
     * @return \Chottvn\PaymentAccount\Api\Data\BankInterface
     */
    public function setCode($code)
    {
        return $this->setData(self::CODE, $code);
    }
}

