<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Promo
 */


namespace Amasty\Promo\Model\Rule\Action\Discount;

/**
 * Action name: Auto add promo items for every $X spent
 */
class Spent extends AbstractDiscount
{
    /**
     * @var array
     */
    private $calculatedTotals = [];

    protected function _getFreeItemsQty(
        \Magento\SalesRule\Model\Rule $rule,
        \Magento\Quote\Model\Quote\Item\AbstractItem $item
    ) {
        $amount = max(1, $rule->getDiscountAmount());
        $step   = $rule->getDiscountStep();

        if (!$step && $this->isSkipCalculation($rule->getRuleId())) {
            return 0;
        }

        $ruleTotal = $this->getItemsSpent($this->getRuleItems($item, $rule));
        $this->setCalculatedTotals($rule->getRuleId(), $ruleTotal);
        $qty = floor($ruleTotal / $step) * $amount;
        $max = $rule->getDiscountQty();

        if ($max) {
            $qty = min($max, $qty);
        }

        return $qty;
    }

    /**
     * @param \Magento\SalesRule\Model\Rule $rule
     * @param float $total
     *
     * @return int
     *
     * @used-by \Amasty\Promo\Observer\CollectTotalsAfterObserver::fixQty
     */
    public static function getQty(\Magento\SalesRule\Model\Rule $rule, $total)
    {
        $amount = max(1, $rule->getDiscountAmount());
        $step = $rule->getDiscountStep();

        if (!$step) {
            return 0;
        }

        $qty = floor($total / $step) * $amount;
        $max = $rule->getDiscountQty();

        if ($max) {
            $qty = min($max, $qty);
        }

        return $qty;
    }

    /**
     * @param int $ruleId
     * @return bool
     */
    private function isSkipCalculation($ruleId)
    {
        return isset($this->calculatedTotals[$ruleId]);
    }

    /**
     * @param $ruleId
     * @param $total
     * @return $this
     */
    public function setCalculatedTotals($ruleId, $total)
    {
        $this->calculatedTotals[$ruleId] = $total;

        return $this;
    }

    /**
     * @param $ruleItems
     * @return float|int
     */
    private function getItemsSpent($ruleItems)
    {
        $total = 0;
        $withDiscount = $this->config->isDiscountIncluded();
        /** @var \Magento\Quote\Model\Quote\Item\AbstractItem $item */
        foreach ($ruleItems as $item) {
            $total += $item->getBaseRowTotal();
            if ($withDiscount) {
                $total -= $item->getBaseDiscountAmount();
            }
        }

        return $total;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @param \Magento\SalesRule\Model\Rule $rule
     *
     * @return \Magento\Quote\Model\Quote\Address\Item[]
     */
    private function getRuleItems($item, $rule)
    {
        $validItems = [];

        if ($item->getProductType() === \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE) {
            $validItems[] = $item;
        } else {
            foreach ($this->_getAllItems($item) as $item) {
                if ($rule->getActions()->validate($item)) {
                    $validItems[] = $item;
                    $this->promoRegistry->addValidItem($rule->getId(), $item->getId());
                }
            }
        }

        if ($validItems) {
            $this->promoRegistry->addRuleUsed($rule);
        }

        return $validItems;
    }
}
