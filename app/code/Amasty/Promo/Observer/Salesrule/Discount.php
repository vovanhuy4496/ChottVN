<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Promo
 */


namespace Amasty\Promo\Observer\Salesrule;

use Amasty\Promo\Model\Rule;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;

class Discount implements ObserverInterface
{
    const PROMO_RULES = [
        Rule::PER_PRODUCT,
        Rule::SAME_PRODUCT,
        Rule::SPENT,
        Rule::WHOLE_CART,
        Rule::EACHN,
    ];
    /**
     * @var \Amasty\Promo\Helper\Item
     */
    private $promoItemHelper;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var \Amasty\Promo\Model\DiscountCalculator
     */
    private $discountCalculator;

    /**
     * @var \Amasty\Promo\Model\RuleResolver
     */
    private $ruleResolver;

    /**
     * @var \Magento\Framework\App\State
     */
    private $state;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    public function __construct(
        \Amasty\Promo\Helper\Item $promoItemHelper,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Amasty\Promo\Model\DiscountCalculator $discountCalculator,
        \Amasty\Promo\Model\RuleResolver $ruleResolver,
        \Magento\Framework\App\State $state,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->promoItemHelper = $promoItemHelper;
        $this->productRepository = $productRepository;
        $this->discountCalculator = $discountCalculator;
        $this->ruleResolver = $ruleResolver;
        $this->state = $state;
        $this->logger = $logger;
    }

    /**
     * @param Observer $observer
     * @return \Magento\SalesRule\Model\Rule\Action\Discount\Data|void
     */
    public function execute(Observer $observer)
    {
        try {
            /** @var \Magento\Quote\Model\Quote\Item $item */
            $item = $observer->getItem();

            /** @var \Magento\SalesRule\Model\Rule\Action\Discount\Data $result */
            $result = $observer->getResult();

            if ($this->promoItemHelper->isPromoItem($item)
                && in_array($observer->getRule()->getSimpleAction(), self::PROMO_RULES)
            ) {
                $isValid = $this->checkItemForPromo($observer, $item);

                try {
                    $areaCode = $this->state->getAreaCode();
                } catch (LocalizedException $exception) {
                    $areaCode = \Magento\Framework\App\Area::AREA_FRONTEND;
                }

                $ruleId = $observer->getRule()->getId();
                if ($isValid && (int)$ruleId === $item->getAmpromoRuleId()) {
                    if (!$item->getAmDiscountAmount()) {
                        $baseDiscount = $this->discountCalculator->getBaseDiscountAmount($observer->getRule(), $item);
                        $discount = $this->discountCalculator->getDiscountAmount($observer->getRule(), $item);

                        $result->setBaseAmount($baseDiscount);
                        $result->setAmount($discount);
                        $item->setAmBaseDiscountAmount($baseDiscount);
                        $item->setAmDiscountAmount($discount);
                    } elseif ($areaCode === \Magento\Framework\App\Area::AREA_WEBAPI_REST) {
                        $result->setAmount($item->getAmDiscountAmount());
                        $result->setBaseAmount($item->getAmBaseDiscountAmount());
                    }
                }
            }
        } catch (LocalizedException $e) {
            $this->logger->critical($e->getMessage());
        }
    }

    /**
     * @param Observer $observer
     * @param \Magento\Quote\Model\Quote\Item $item
     *
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function checkItemForPromo($observer, $item)
    {
        $itemSku = $item->getSku();
        $ampromoRule = $this->ruleResolver->getFreeGiftRule($observer->getRule());
        $promoDiscount = $ampromoRule->getItemsDiscount();
        $minimalPrice = $ampromoRule->getMinimalItemsPrice();

        if (!$minimalPrice
            && (!$promoDiscount || $promoDiscount === "100%")
            && $item->getProductType() !== 'giftcard'
        ) {
            return false;
        }

        $promoSku = explode(",", $observer->getRule()->getAmpromoRule()->getSku());
        $isValid = false;
        foreach ($promoSku as $sku) {
            if ($sku && stristr($itemSku, $sku)) {
                $isValid = true;
                break;
            }
        }

        return $isValid || $observer->getRule()->getSimpleAction() === Rule::SAME_PRODUCT;
    }
}
