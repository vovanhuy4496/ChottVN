<?php

namespace Chottvn\Address\Plugin\Magento\Quote\Model\GuestCart;

class GuestBillingAddressManagement
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
     * Assign custom address attribute to billing address
     *
     * @param \Magento\Quote\Model\BillingAddressManagement $subject
     * @param int $cartId
     * @param \Magento\Quote\Api\Data\AddressInterface $address
     * @param bool $useForShipping
     */
    public function beforeAssign(
        \Magento\Quote\Model\GuestCart\GuestBillingAddressManagement $subject,
        $cartId,
        \Magento\Quote\Api\Data\AddressInterface $address,
        $useForShipping = false
    ) {
        $extAttributes = $address->getExtensionAttributes();
        if (!empty($extAttributes)) {
            $this->helper->transportFieldsFromExtensionAttributesToObject(
                $extAttributes,
                $address,
                'extra_checkout_billing_address_fields'
            );
        }
    }
}
