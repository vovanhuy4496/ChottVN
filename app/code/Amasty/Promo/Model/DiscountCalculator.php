<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Promo
 */


namespace Amasty\Promo\Model;

use Magento\Quote\Model\Quote\Item;

class DiscountCalculator
{
    /**
     * @var \Magento\Store\Model\Store
     */
    private $store;

    /**
     * @var \Amasty\Promo\Model\Config
     */
    private $config;

    public function __construct(
        \Magento\Store\Model\Store $store,
        \Amasty\Promo\Model\Config $config
    ) {
        $this->store = $store;
        $this->config = $config;
    }

    /**
     * @param \Magento\SalesRule\Model\Rule $rule
     * @param Item $item
     *
     * @return float|int|mixed|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getBaseDiscountAmount(\Magento\SalesRule\Model\Rule $rule, Item $item)
    {
        $promoDiscount = trim($rule->getAmpromoRule()->getItemsDiscount());
        $itemPrice = $item->getPrice();
        switch ($promoDiscount) {
            case $promoDiscount === "100%":
            case $promoDiscount == "":
                $baseDiscount = $itemPrice;
                break;

            case strpos($promoDiscount, "%") !== false:
                $baseDiscount = $this->getPercentDiscount($itemPrice, $promoDiscount);
                break;

            case strpos($promoDiscount, "-") !== false:
                $baseDiscount = $this->getFixedDiscount($itemPrice, $promoDiscount);
                break;

            default:
                $baseDiscount = $this->getFixedPrice($itemPrice, $promoDiscount);
                break;
        }

        $baseDiscount = $this->getDiscountAfterMinimalPrice($rule, $itemPrice, $baseDiscount) * $item->getQty();

        return $baseDiscount;
    }

    /**
     * @param \Magento\SalesRule\Model\Rule $rule
     * @param \Magento\Quote\Model\Quote\Item $item
     *
     * @return float|int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getDiscountAmount(\Magento\SalesRule\Model\Rule $rule, Item $item)
    {
        $discountAmount = $this->getBaseDiscountAmount($rule, $item) * $this->store->getCurrentCurrencyRate();

        return $discountAmount;
    }

    /**
     * @param $itemPrice
     * @param $promoDiscount
     * @return mixed
     */
    private function getPercentDiscount($itemPrice, $promoDiscount)
    {
        $percent = (float)str_replace("%", "", $promoDiscount);
        $discount = $itemPrice * $percent / 100;

        return $discount;
    }

    /**
     * @param $itemPrice
     * @param $promoDiscount
     * @return mixed
     */
    private function getFixedDiscount($itemPrice, $promoDiscount)
    {
        $discount = abs($promoDiscount);
        if ($discount > $itemPrice) {
            $discount = $itemPrice;
        }

        return $discount;
    }

    /**
     * @param $itemPrice
     * @param $promoDiscount
     * @return mixed
     */
    private function getFixedPrice($itemPrice, $promoDiscount)
    {
        $discount = $itemPrice - (float)$promoDiscount;
        if ($discount < 0) {
            $discount = 0;
        }

        return $discount;
    }

    /**
     * @param \Magento\SalesRule\Model\Rule $rule
     * @param $itemPrice
     * @param $discount
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getDiscountAfterMinimalPrice(\Magento\SalesRule\Model\Rule $rule, $itemPrice, $discount)
    {
        $minimalPrice = $rule->getAmpromoRule()->getMinimalItemsPrice();
        if ($itemPrice > $minimalPrice && $itemPrice - $discount < $minimalPrice) {
            $discount = $itemPrice - $minimalPrice;
        }

        return $discount;
    }

    /**
     * @param $discount
     *
     * @return bool
     */
    public function isFullDiscount($discount)
    {
        if ($discount) {
            $discountItem = isset($discount['discount_item']) ? $discount['discount_item'] : '';
            $minimalPrice = isset($discount['minimal_price']) ? $discount['minimal_price'] : '';
            if ($minimalPrice) {
                return false;
            }

            if ($discountItem === false) {
                return true;
            }

            if ($discountItem === "100%" || $discountItem === null || $discountItem === "") {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $discount
     *
     * @return bool
     */
    public function isEnableAutoAdd($discount)
    {
        $addAutomatically = $this->config->getAutoAddType();

        return ($addAutomatically == \Amasty\Promo\Model\Rule::AUTO_FREE_ITEMS
                && $this->isFullDiscount($discount))
            || $addAutomatically == \Amasty\Promo\Model\Rule::AUTO_FREE_DISCOUNTED_ITEMS;
    }
}
