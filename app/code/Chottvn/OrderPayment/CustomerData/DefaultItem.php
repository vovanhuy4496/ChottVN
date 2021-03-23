<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Chottvn\OrderPayment\CustomerData;

use Magento\Framework\App\ObjectManager;
use Magento\Catalog\Model\Product\Configuration\Item\ItemResolverInterface;

/**
 * Default item
 */
class DefaultItem extends \Magento\Checkout\CustomerData\DefaultItem
{
    const THUMBS_WIDTH_CHECKOUT = 'market/checkout/thumbs_width_checkout';

    const THUMBS_HEIGHT_CHECKOUT = 'market/checkout/thumbs_height_checkout';
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $imageHelper;

    /**
     * @var \Magento\Msrp\Helper\Data
     */
    protected $msrpHelper;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Catalog\Helper\Product\ConfigurationPool
     */
    protected $configurationPool;

    /**
     * @var \Chottvn\PriceDecimal\Helper\Data
     */
    protected $checkoutHelper;

    /**
     * @var \Magento\Framework\Escaper
     */
    private $escaper;

    /**
     * @var ItemResolverInterface
     */
    private $itemResolver;

    // Phuoc add at 20200727 for figure out promo item
    /**
     * @var \Amasty\Promo\Helper\Item
     */
    private $amPromoHelper;

    /**
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magento\Msrp\Helper\Data $msrpHelper
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Catalog\Helper\Product\ConfigurationPool $configurationPool
     * @param \Chottvn\PriceDecimal\Helper\Data $checkoutHelper
     * @param \Magento\Framework\Escaper|null $escaper
     * @param ItemResolverInterface|null $itemResolver
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Msrp\Helper\Data $msrpHelper,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Catalog\Helper\Product\ConfigurationPool $configurationPool,
        \Chottvn\PriceDecimal\Helper\Data $checkoutHelper,
        \Magento\Framework\Escaper $escaper = null,
        ItemResolverInterface $itemResolver = null,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        // Phuoc add at 20200727 for figure out promo item
        \Amasty\Promo\Helper\Item $amPromoHelper
    ) {
        $this->configurationPool = $configurationPool;
        $this->imageHelper = $imageHelper;
        $this->msrpHelper = $msrpHelper;
        $this->urlBuilder = $urlBuilder;
        $this->checkoutHelper = $checkoutHelper;
        $this->escaper = $escaper ?: ObjectManager::getInstance()->get(\Magento\Framework\Escaper::class);
        $this->itemResolver = $itemResolver ?: ObjectManager::getInstance()->get(ItemResolverInterface::class);
        $this->scopeConfig = $scopeConfig;
        // Phuoc add at 20200727 for figure out promo item
        $this->amPromoHelper = $amPromoHelper;
        
    }

    /**
     * {@inheritdoc}
     */
    protected function doGetItemData()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $thumsWidth = $this->scopeConfig->getValue(self::THUMBS_WIDTH_CHECKOUT, $storeScope);
        $thumsHeight = $this->scopeConfig->getValue(self::THUMBS_HEIGHT_CHECKOUT, $storeScope);
        $productType = $this->item->getProductType();
        $productName = $this->escaper->escapeHtml($this->item->getProduct()->getName());
        $productNameLong = $this->item->getProduct()->getNameLongHtml();

        // huy set default stock
        $defaultStock = 0;

        // huan update
        if ($productType == 'configurable') {
            
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $checkoutSession = $objectManager->get('Magento\Checkout\Model\Session');
            $quote = $checkoutSession->getQuote();
            $quoteId = $quote->getId();

            $itemQuote = ObjectManager::getInstance()->get('Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory');
            $itemQuoteCollection = $itemQuote->create()->addFieldToFilter('quote_id', $quoteId)
                                                        ->addFieldToFilter('parent_item_id', $this->item->getId())
                                                        ->addFieldToFilter('product_type', 'simple');
            $lastItemQuote = $itemQuoteCollection->getLastItem();
            $productId = $lastItemQuote->getData('product_id');
            $productConfigurable = ObjectManager::getInstance()->create('Magento\Catalog\Model\Product')->load($productId);
            $imageHelper =  $this->imageHelper->init($productConfigurable, 'product_page_image_thumbnail')->setImageFile($productConfigurable->getImage());
            $defaultStock = $productConfigurable->getDefaultStockCustom() - $productConfigurable->sumQtyCurrentInQuoteItem();
        } else {
            $defaultStock = $this->item->getProduct()->getDefaultStockCustom() - $this->item->getProduct()->sumQtyCurrentInQuoteItem();
            $imageHelper = $this->imageHelper->init($this->getProductForThumbnail(), 'config_image_check_out');
        }
        // Phuoc add at 20200727 for figure out promo item
        $isPromoItem = $this->amPromoHelper->isPromoItem($this->item);

        // Begin: Phuoc add at 20200727 for add prefix to product name (get code from Amasty\Promo\Plugin\Quote\Item.php)
        if ($isPromoItem) {
            $prefix = $this->scopeConfig->getValue(
                'ampromo/messages/prefix',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );

            if ($prefix) {
                $productNameLong = $prefix . ' ' . $productNameLong;
            }
        }
        // End

        $thumsWidth = $thumsWidth ? $thumsWidth : $imageHelper->getWidth();
        $thumsHeight = $thumsHeight ? $thumsHeight : $imageHelper->getHeight();
        $showProduct = true;
        if ($this->item->getCartPromoOption() == 'ampromo_items' || $this->item->getCartPromoOption() == 'ampromo_cart') {
            $showProduct = false;
        }
        
        return [
            'showProduct' => $showProduct,
            'defaultStock' => $defaultStock,
            'product_name_long' => $productNameLong,
            'isPromoItem' => $isPromoItem,
            'options' => $this->getOptionList(),
            'qty' => $this->item->getQty() * 1,
            'item_id' => $this->item->getId(),
            'configure_url' => $this->getConfigureUrl(),
            'is_visible_in_site_visibility' => $this->item->getProduct()->isVisibleInSiteVisibility(),
            'product_id' => $this->item->getProduct()->getId(),
            'product_name' => $productName,
            'product_sku' => $this->item->getProduct()->getSku(),
            'product_url' => $this->getProductUrl(),
            'product_has_url' => $this->hasProductUrl(),
            'product_price_new' => $this->checkoutHelper->formatPrice($this->item->getCalculationPrice()),
            'product_price' => $this->checkoutHelper->formatPrice($this->item->getCalculationPrice()),
            'product_price_value' => $this->item->getCalculationPrice(),
            'cart_promo_option' => $this->item->getCartPromoOption(),
            // 'list_gift_product_name' => $this->getProductNameGift($this->item),
            'product_image' => [
                'src' => $imageHelper->getUrl(),
                'alt' => $imageHelper->getLabel(),
                'width' => $thumsWidth,
                'height' => $thumsHeight,
            ],
            'canApplyMsrp' => $this->msrpHelper->isShowBeforeOrderConfirm($this->item->getProduct())
                && $this->msrpHelper->isMinimalPriceLessMsrp($this->item->getProduct()),
        ];
    }
  
    /**
     * Get list of all options for product
     *
     * @return array
     * @codeCoverageIgnore
     */
    protected function getOptionList()
    {
        return $this->configurationPool->getByProductType($this->item->getProductType())->getOptions($this->item);
    }

    /**
     * @return \Magento\Catalog\Model\Product
     * @codeCoverageIgnore
     */
    protected function getProductForThumbnail()
    {
        return $this->itemResolver->getFinalProduct($this->item);
    }

    /**
     * @return \Magento\Catalog\Model\Product
     * @codeCoverageIgnore
     */
    protected function getProduct()
    {
        return $this->item->getProduct();
    }

    /**
     * Get item configure url
     *
     * @return string
     */
    protected function getConfigureUrl()
    {
        return $this->urlBuilder->getUrl(
            'checkout/cart/configure',
            ['id' => $this->item->getId(), 'product_id' => $this->item->getProduct()->getId()]
        );
    }

    /**
     * Check Product has URL
     *
     * @return bool
     */
    protected function hasProductUrl()
    {
        if ($this->item->getRedirectUrl()) {
            return true;
        }

        $product = $this->item->getProduct();
        $option = $this->item->getOptionByCode('product_type');
        if ($option) {
            $product = $option->getProduct();
        }

        if ($product->isVisibleInSiteVisibility()) {
            return true;
        } else {
            if ($product->hasUrlDataObject()) {
                $data = $product->getUrlDataObject();
                if (in_array($data->getVisibility(), $product->getVisibleInSiteVisibilities())) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Retrieve URL to item Product
     *
     * @return string
     */
    protected function getProductUrl()
    {
        if ($this->item->getRedirectUrl()) {
            return $this->item->getRedirectUrl();
        }

        $product = $this->item->getProduct();
        $option = $this->item->getOptionByCode('product_type');
        
        if ($option) {
            $product = $option->getProduct();
        }
        $producturl = $product->getUrlModel()->getUrl($product);
        $productType = $this->item->getProductType();
        // huan update
        if($productType == 'configurable'){
            $optionList = $this->getOptionList();
            if($optionList){
                $producturl = $producturl . '#';
                foreach($optionList as $value){
                        if(isset($value['option_id']) && $value['option_value']){
                            $eavModel = ObjectManager::getInstance()->create('Magento\Catalog\Model\ResourceModel\Eav\Attribute');
                            $attr = $eavModel->load($value['option_id']);
                            $attributeCode = $attr->getAttributeCode();//Get attribute code from its id
                            $producturl = $producturl .$attributeCode. '='. $value['option_value'] .'&';
                        }
                }
               $producturl = substr($producturl, 0, -1);    
            }
            return $producturl;
        }
        
        return $producturl;
    }
        /**
     * @param $info
     * @param $type  [error, warning, info]
     * @return 
     */
    private function writeLog($info, $type = "info")
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/DefaultItem.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        switch ($type) {
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
