<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Promo
 */


namespace Amasty\Promo\Plugin\Quote;

use Magento\Framework\Api\SortOrder;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Amasty\Promo\Model\Storage;
use Magento\SalesRule\Model\Data\Rule;

class Item
{
    /**
     * @var \Amasty\Promo\Helper\Item
     */
    private $promoItemHelper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Amasty\Promo\Model\DiscountCalculator
     */
    private $discountCalculator;

    /**
     * @var \Magento\SalesRule\Api\RuleRepositoryInterface
     */
    private $ruleRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\Api\SortOrderBuilder
     */
    private $sortOrderBuilder;

    public function __construct(
        \Amasty\Promo\Helper\Item $promoItemHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Amasty\Promo\Model\DiscountCalculator $discountCalculator,
        \Magento\SalesRule\Api\RuleRepositoryInterface $ruleRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Api\SortOrderBuilder $sortOrderBuilder
    ) {
        $this->promoItemHelper = $promoItemHelper;
        $this->scopeConfig     = $scopeConfig;
        $this->discountCalculator = $discountCalculator;
        $this->ruleRepository = $ruleRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sortOrderBuilder = $sortOrderBuilder;
    }

    /**
     * @param AbstractItem $subject
     * @param $key
     * @param null $value
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeSetData(AbstractItem $subject, $key, $value = null)
    {
        if (!is_string($key)) {
            return [$key, $value];
        }

        $fields = [
            'price',
            'base_price',
            'custom_price',
            'original_custom_price',
            'price_incl_tax',
            'base_price_incl_tax',
            'row_total',
            'row_total_incl_tax',
            'base_row_total',
            'base_row_total_incl_tax',
        ];

        if (in_array($key, $fields)) {
            if ($this->promoItemHelper->isPromoItem($subject)
                && $this->isFullDiscount($subject)
                && $subject->getNotUsePricePlugin() !== true
                && $subject->getProduct()->getTypeId() !== 'giftcard'
            ) {
                if (isset(Storage::$cachedQuoteItemPricesWithTax[$subject->getSku()][$key])) {
                    return [$key, Storage::$cachedQuoteItemPricesWithTax[$subject->getSku()][$key]];
                }

                return [$key, 0];
            }
        }

        return [$key, $value];
    }

    /**
     * @param AbstractItem $item
     *
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function isFullDiscount(\Magento\Quote\Model\Quote\Item\AbstractItem $item)
    {
        $buyRequest = $item->getBuyRequest();
        $discount = isset($buyRequest['options']['discount']) ? $buyRequest['options']['discount'] : false;

        return $this->discountCalculator->isFullDiscount($discount);
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $subject
     * @param \Closure $proceed
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return bool
     */
    public function aroundRepresentProduct(
        AbstractItem $subject,
        \Closure $proceed,
        \Magento\Catalog\Model\Product $product
    ) {
        if ($proceed($product)) {
            $productRuleId = $product->getData('ampromo_rule_id');
            $itemRuleId    = $this->promoItemHelper->getRuleId($subject);

            return $productRuleId === $itemRuleId;
        } else {
            return false;
        }
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $subject
     * @param \Closure $proceed
     * @param bool $string
     *
     * @return array|mixed|string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aroundGetMessage(
        AbstractItem $subject,
        \Closure $proceed,
        $string = true
    ) {
        $result = $proceed($string);

        if ($this->promoItemHelper->isPromoItem($subject)) {
            $customMessage = $this->scopeConfig->getValue(
                'ampromo/messages/cart_message',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );

            if (!$customMessage) {
                /** @var \Magento\Framework\Api\SearchCriteria $searchCriteria */
                $searchCriteria = $this->getSearchCriteria((string)$subject->getAmpromoRuleId());
                /** @var \Magento\SalesRule\Api\Data\RuleSearchResultInterface $rules */
                $rules = $this->ruleRepository->getList($searchCriteria);
                foreach ($rules->getItems() as $rule) {
                    $ruleLabel = $this->getStoreLabel($rule, $subject->getStoreId());
                    if ($ruleLabel) {
                        $customMessage = $ruleLabel->getStoreLabel();

                        if ($customMessage) {
                            break;
                        }
                    }
                }
            }

            $prefix = $this->scopeConfig->getValue(
                'ampromo/messages/prefix',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );

            if ($prefix) {
                $buyRequest = $subject->getBuyRequest();

                if (isset($buyRequest['options']['ampromo_rule_id'])) {
                    $subject->setName($prefix . ' ' . $subject->getName());
                }
            }

            if ($customMessage) {
                if ($string) {
                    $result .= __("\n" . $customMessage);
                } else {
                    $result[] = __($customMessage);
                }
            }
        }

        return $result;
    }

    /**
     * @param string $ruleIds
     *
     * @return \Magento\Framework\Api\SearchCriteria
     */
    private function getSearchCriteria($ruleIds)
    {
        /** @var \Magento\Framework\Api\SortOrder $sortOrder */
        $sortOrder = $this->sortOrderBuilder->setField(Rule::KEY_SORT_ORDER)
            ->setDirection(SortOrder::SORT_ASC)
            ->create();

        return $this->searchCriteriaBuilder->addFilter(
            Rule::KEY_RULE_ID,
            $ruleIds,
            'in'
        )
            ->addSortOrder($sortOrder)
            ->create();
    }

    /**
     * Get Rule label by specified store
     *
     * @param \Magento\SalesRule\Model\Data\Rule $rule
     * @param int|null $storeId
     *
     * @return \Magento\SalesRule\Model\Data\RuleLabel|bool
     */
    private function getStoreLabel(Rule $rule, $storeId = null)
    {
        $labels = (array)$rule->getStoreLabels();

        if (isset($labels[$storeId])) {
            return $labels[$storeId];
        } elseif (isset($labels[0]) && $labels[0]) {
            return $labels[0];
        }

        return false;
    }
}
