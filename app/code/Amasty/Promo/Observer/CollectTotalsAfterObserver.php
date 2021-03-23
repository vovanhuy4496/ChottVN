<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Promo
 */


namespace Amasty\Promo\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Remove all not allowed items
 */

class CollectTotalsAfterObserver implements ObserverInterface
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    private $_coreRegistry;

    /**
     * @var \Amasty\Promo\Helper\Cart
     */
    private $promoCartHelper;

    /**
     * @var \Amasty\Promo\Helper\Item
     */
    private $promoItemHelper;

    /**
     * @var \Amasty\Promo\Model\Registry
     */
    private $promoRegistry;

    /**
     * @var \Amasty\Promo\Model\Config
     */
    private $config;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;

    public function __construct(
        \Magento\Framework\Registry $registry,
        \Amasty\Promo\Helper\Cart $promoCartHelper,
        \Amasty\Promo\Helper\Item $promoItemHelper,
        \Amasty\Promo\Model\Registry $promoRegistry,
        \Amasty\Promo\Model\Config $config,
        \Magento\Framework\Event\ManagerInterface\Proxy $eventManager
    ) {
        $this->_coreRegistry = $registry;
        $this->promoCartHelper = $promoCartHelper;
        $this->promoItemHelper = $promoItemHelper;
        $this->promoRegistry = $promoRegistry;
        $this->config = $config;
        $this->eventManager = $eventManager;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @throws \Exception
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $allowedItems = $this->promoRegistry->getPromoItems();
        $deletedItems = $this->promoRegistry->getDeletedItems();
        $toAdd = $this->_coreRegistry->registry('ampromo_to_add');
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $observer->getQuote();

        if (is_array($toAdd)) {
            foreach ($toAdd as $item) {
                if (!array_key_exists($item['product']->getSku(), $deletedItems)) {
                    $this->promoCartHelper->addProduct(
                        $item['product'],
                        $item['qty'],
                        $item['rule_id'],
                        [],
                        $item['discount'],
                        isset($item['discount']) && !empty($item['discount']) ? $item['discount']['minimal_price'] : null,
                        $quote
                    );
                }
            }
        }

        $this->_coreRegistry->unregister('ampromo_to_add');

        /** @var \Magento\Quote\Model\Quote\Item $item */
        foreach ($quote->getAllItems() as $item) {
            if ($this->promoItemHelper->isPromoItem($item)) {
                if ($item->getParentItemId()) {
                    continue;
                }

                $sku = $item->getProduct()->getData('sku');

                $ruleId = $this->promoItemHelper->getRuleId($item);
                $item->setQuote($quote);

                if (isset($allowedItems['_groups'][$ruleId])) { // Add one of
                    if ($allowedItems['_groups'][$ruleId]['qty'] <= 0) {
                        $this->removeGift($item);
                    } elseif ($item->getQty() > $allowedItems['_groups'][$ruleId]['qty']) {
                        $item->setQty($allowedItems['_groups'][$ruleId]['qty']);
                    }

                    $allowedItems['_groups'][$ruleId]['qty'] -= $item->getQty();
                } elseif (isset($allowedItems[$ruleId]['sku'][$sku])) { // Add all of
                    if ($allowedItems[$ruleId]['sku'][$sku]['qty'] <= 0) {
                        $this->removeGift($item);
                    } elseif ($item->getQty() > $allowedItems[$ruleId]['sku'][$sku]['qty']) {
                        $item->setQty($allowedItems[$ruleId]['sku'][$sku]['qty']);
                    }

                    $allowedItems[$ruleId]['sku'][$sku]['qty'] -= $item->getQty();
                } else {
                    $this->removeGift($item);
                }
            }
        }

        $this->promoCartHelper->updateQuoteTotalQty(
            false,
            $quote
        );
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item $item
     */
    private function removeGift($item)
    {
        $quote = $item->getQuote();
        if ($item->getId()) {
            $quote->removeItem($item->getId());
        } else {
            $item->isDeleted(true);
            if ($item->getHasChildren()) {
                foreach ($item->getChildren() as $child) {
                    $child->isDeleted(true);
                }
            }

            $parent = $item->getParentItem();
            if ($parent) {
                $parent->isDeleted(true);
            }
            $this->eventManager->dispatch('sales_quote_remove_item', ['quote_item' => $item]);

            //reassemble collection items, otherwise 'deleted' items without ID will be saved
            $collection = $quote->getItemsCollection();
            $items = $collection->getItems();
            $collection->removeAllItems();

            /** @var \Magento\Quote\Model\Quote\Item $row */
            foreach ($items as $row) {
                if (!(!$row->getId() && $row->isDeleted())) {
                    $collection->addItem($row);
                }
            }
        }
    }
}
