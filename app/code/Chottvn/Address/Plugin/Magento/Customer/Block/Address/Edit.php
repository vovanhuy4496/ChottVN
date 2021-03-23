<?php

namespace Chottvn\Address\Plugin\Magento\Customer\Block\Address;

class Edit
{
    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     */
    public function __construct(
        \Magento\Framework\App\ProductMetadataInterface $productMetadata
    ) {
        $this->productMetadata = $productMetadata;
    }

    /**
     * Overwrite address template
     *
     * @param \Magento\Customer\Block\Address\Edit $subject
     * @return array
     */
    public function beforeSetTemplate(
        \Magento\Customer\Block\Address\Edit $subject,
        $template
    ) {
        $edition = strtolower($this->productMetadata->getEdition());
        $version = $this->productMetadata->getVersion();
        if (version_compare($version, '2.3.0', '>=')) {
            return ["Chottvn_Address::address/{$edition}/edit_23.phtml"];
        } elseif (version_compare($version, '2.2.0', '>=')) {
            return ["Chottvn_Address::address/{$edition}/edit_22.phtml"];
        } elseif (version_compare($version, '2.1.0', '>=')) {
            return ["Chottvn_Address::address/{$edition}/edit_21.phtml"];
        }
        return [$template];
    }
}
