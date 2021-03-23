<?php

namespace Chottvn\Address\Plugin\Magento\Framework\Reflection;

class DataObjectProcessor
{
    /**
     * Overwrite AddressInterface
     *
     * @param \Magento\Framework\Reflection\DataObjectProcessor $subject
     * @param mixed $dataObject
     * @param string $dataObjectType
     * @return array
     */
    public function beforeBuildOutputDataArray(
        \Magento\Framework\Reflection\DataObjectProcessor $subject,
        $dataObject,
        $dataObjectType
    ) {
        if (ltrim($dataObjectType, '\/') == \Magento\Customer\Api\Data\AddressInterface::class) {
            $dataObjectType = \Chottvn\Address\Api\Data\AddressInterface::class;
        }
        return [$dataObject, $dataObjectType];
    }
}
