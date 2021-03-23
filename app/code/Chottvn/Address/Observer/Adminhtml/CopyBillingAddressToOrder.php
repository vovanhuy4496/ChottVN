<?php

namespace Chottvn\Address\Observer\Adminhtml;

class CopyBillingAddressToOrder implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Chottvn\Address\Helper\Data
     */
    private $helperData;

    /**
     * @param \Chottvn\Address\Helper\Data $helperData
     */
    public function __construct(
        \Chottvn\Address\Helper\Data $helperData
    ) {
        $this->helperData = $helperData;
    }

    /**
     * Copy data from order address to quote address
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(
        \Magento\Framework\Event\Observer $observer
    ) {
        $target = $observer->getEvent()->getTarget();
        $source = $observer->getEvent()->getSource();

        $fields = $this->helperData->getExtraCheckoutAddressFields('extra_checkout_billing_address_fields');
        foreach ($fields as $key => $field) {
            $target->setData($field, $source->getData($field));
        }
    }
}
