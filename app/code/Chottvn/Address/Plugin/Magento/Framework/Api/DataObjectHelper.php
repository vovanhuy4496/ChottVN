<?php

namespace Chottvn\Address\Plugin\Magento\Framework\Api;

class DataObjectHelper
{
    /**
     * Overwrite AddressInterface
     *
     * @param \Magento\Framework\Api\DataObjectHelper $subject
     * @param mixed $dataObject
     * @param array $data
     * @param string $interfaceName
     * @return array
     */
    public function beforePopulateWithArray(
        \Magento\Framework\Api\DataObjectHelper $subject,
        $dataObject,
        array $data,
        $interfaceName
    ) {
        if (ltrim($interfaceName, '\/') == \Magento\Customer\Api\Data\AddressInterface::class
            || ltrim($interfaceName, '\/') == \Magento\Quote\Api\Data\AddressInterface::class
            ) {
            $interfaceName = \Chottvn\Address\Api\Data\AddressInterface::class;
        }
        return [$dataObject, $data, $interfaceName];
    }
}
