<?php

namespace Chottvn\Address\Plugin\Magento\Customer\Model\Address;

class Mapper
{
    /**
     * @var \Magento\Customer\Model\Address
     */
    private $addressFactory;

    /**
     * @var \Chottvn\Address\Helper\Data
     */
    private $helperData;

    /**
     * @param \Magento\Customer\Model\AddressFactory $addressFactory
     * @param \Chottvn\Address\Helper\Data $helperData
     */
    public function __construct(
        \Magento\Customer\Model\AddressFactory $addressFactory,
        \Chottvn\Address\Helper\Data $helperData
    ) {
        $this->addressFactory = $addressFactory;
        $this->helperData = $helperData;
    }

    /**
     * Add custom address attribute to customer address object
     *
     * @param \Magento\Customer\Model\Address\Mapper $subject
     * @return array
     */
    public function afterToFlatArray(
        \Magento\Customer\Model\Address\Mapper $subject,
        $result
    ) {
        if ($result['id']) {
            $addressData = $this->addressFactory->create()->load($result['id']);
            $additionalFields = $this->helperData->getExtraCheckoutAddressFields();
            foreach ($additionalFields as $field) {
                $result[$field] = $addressData->getData($field);
            }
        }
        return $result;
    }
}
