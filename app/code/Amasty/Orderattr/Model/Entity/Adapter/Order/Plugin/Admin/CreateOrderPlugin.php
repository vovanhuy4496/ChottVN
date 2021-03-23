<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Orderattr
 */


namespace Amasty\Orderattr\Model\Entity\Adapter\Order\Plugin\Admin;

use Amasty\Orderattr\Model\Value\Metadata\Form;

class CreateOrderPlugin
{
    /**
     * @var \Magento\Quote\Api\Data\CartExtensionInterfaceFactory
     */
    private $cartExtensionFactory;

    /**
     * @var \Amasty\Orderattr\Model\Value\Metadata\FormFactory
     */
    private $metadataFormFactory;

    /**
     * @var \Amasty\Orderattr\Model\Entity\EntityResolver
     */
    private $entityResolver;

    /**
     * @var \Amasty\Orderattr\Model\Entity\Adapter\Quote\Adapter
     */
    private $quoteAdapter;

    public function __construct(
        \Magento\Quote\Api\Data\CartExtensionFactory $cartExtensionFactory,
        \Amasty\Orderattr\Model\Value\Metadata\FormFactory $metadataFormFactory,
        \Amasty\Orderattr\Model\Entity\EntityResolver $entityResolver,
        \Amasty\Orderattr\Model\Entity\Adapter\Quote\Adapter $quoteAdapter
    ) {
        $this->cartExtensionFactory = $cartExtensionFactory;
        $this->metadataFormFactory = $metadataFormFactory;
        $this->entityResolver = $entityResolver;
        $this->quoteAdapter = $quoteAdapter;
    }

    /**
     * @param \Magento\Sales\Model\AdminOrder\Create $subject
     * @param array                                  $data
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeImportPostData(\Magento\Sales\Model\AdminOrder\Create $subject, $data)
    {
        if (!isset($data['extension_attributes']['amasty_order_attributes'])) {
            return;
        }
        $quote = $subject->getQuote();
        $entity = $this->entityResolver->getEntityByQuoteId($quote->getId());

        $form = $this->createEntityForm($entity, $subject->getSession()->getStore(), $quote->getCustomerGroupId());
        // emulate request
        $request = $form->prepareRequest($data['extension_attributes']['amasty_order_attributes']);
        $data = $form->extractData($request);
        $form->restoreData($data);
        $errors = $form->validateData($data);
        if (is_array($errors)) {
            throw new \Magento\Framework\Exception\LocalizedException(__(implode($errors)));
        }

        $this->quoteAdapter->addExtensionAttributesToQuote($quote, true);
    }

    /**
     * Return Checkout Form instance
     *
     * @param \Amasty\Orderattr\Model\Entity\EntityData $entity
     * @param \Magento\Store\Model\Store                $store
     * @param int                                       $customerGroup
     *
     * @return Form
     */
    protected function createEntityForm($entity, $store, $customerGroup)
    {
        /** @var Form $formProcessor */
        $formProcessor = $this->metadataFormFactory->create();
        $formProcessor->setFormCode('adminhtml_checkout')
            ->setEntity($entity)
            ->setStore($store)
            ->setCustomerGroupId($customerGroup);

        return $formProcessor;
    }
}
