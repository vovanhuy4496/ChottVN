<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Promo
 */


namespace Amasty\Promo\Plugin\OfflineShipping\Model\SalesRule;

use Magento\OfflineShipping\Model\SalesRule\Calculator as ShippingCalculator;
use Magento\Quote\Model\Quote\Item\AbstractItem;

class Calculator
{
    /**
     * @var \Magento\Quote\Model\Quote\Item\AbstractItem
     */
    private $item;

    /**
     * @var \Amasty\Promo\Helper\Item
     */
    private $helperItem;

    /**
     * @var \Amasty\Promo\Model\ResourceModel\Rule
     */
    private $ruleResource;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    public function __construct(
        \Amasty\Promo\Helper\Item $helperItem,
        \Amasty\Promo\Model\ResourceModel\Rule $ruleResource,
        \Magento\Checkout\Model\Session $resourceSession
    ) {
        $this->checkoutSession = $resourceSession;
        $this->helperItem = $helperItem;
        $this->ruleResource = $ruleResource;
    }

    /**
     * @param ShippingCalculator $subject
     * @param ShippingCalculator $result
     * @return ShippingCalculator
     */
    public function afterProcessFreeShipping(
        ShippingCalculator $subject,
        $result
    ) {

        $fullDiscountItems = $this->checkoutSession->getAmpromoFullDiscountItems();
        $itemSku = $this->helperItem->getItemSku($this->item);

        if ($this->helperItem->isPromoItem($this->item)
            && isset($fullDiscountItems[$itemSku])
            && !$this->ruleResource->isApplyShipping($fullDiscountItems[$itemSku]['rule_ids'])
        ) {
            $this->item->setFreeShipping(true);
        }

        return $result;
    }

    /**
     * @param ShippingCalculator $subject
     * @param AbstractItem $item
     */
    public function beforeProcessFreeShipping(
        ShippingCalculator $subject,
        $item
    ) {
        $this->item = $item;
    }
}
