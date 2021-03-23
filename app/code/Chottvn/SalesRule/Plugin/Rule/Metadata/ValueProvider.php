<?php

namespace Chottvn\SalesRule\Plugin\Rule\Metadata;

class ValueProvider {
    public function afterGetMetadataValues(
        \Magento\SalesRule\Model\Rule\Metadata\ValueProvider $subject,
        $result
    ) {
        $actions = &$result['actions']['children']['simple_action']['arguments']['data']['config']['options'];
        $autoAddActions = [
            [
                'label' => __('Get voucher for next order'),
                'value' => \Chottvn\SalesRule\Api\Data\GiftRuleInterface::ORDER_VOUCHER
            ]
        ];

        $actions[] = [
            'label' => __('Discount code promotions'),
            'value' => $autoAddActions
        ];

        return $result;
    }
}