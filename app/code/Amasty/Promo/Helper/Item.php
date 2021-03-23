<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Promo
 */


namespace Amasty\Promo\Helper;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

class Item extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->storeManager = $storeManager;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @return mixed|null
     */
    public function getRuleId(\Magento\Quote\Model\Quote\Item\AbstractItem $item)
    {
        if (!($ruleId = $item->getData('ampromo_rule_id'))) {
            $buyRequest = $item->getBuyRequest();

            $ruleId = null;
            if (isset($buyRequest['options']['ampromo_rule_id'])) {
                $ruleId = $buyRequest['options']['ampromo_rule_id'];
            }

            $item->setData('ampromo_rule_id', $ruleId);
        }

        return $ruleId;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @return bool
     */
    public function isPromoItem(\Magento\Quote\Model\Quote\Item\AbstractItem $item)
    {
        if ($this->storeManager->getStore()->getCode() == \Magento\Store\Model\Store::ADMIN_CODE) {
            return false;
        }

        return $this->getRuleId($item) !== null;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @return mixed
     */
    public function getItemSku(\Magento\Quote\Model\Quote\Item\AbstractItem $item)
    {
        $productType = $item->getProductType();
        if ($productType == Configurable::TYPE_CODE) {
            return $item->getProduct()->getData('sku');
        }

        return $item->getSku();
    }
}
