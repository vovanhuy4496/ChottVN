<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Promo
 */


namespace Chottvn\Sales\Rewrite\Amasty\Promo\Helper;

use Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface;
use Magento\CatalogInventory\Model\Spi\StockStateProviderInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Exception\LocalizedException;

class Cart extends \Amasty\Promo\Helper\Cart
{
    /**
     * @var \Magento\Checkout\Model\Cart
     */
    private $cart;

    /**
     * @var \Amasty\Promo\Model\Registry
     */
    private $promoRegistry;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    private $stockRegistry;

    /**
     * @var \Amasty\Promo\Helper\Messages
     */
    private $promoMessagesHelper;
    
    /**
     * @var StockStateProviderInterface
     */
    private $stockStateProvider;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    private $metadata;

    /**
     * @var \Amasty\Promo\Helper\Item
     */
    private $promoItemHelper;

    private $addedProducts = [];

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Checkout\Model\Cart $cart,
        \Amasty\Promo\Model\Registry $promoRegistry,
        StockRegistryProviderInterface $stockRegistry,
        \Amasty\Promo\Helper\Messages $promoMessagesHelper,
        StockStateProviderInterface $stockStateProvider,
        \Magento\Framework\App\ProductMetadataInterface $metadata,
        \Amasty\Promo\Helper\Item $promoItemHelper
    ) {
        parent::__construct(
            $context, 
            $cart, 
            $promoRegistry, 
            $stockRegistry, 
            $promoMessagesHelper, 
            $stockStateProvider, 
            $metadata, 
            $promoItemHelper
        );

        $this->cart = $cart;
        $this->promoRegistry = $promoRegistry;
        $this->stockRegistry = $stockRegistry;
        $this->promoMessagesHelper = $promoMessagesHelper;
        $this->stockStateProvider = $stockStateProvider;
        $this->metadata = $metadata;
        $this->promoItemHelper = $promoItemHelper;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param $qty
     * @param bool $ruleId
     * @param array $requestParams
     * @param null $discount
     * @param null $minimalPrice
     * @param \Magento\Quote\Model\Quote|null $quote
     */
    public function addProduct(
        \Magento\Catalog\Model\Product $product,
        $qty,
        $ruleId = false,
        $requestParams = [],
        $discount = null,
        $minimalPrice = null,
        \Magento\Quote\Model\Quote $quote = null
    ) {
        if (isset($this->addedProducts[$product->getId()])) {
            return;
        }

        if ($product->getTypeId() == 'simple') {
            $availableQty = $this->checkAvailableQty($product, $qty, $quote);

            if ($availableQty <= 0) {
                $this->promoMessagesHelper->addAvailabilityError($product);

                return;
            } else {
                if ($availableQty < $qty) {
                    $this->promoMessagesHelper->showMessage(
                        __(
                            "We apologize, but requested quantity of free gift <strong>%1</strong> is not available at the moment",
                            $product->getName()
                        ),
                        false,
                        true
                    );
                }
            }

            $qty = $availableQty;
        }

        $requestInfo = [
            'qty' => $qty,
            'options' => []
        ];

        if (!empty($requestParams)) {
            $requestInfo = array_merge_recursive($requestParams, $requestInfo);
        }

        $requestInfo['options']['ampromo_rule_id'] = $ruleId;
        $requestInfo['options']['discount'] = $discount;
        $requestInfo['options']['minimal_price'] = $minimalPrice;

        try {
            $product->setData('ampromo_rule_id', $ruleId);
            if ($quote instanceof \Magento\Quote\Model\Quote
                && !$this->cart->hasData('quote')
            ) {
                $this->cart->setQuote($quote); //prevent quote afterload event in cart::addProduct()
            }
            $cartQuote = $this->cart->getQuote();
            $quoteItems = $cartQuote->getAllItems();

            if ($this->hasProductId($cartQuote, $product->getId(), $quoteItems, $ruleId)) {
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

                $item = $cartQuote->addProduct($product, new \Magento\Framework\DataObject($requestInfo));

                if ($item instanceof \Magento\Quote\Model\Quote\Item) {
                    $this->collectTotals($item, $cartQuote);
                } else {
                    throw new LocalizedException(__($item));
                }
    
                $this->addedProducts[$product->getId()] = $item->getQty();
                 if (isset($quote) && !$quote->hasData('is_copy')) {
                    $message = __(
                                    "Free gift %1 was added to your shopping cart",
                                    $product->getName()
                                );
                    $messageManager = $objectManager->get('Magento\Framework\Message\ManagerInterface');
                    $messageManager->addSuccessMessage($message);
                }
                $salesRule = $objectManager->get('\Magento\SalesRule\Api\RuleRepositoryInterface');
                $rule = $salesRule->getById($ruleId);
                if ($rule && $rule->getIsActive()) {
                    $getSimpleAction = $rule->getSimpleAction();
                    switch ($getSimpleAction) {
                        case 'ampromo_spent':
                            $itemUpdate = $quote->getItemById($item->getId());
                            $itemUpdate->setCartPromoOption('ampromo_spent');
                            $itemUpdate->save();        
                        break;
                    } 
                }
            }
        } catch (\Exception $e) {
            $this->promoMessagesHelper->showMessage(
                $e->getMessage(),
                true,
                true
            );
        }
    }

    public function hasProductId($cartQuote, $productId, $quoteItems, $ruleId) {
        $countItem = 0;
        if ($cartQuote->hasProductId($productId)) {
            foreach ($quoteItems as $item) {
                if (empty($item->getCartPromoOption())) {
                    $appliedRuleIds = $item->getAppliedRuleIds();
                    $salesRuleIds = explode(",", $appliedRuleIds);
                    if ($salesRuleIds && count($salesRuleIds) > 0) {
                        foreach($salesRuleIds as $value) {
                            if ($value == $ruleId) {
                                // $this->writeLog('item_id = '.$item->getId().' --- '.$value.' = '.$ruleId);
                                $countItem++;
                            }
                        }
                    }
                }
            }
        }
        if ($countItem > 0) {
            $countPromoItem = $this->countPromoItem($productId);
            // $this->writeLog('countPromoItem: '.$countPromoItem);
            // $this->writeLog('countItem: '.$countItem);
            if ($countItem == $countPromoItem) {
                return false;
            }
        }
        return true;
    }

    public function countPromoItem($productId)
    {
        try {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $checkoutSession = $objectManager->create('Magento\Checkout\Model\Session');
            $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
            $connection = $resource->getConnection();
            $quote = $checkoutSession->getQuote();
            $quoteId = $quote->getId();
            if (isset($quoteId)) {
                $sql = 'SELECT COUNT(item_id) as item_id
                        FROM quote_item
                        WHERE cart_promo_option IS NOT NULL AND quote_id = '.$quoteId.' AND product_id = "'.$productId.'"';
                // $this->writeLog($sql);
                $result = $connection->fetchRow($sql);
                if (isset($result['item_id'])) {
                    return (int)$result['item_id'];
                }
            }
            return 0;
        } catch(\Exception $e) {
            $this->writeLog($e);
            return 0;
        }
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item $item
     * @param \Magento\Quote\Model\Quote $cartQuote
     */
    private function collectTotals(\Magento\Quote\Model\Quote\Item $item, \Magento\Quote\Model\Quote $cartQuote)
    {
        if ($item->getProductType() !== Configurable::TYPE_CODE) {
            $cartQuote->setTotalsCollectedFlag(false);
            $items = $cartQuote->getShippingAddress()->getAllItems();
            $items[] = $item;
            $cartQuote->getShippingAddress()->setCollectShippingRates(true);
            $cartQuote->getShippingAddress()->setData('cached_items_all', $items);
            $cartQuote->collectTotals();
        }
    }

    /**
     * @param bool $saveCart
     * @param \Magento\Quote\Model\Quote|null $quote
     * @throws \Exception
     */
    public function updateQuoteTotalQty(
        $saveCart = false,
        \Magento\Quote\Model\Quote $quote = null
    ) {
        if (!$quote) {
            $quote = $this->cart->getQuote();
        }

        $quote->setItemsCount(0);
        $quote->setItemsQty(0);
        $quote->setVirtualItemsQty(0);

        $items = $quote->getAllVisibleItems();
        foreach ($items as $item) {
            if ($item->getParentItem()) {
                continue;
            }

            $children = $item->getChildren();
            if ($children && $item->isShipSeparately()) {
                foreach ($children as $child) {
                    if ($child->getProduct()->getIsVirtual()) {
                        $qty = $quote->getVirtualItemsQty() + $child->getQty() * $item->getQty();
                        $quote->setVirtualItemsQty($qty);
                    }
                }
            }

            if ($item->getProduct()->getIsVirtual()) {
                $quote->setVirtualItemsQty($quote->getVirtualItemsQty() + $item->getQty());
            }
            $quote->setItemsCount($quote->getItemsCount()+1);
            $quote->setItemsQty((float) $quote->getItemsQty()+$item->getQty());
        }

        if ($saveCart) {
            try {
                if (version_compare($this->metadata->getVersion(), '2.2.0', '>=')) {
                    $quote->getItemsCollection()->each([$this, 'removeDeleteMark']);
                }

                $quote->save();
                $this->cart->save();
            } catch (\Exception $e) {
                $this->promoMessagesHelper->showMessage(
                    __(
                        "Something went wrong. Please try again later."
                    ),
                    true,
                    true
                );
                $this->_logger->critical($e->getTraceAsString());
            }
        }
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item $item
     */
    public function removeDeleteMark(\Magento\Quote\Model\Quote\Item $item)
    {
        if ($this->promoItemHelper->isPromoItem($item) && $item->getProductType() !== Configurable::TYPE_CODE) {
            $item->isDeleted(false);
        }
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param $qtyRequested
     * @param null $quote
     *
     * @return float|int
     */
    public function checkAvailableQty(
        \Magento\Catalog\Model\Product $product,
        $qtyRequested,
        $quote = null
    ) {
        $stockItem = $this->stockRegistry->getStockItem(
            $product->getId(),
            $product->getStore()->getWebsiteId()
        );

        $qtyAdded = 0;
        if ($quote instanceof \Magento\Quote\Model\Quote) {
            $items = $quote->getItemsCollection();
        } else {
            $items =  $this->cart->getItems();
        }
        foreach ($items as $item) {
            if ($item->getProductId() == $product->getId()) {
                $qtyAdded += $item->getQty();
            }
        }

        $totalQty = $qtyRequested + $qtyAdded;

        $checkResult = $this->stockStateProvider->checkQuoteItemQty(
            $stockItem,
            $qtyRequested,
            $totalQty,
            $qtyRequested
        );

        if ($checkResult->getData('has_error')) {
            if (!$this->stockStateProvider->checkQty($stockItem, $totalQty)) {
                return $stockItem->getQty() - $qtyAdded;
            }

            if ($stockItem->getBackorders()) {
                return $stockItem->getMaxSaleQty() - $qtyAdded;
            }

            return 0;
        } else {
            return $qtyRequested;
        }
    }

    /**
    * @param $info
    * @param $type  [error, warning, info]
    * @return 
    */
    private function writeLog($info, $type = "info") {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/chottvn_sales_amasty.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        switch($type){
            case "error":
                $logger->err($info);  
                break;
            case "warning":
                $logger->notice($info);  
                break;
            case "info":
                $logger->info($info);  
                break;
            default:
                $logger->info($info);  
        }
    }
}
