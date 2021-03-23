<?php
/**
 * Copyright © (c) Cho Truc Tuyen 2020 All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Chottvn\PaymentAccount\Api\Data;

interface BankInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{

    const UPDATED_AT = 'updated_at';
    const CODE = 'code';
    const STATUS = 'status';
    const NAME_SHORT = 'name_short';
    const NAME = 'name';
    const ORDER = 'order';
    const BANK_ID = 'bank_id';
    const DESCRIPTION = 'description';
    const IMAGE = 'image';
    const CREATED_AT = 'created_at';

    /**
     * Get bank_id
     * @return string|null
     */
    public function getBankId();

    /**
     * Set bank_id
     * @param string $bankId
     * @return \Chottvn\PaymentAccount\Api\Data\BankInterface
     */
    public function setBankId($bankId);

    /**
     * Get name
     * @return string|null
     */
    public function getName();

    /**
     * Set name
     * @param string $name
     * @return \Chottvn\PaymentAccount\Api\Data\BankInterface
     */
    public function setName($name);

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Chottvn\PaymentAccount\Api\Data\BankExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     * @param \Chottvn\PaymentAccount\Api\Data\BankExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Chottvn\PaymentAccount\Api\Data\BankExtensionInterface $extensionAttributes
    );

    /**
     * Get description
     * @return string|null
     */
    public function getDescription();

    /**
     * Set description
     * @param string $description
     * @return \Chottvn\PaymentAccount\Api\Data\BankInterface
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
     * @return \Chottvn\PaymentAccount\Api\Data\BankInterface
     */
    public function setImage($image);

    /**
     * Get status
     * @return string|null
     */
    public function getStatus();

    /**
     * Set status
     * @param string $status
     * @return \Chottvn\PaymentAccount\Api\Data\BankInterface
     */
    public function setStatus($status);

    /**
     * Get order
     * @return string|null
     */
    public function getOrder();

    /**
     * Set order
     * @param string $order
     * @return \Chottvn\PaymentAccount\Api\Data\BankInterface
     */
    public function setOrder($order);

    /**
     * Get created_at
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Set created_at
     * @param string $createdAt
     * @return \Chottvn\PaymentAccount\Api\Data\BankInterface
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
     * @return \Chottvn\PaymentAccount\Api\Data\BankInterface
     */
    public function setUpdatedAt($updatedAt);

    /**
     * Get name_short
     * @return string|null
     */
    public function getNameShort();

    /**
     * Set name_short
     * @param string $nameShort
     * @return \Chottvn\PaymentAccount\Api\Data\BankInterface
     */
    public function setNameShort($nameShort);

    /**
     * Get code
     * @return string|null
     */
    public function getCode();

    /**
     * Set code
     * @param string $code
     * @return \Chottvn\PaymentAccount\Api\Data\BankInterface
     */
    public function setCode($code);
}

