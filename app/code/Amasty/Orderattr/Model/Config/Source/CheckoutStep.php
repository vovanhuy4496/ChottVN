<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Orderattr
 */


namespace Amasty\Orderattr\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class CheckoutStep implements ArrayInterface
{
    const CUSTOMER_INFO = 1;
    const SHIPPING_STEP = 2;
    const PAYMENT_STEP = 3;
    const SHIPPING_METHODS = 4;
    const PAYMENT_PLACE_ORDER = 5;
    const ORDER_SUMMARY = 6;
    const VAT_INVOICE_REQUIRED = 7;
    const NONE = 999;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $optionArray = [];
        foreach ($this->toArray() as $stepId => $label) {
            $optionArray[] = ['value' => $stepId, 'label' => $label];
        }
        return $optionArray;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [
            self::CUSTOMER_INFO => __('Customer Info'),
            self::SHIPPING_STEP => __('Shipping Address'),
            self::SHIPPING_METHODS => __('Shipping Methods'),
            self::PAYMENT_STEP => __('Above Payment Method'),
            self::PAYMENT_PLACE_ORDER => __('Below Payment Method'),
            self::ORDER_SUMMARY => __('Order Summary'),
            self::VAT_INVOICE_REQUIRED => __('Invoice information'),
            self::NONE => __('None'),
        ];
    }
}
