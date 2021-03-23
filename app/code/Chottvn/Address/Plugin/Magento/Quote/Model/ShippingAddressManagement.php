<?php

namespace Chottvn\Address\Plugin\Magento\Quote\Model;

class ShippingAddressManagement
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
     * Assign custom address attribute to shipping address
     *
     * @param \Magento\Quote\Model\ShippingAddressManagement $subject
     * @param int $cartId
     * @param \Magento\Quote\Api\Data\AddressInterface $address
     */
    public function beforeAssign(
        \Magento\Quote\Model\ShippingAddressManagement $subject,
        $cartId,
        \Magento\Quote\Api\Data\AddressInterface $address
    ) {
        $extAttributes = $address->getExtensionAttributes();
        if (!empty($extAttributes)) {
            $this->helper->transportFieldsFromExtensionAttributesToObject(
                $extAttributes,
                $address,
                'extra_checkout_shipping_address_fields'
            );
        }
    }
}
