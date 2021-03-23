<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Orderattr
 */


namespace Amasty\Orderattr\Model\Entity\Adapter\Quote;

class Adapter
{
    /**
     * @var \Magento\Quote\Api\Data\CartExtensionFactory
     */
    private $cartExtensionFactory;

    /**
     * @var \Amasty\Orderattr\Model\Entity\EntityResolver
     */
    private $entityResolver;

    /**
     * @var \Amasty\Orderattr\Model\Entity\Handler\Save
     */
    private $saveHandler;

    public function __construct(
        \Magento\Quote\Api\Data\CartExtensionFactory $cartExtensionFactory,
        \Amasty\Orderattr\Model\Entity\EntityResolver $entityResolver,
        \Amasty\Orderattr\Model\Entity\Handler\Save $saveHandler
    ) {
        $this->cartExtensionFactory = $cartExtensionFactory;
        $this->entityResolver = $entityResolver;
        $this->saveHandler = $saveHandler;
    }

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @param bool                                  $force
     */
    public function addExtensionAttributesToQuote(\Magento\Quote\Api\Data\CartInterface $quote, $force = false)
    {
        $extensionAttributes = $quote->getExtensionAttributes();
        if (empty($extensionAttributes)) {
            $extensionAttributes = $this->cartExtensionFactory->create();
            $quote->setExtensionAttributes($extensionAttributes);
        }
        if (!$force && !empty($extensionAttributes->getAmastyOrderAttributes())) {
            return;
        }

        $entity = $this->entityResolver->getEntityByQuoteId($quote->getId());
        $customAttributes = $entity->getCustomAttributes();

        if (!empty($customAttributes)) {
            $extensionAttributes->setAmastyOrderAttributes($customAttributes);
        }
        $quote->setExtensionAttributes($extensionAttributes);
    }

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     */
    public function saveQuoteValues(\Magento\Quote\Api\Data\CartInterface $quote)
    {
        $extensionAttributes = $quote->getExtensionAttributes();
        if ($extensionAttributes && $extensionAttributes->getAmastyOrderAttributes()) {
            $entity = $this->entityResolver->getEntityByQuoteId($quote->getId());
            $attributes = $extensionAttributes->getAmastyOrderAttributes();
            $entity->setCustomAttributes($attributes);
            $this->saveHandler->execute($entity);
        }
    }
}
