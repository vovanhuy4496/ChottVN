<?php

namespace Chottvn\Address\Observer;

class CopyQuoteAddressToOrderAddress implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Chottvn\Address\Helper\Data
     */
    private $helper;

    /**
     * @param \Chottvn\Address\Helper\Data $helper
     */
    public function __construct(
        \Chottvn\Address\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * Save custom address attribute
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(
        \Magento\Framework\Event\Observer $observer
    ) {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getEvent()->getOrder();
        $quote = $observer->getEvent()->getQuote();

        $this->helper->transportFieldsFromExtensionAttributesToObject(
            $quote->getBillingAddress(),
            $order->getBillingAddress(),
            'extra_checkout_billing_address_fields'
        );

        $this->helper->transportFieldsFromExtensionAttributesToObject(
            $quote->getShippingAddress(),
            $order->getShippingAddress(),
            'extra_checkout_shipping_address_fields'
        );

        $orderBilling = $order->getBillingAddress();
        $orderShipping = $order->getShippingAddress();
        if ($orderBilling && $orderShipping) {
            if (!$orderBilling->getCityId()) {
                $orderBilling->setCityId($orderShipping->getCityId());
            }
            if (!$orderBilling->getTownship()) {
                $orderBilling->setTownship($orderShipping->getTownship());
            }
            if (!$orderBilling->getTownshipId()) {
                $orderBilling->setTownshipId($orderShipping->getTownshipId());
            }
        }
    }
}
