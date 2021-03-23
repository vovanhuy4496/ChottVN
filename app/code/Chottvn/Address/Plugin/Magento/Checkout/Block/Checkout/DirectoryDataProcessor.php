<?php

namespace Chottvn\Address\Plugin\Magento\Checkout\Block\Checkout;

class DirectoryDataProcessor
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
     * Add city options
     *
     * @param \Magento\Checkout\Block\Checkout\DirectoryDataProcessor $subject
     * @return array
     */
    public function afterProcess(
        \Magento\Checkout\Block\Checkout\DirectoryDataProcessor $subject,
        $result
    ) {
        $result['components']['checkoutProvider']
        ['dictionaries']['city_id'] = $this->helper->getCityDataProvider();
        // $result['components']['checkoutProvider']
        // ['dictionaries']['township_id'] = $this->helper->getTownshipDataProvider();
        return $result;
    }
}
