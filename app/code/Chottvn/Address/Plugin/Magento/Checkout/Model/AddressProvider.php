<?php

namespace Chottvn\Address\Plugin\Magento\Checkout\Model;

use Magento\Customer\Model\Context as CustomerContext;

class AddressProvider
{
    /**
     * @var \Magento\Framework\App\Http\Context
     */
    private $httpContext;

    /**
     * @var \Magento\Customer\Model\Address
     */
    private $addressFactory;

    /**
     * @var \Chottvn\Address\Helper\Data
     */
    private $helperData;

    /**
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magento\Customer\Model\AddressFactory $addressFactory
     * @param \Chottvn\Address\Helper\Data $helperData
     */
    public function __construct(
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Customer\Model\AddressFactory $addressFactory,
        \Chottvn\Address\Helper\Data $helperData
    ) {
        $this->httpContext = $httpContext;
        $this->addressFactory = $addressFactory;
        $this->helperData = $helperData;
    }

    /**
     * Add custom address attribute to checkout config
     *
     * @param \Magento\Checkout\Model\DefaultConfigProvider $subject
     * @return array
     */
    public function afterGetConfig(
        \Magento\Checkout\Model\DefaultConfigProvider $subject,
        $result
    ) {
        if ($this->httpContext->getValue(CustomerContext::CONTEXT_AUTH)) {
            $additionalFields = $this->helperData->getExtraCheckoutAddressFields();
            foreach ($result['customerData']['addresses'] as $key => $address) {
                $addressData = $this->addressFactory->create()->load($address['id']);
                foreach ($additionalFields as $field) {
                    $result['customerData']['addresses'][$key][$field] = $addressData->getData($field);
                }
            }
        }

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $checkoutSession = $objectManager->get('Magento\Checkout\Model\Session');
        $storeManager = $objectManager->get('Magento\Store\Model\StoreManagerInterface');
        // $reviewFactory = $objectManager->create('Magento\Review\Model\Review');
        $productBrandHelper = $objectManager->get('Ves\Brand\Helper\ProductBrand');
        $imageHelper = $objectManager->get('Magento\Catalog\Helper\Image');
        $blockRules = $objectManager->create('Chottvn\Frontend\Block\Rules');
        $entityAttribute = $objectManager->get('Magento\Catalog\Model\ResourceModel\Eav\Attribute');
        $productCollectionFactory = $objectManager->get('Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');
        $quoteFactory = $objectManager->create('Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory');
        $checkStatusQtyProduct = $objectManager->get('Chottvn\Sales\Rewrite\Magento\Catalog\Block\Product\View');
        $salesRule = $objectManager->get('\Magento\SalesRule\Api\RuleRepositoryInterface');

        $getQuotes = $checkoutSession->getQuote();
        $quoteId = $getQuotes->getId();
        $storeId = $storeManager->getStore()->getId();

        try {
            if (isset($result['totalsData']) && $result['totalsData']) {
                $items = $result['totalsData']['items'];
                foreach ($items as $index => $item) {
                    if (isset($item['item_id'])) {
                        $_attributes = array();
                        $ruleNames = array();
                        // $getRuleName = array();
                        // $getRuleId = array();
                        // $_getAmpromoRule = array();
                        // $renameRule = array();
                        // $productUrlRule = array();
                        // $cartPriceRules_1_N = array();
                        // $cartPriceRulesSku = array();
                        // $cartPriceRulesSkus = array();
                        // $cartPriceRulesIds = array();
                        $skus = array();
                        $image_url = null;
                        $defaultStock = 0;
                        // $flagKeyRenameRule = 0;
                        $cartPromoItemIds = '';

                        $result['quoteItemData'][$index]['attributes'] = $_attributes;
                        $result['quoteItemData'][$index]['customOptionsConfig'] = array();
                        // $result['quoteItemData'][$index]['ampromo_items'] = array();
                        // $result['quoteItemData'][$index]['cart_promo_ids'] = array();

                        $quoteItem = $getQuotes->getItemById($item['item_id']);
                        $productId = $quoteItem->getProduct()->getId();
                        $product = $objectManager->create('Magento\Catalog\Model\Product')->load($productId);
                        // $this->writeLog('$item["item_id"]: '.$item['item_id'].' => '.'productId: '.$productId);
                        // $this->writeLog('cartPromoItemIds: '.$quoteItem->getCartPromoItemIds());
                        if (!empty($quoteItem->getCartPromoItemIds())) {
                            $cartPromoItemIds = $quoteItem->getCartPromoItemIds();
                        }

                        if ($product->getTypeId() == 'configurable') {
                            $_product = $quoteItem->getProduct();
                            $attributes = $_product->getTypeInstance(true)->getSelectedAttributesInfo($_product);
                            $result['quoteItemData'][$index]['customOptionsConfig'] = $attributes;
                            foreach ($attributes as $attr) {
                                $attributeId = $attr['option_id'];
                                $_attr = $entityAttribute->load($attributeId);
                                $attributeCode = $_attr->getAttributeCode();
                                $_attributes[$attributeCode] = $attr['option_value'];
                            }
                            $result['quoteItemData'][$index]['attributes'] = $_attributes;
                            $itemQuoteCollection = $quoteFactory->create()->addFieldToFilter('quote_id', $quoteId)
                                                                          ->addFieldToFilter('parent_item_id', $item['item_id'])
                                                                          ->addFieldToFilter('product_type', 'simple');
                            $lastItemQuote = $itemQuoteCollection->getLastItem();
                            $productConfigurableId = $lastItemQuote->getData('product_id');
                            if ($productConfigurableId) {
                                $productConfigurable = $objectManager->create('Magento\Catalog\Model\Product')->load($productConfigurableId);
                                $image_url = $imageHelper->init($productConfigurable, 'product_page_image_thumbnail')
                                                        ->setImageFile($productConfigurable->getImage())->getUrl();
                                $checkStatusQtyProduct = $objectManager->get('Chottvn\Sales\Rewrite\Magento\Catalog\Block\Product\View');
                                // $defaultStock = $checkStatusQtyProduct->checkStatusQtyProduct($productConfigurable);
                                $defaultStock = $productConfigurable->getDefaultStockCustom();
                            }
                        } else {
                            $image_url = $imageHelper->init($product, 'product_page_image_thumbnail')->setImageFile($product->getImage())->getUrl();
                            // $defaultStock = $checkStatusQtyProduct->checkStatusQtyProduct($product);
                            $defaultStock = $product->getDefaultStockCustom();
                        }

                        $hasProductUrl = $this->hasProductUrl($product);
                        $productBrand = $productBrandHelper->getFirstBrandByProduct($product);

                        if (!isset($productBrand)) {
                            $productBrandName = '';
                        } else {
                            $productBrandName = $productBrand->getName();
                        }
                        if (isset($image_url) && !($this->url_exists($image_url))) {
                            $image_url = $imageHelper->getDefaultPlaceholderUrl('thumbnail');
                        }
                        if (!isset($image_url)) {
                            $image_url = $imageHelper->getDefaultPlaceholderUrl('thumbnail');
                        }

                        $getQuoteItem = $quoteFactory->create()->addFieldToFilter('quote_id', $quoteId)
                                                            ->addFieldToFilter('cart_promo_parent_id', $productId)
                                                            ->addFieldToFilter('cart_promo_item_ids', $cartPromoItemIds)
                                                            ->addFieldToFilter('cart_promo_parent_item_id', $item['item_id'])
                                                            ->addFieldToFilter('cart_promo_option', 'ampromo_items');
                        if ($getQuoteItem) {
                            foreach($getQuoteItem as $item) {
                                $productId = $item->getProductId();
                                $productPromo = $objectManager->create('Magento\Catalog\Model\Product')->load($productId);
                                array_push($ruleNames, $item->getName());
                                $hasProductPromoUrl = $this->hasProductUrl($productPromo);
                                $getProductPromoUrl = $productPromo->getProductUrl();
                                $dataProdPromo['getProductPromoUrl'] = $getProductPromoUrl;
                                $dataProdPromo['hasProductPromoUrl'] = $hasProductPromoUrl;
                                $dataProdPromo['getProductUnit'] = $productPromo->getProductUnit();
                                $dataProdPromo['getCartPromoQty'] = (int)$item->getCartPromoQty();
                                $result['quoteItemData'][$index][$item->getName()] = $dataProdPromo;
                            }
                        }
                        $result['quoteItemData'][$index]['checkPromoItems'] = 'null';
                        $getAllItems = $getQuotes->getAllItems();
                        foreach ($getAllItems as $item) {
                            // $this->writeLog($item->getProduct()->getId());
                            if ($item->getProduct()->getId() == $productId) {
                                $salesRuleId = $item->getAmpromoRuleId();
                                if (isset($salesRuleId)) {
                                    $rule = $salesRule->getById($salesRuleId);
                                    if ($rule && $rule->getIsActive()) {
                                        $getSimpleAction = $rule->getSimpleAction();
                                        $stringSkus = $rule->getExtensionAttributes()->getAmpromoRule()->getSku();
                                        $skus = explode(',', $stringSkus);
                                        if ($getSimpleAction == 'ampromo_cart' && count($skus) > 0) {
                                            // $this->writeLog($salesRuleId);
                                            // $this->writeLog($product->getNameLongHtml());
                                            $result['quoteItemData'][$index]['checkPromoItems'] = 'ampromo_cart';
                                            break;
                                        }
                                        if ($getSimpleAction == 'ampromo_spent' && count($skus) > 0) {
                                            $result['quoteItemData'][$index]['checkPromoItems'] = 'ampromo_spent';
                                            break;
                                        }
                                    }
                                }
                            }
                        }
                        // $this->writeLog($product->getTypeId());
                        if ($product->getTypeId() == 'virtual') {
                            $result['quoteItemData'][$index]['checkPromoItems'] = 'virtual';
                        }
                        // $cartRuleNames = $blockRules->getCartRuleByProduct($product);
                        // $catalogRuleNames = $blockRules->getCatalogRuleByProduct($product);
                        // $ruleNames = array_merge($cartRuleNames, $catalogRuleNames);
                        // $this->writeLog($ruleNames);
                        // $salesRuleIds = $blockRules->getIdCartRuleByProduct($product, $type = array(), $without_type = array());
                        // foreach($salesRuleIds as $salesRuleId) {
                        //     if ($salesRuleId) {
                        //         $salesRule = $objectManager->get('\Magento\SalesRule\Api\RuleRepositoryInterface');
                        //         $rule = $salesRule->getById($salesRuleId);
                        //         if ($rule && $rule->getIsActive()) {
                        //             $getAmpromoRule = $rule->getExtensionAttributes()->getAmpromoRule()->getType();
                        //             $stringSkus = $rule->getExtensionAttributes()->getAmpromoRule()->getSku();
                        //             $skus = explode(',', $stringSkus);
                        //             // $this->writeLog('salesRuleId: '.$salesRuleId.' => skus: '.count($skus));
                        //             // $this->writeLog($skus);
                        //             // $skus = explode(', ', $stringSkus);
                        //             // if (!isset($skus[0])) {
                        //             //     $skus = explode(',', $stringSkus);
                        //             // }
                        //             $getSimpleAction = $rule->getSimpleAction();
                        //             switch ($getSimpleAction) {
                        //                 // Auto add promo items with products
                        //                 case 'ampromo_items':
                        //                     // One of the SKUs below
                        //                     if ($getAmpromoRule == 1) {
                        //                         foreach($skus as $key => $sku) {
                        //                             $getRuleName[$rule->getRuleId()][$sku] = $rule->getName();
                        //                             $getRuleId[$rule->getRuleId()][$sku] = $rule->getRuleId();
                        //                             $_getAmpromoRule[$rule->getRuleId()] = 1;
                        //                             $result['quoteItemData'][$index]['ampromo_items'] = $getRuleName;
                        //                             $result['quoteItemData'][$index]['cart_promo_ids'] = $getRuleId;
                        //                             $result['quoteItemData'][$index]['getAmpromoRule'] = $_getAmpromoRule;

                        //                             // get value de check radio
                        //                             $getQuoteItem = $quoteFactory->create()->addFieldToFilter('quote_id', $quoteId)
                        //                                                 ->addFieldToFilter('cart_promo_parent_id', $productId)
                        //                                                 ->addFieldToFilter('cart_promo_qty', (int)$rule->getDiscountAmount())
                        //                                                 ->addFieldToFilter('cart_promo_item_ids', $cartPromoItemIds)
                        //                                                 ->addFieldToFilter('cart_promo_ids', $rule->getRuleId())
                        //                                                 ->addFieldToFilter('cart_promo_option', 'ampromo_items');

                        //                             // $this->writeLog($getQuoteItem->getSelect()->__toString());
                        //                             $getLastItem = $getQuoteItem->getLastItem();
                        //                             // $this->writeLog(count($getLastItem->getData()));
                                                    
                        //                             $checked = '';
                        //                             if ($getLastItem->getSku() == $sku) {
                        //                                 $checked = 'checked';
                        //                             }

                        //                             // Get product with sku
                        //                             $collection = $productCollectionFactory->create();
                        //                             $collection->addAttributeToSelect('*');
                        //                             $collection->addAttributeToFilter('sku', ['eq' => $sku]);
                        //                             $prd = $collection->getLastItem();
                        //                             $hasProductUrlRule = $this->hasProductUrl($prd);

                        //                             if (count($skus) == 1) {
                        //                                 foreach($ruleNames as $key => $name) {
                        //                                     if ($name == $rule->getName() && ($key == 0 || $key > $flagKeyRenameRule)) {
                        //                                         $renameRule[$key] = $prd->getName();
                        //                                         if ($hasProductUrlRule) {
                        //                                             $productUrlRule[$key] = $prd->getProductUrl();
                        //                                         }
                        //                                         $flagKeyRenameRule = $key;
                        //                                     }
                        //                                 }
                        //                             } else {
                        //                                 if (count($skus) > 1) {
                        //                                     foreach($ruleNames as $key => $name) {
                        //                                         if ($name == $rule->getName() && ($key == 0 || $key > $flagKeyRenameRule)) {
                        //                                             if ($checked == 'checked') {
                        //                                                 if ($hasProductUrlRule) {
                        //                                                     $productUrlRule[$key] = $prd->getProductUrl();
                        //                                                 }
                        //                                                 $_renameRule = $getLastItem->getName();
                        //                                                 $renameRule[$key] = $_renameRule;
                        //                                                 $flagKeyRenameRule = $key;
                        //                                             }
                        //                                             // $_renameRule = 'Khách hàng chọn 01 trong '.(count($skus) > 9 ? count($skus) : '0'.count($skus)).' quà tặng:';
                        //                                         }
                        //                                     }
                        //                                 }

                        //                                 $result['quoteItemData'][$index][$sku][$rule->getRuleId()] = $prd->getData();
                        //                                 $result['quoteItemData'][$index][$sku][$rule->getRuleId()]['checked'] = $checked;
                        //                                 $result['quoteItemData'][$index][$sku][$rule->getRuleId()]['hasProductUrlRule'] = $hasProductUrlRule;
                        //                                 $result['quoteItemData'][$index][$sku][$rule->getRuleId()]['getProductUrl'] = $prd->getProductUrl();
                        //                                 $result['quoteItemData'][$index][$sku][$rule->getRuleId()]['discount_amount'] = $rule->getDiscountAmount();
                        //                                 if (!empty($getLastItem->getCartPromoItemIds())) {
                        //                                     $result['quoteItemData'][$index][$sku][$rule->getRuleId()]['item_id'] = $getLastItem->getItemId();
                        //                                     $result['quoteItemData'][$index][$sku][$rule->getRuleId()]['oldCartPromoItemIds'] = $getLastItem->getCartPromoItemIds();
                        //                                 }
                        //                             }
                        //                         }
                        //                         // $this->writeLog('--------------------1--------------------');
                        //                     }
                        //                     // All SKUs below
                        //                     if ($getAmpromoRule == 0) {
                        //                         foreach($skus as $key => $sku) {
                        //                             if (count($skus) > 1) {
                        //                                 foreach($ruleNames as $key => $name) {
                        //                                     if ($name == $rule->getName() && ($key == 0 || $key > $flagKeyRenameRule)) {
                        //                                         $_renameRule = 'Khách hàng được '.(count($skus) > 9 ? count($skus) : '0'.count($skus)).' quà tặng:';
                        //                                         $renameRule[$key] = $_renameRule;
                        //                                         $flagKeyRenameRule = $key;
                        //                                     }
                        //                                 }
                        //                             }
                        //                             // $this->writeLog('sku: '.$sku);
                        //                             $getRuleName[$rule->getRuleId()][$sku] = $rule->getName();
                        //                             $getRuleId[$rule->getRuleId()][$sku] = $rule->getRuleId();
                        //                             $_getAmpromoRule[$rule->getRuleId()] = 0;
                        //                             $result['quoteItemData'][$index]['ampromo_items'] = $getRuleName;
                        //                             $result['quoteItemData'][$index]['cart_promo_ids'] = $getRuleId;
                        //                             $result['quoteItemData'][$index]['getAmpromoRule'] = $_getAmpromoRule;

                        //                             // Get product with sku
                        //                             $collection = $productCollectionFactory->create();
                        //                             $collection->addAttributeToSelect('*');
                        //                             $collection->addAttributeToFilter('sku', ['eq' => $sku]);
                        //                             foreach($collection as $prd) {
                        //                                 $hasProductUrlRule = $this->hasProductUrl($prd);

                        //                                 // neu chi co 1 sp
                        //                                 if (count($skus) == 1) {
                        //                                     foreach($ruleNames as $key => $name) {
                        //                                         if ($name == $rule->getName() && ($key == 0 || $key > $flagKeyRenameRule)) {
                        //                                             $renameRule[$key] = $prd->getName();
                        //                                             if ($hasProductUrlRule) {
                        //                                                 $productUrlRule[$key] = $prd->getProductUrl();
                        //                                             }
                        //                                             $flagKeyRenameRule = $key;
                        //                                         }
                        //                                     }
                        //                                 } else {
                        //                                     $result['quoteItemData'][$index][$sku][$rule->getRuleId()] = $prd->getData();
                        //                                     $result['quoteItemData'][$index][$sku][$rule->getRuleId()]['checked'] = 'autochecked';
                        //                                     $result['quoteItemData'][$index][$sku][$rule->getRuleId()]['hasProductUrlRule'] = $hasProductUrlRule;
                        //                                     $result['quoteItemData'][$index][$sku][$rule->getRuleId()]['getProductUrl'] = $prd->getProductUrl();
                        //                                     $result['quoteItemData'][$index][$sku][$rule->getRuleId()]['discount_amount'] = $rule->getDiscountAmount();
                        //                                 }
                        //                             }
                        //                         }
                        //                         // $this->writeLog('--------------------0--------------------');
                        //                     }
                        //                 break;
                        //             }
                        //         }
                        //     }
                        // }
                        // $salesRuleIds = $blockRules->getIdCartRuleByProduct($product, $type = array(), $without_type = array());
                        // foreach($salesRuleIds as $salesRuleId) {
                        //     if ($salesRuleId) {
                        //         $salesRule = $objectManager->get('\Magento\SalesRule\Api\RuleRepositoryInterface');
                        //         $rule = $salesRule->getById($salesRuleId);
                        //         if ($rule && $rule->getIsActive()) {
                        //             $getAmpromoRule = $rule->getExtensionAttributes()->getAmpromoRule()->getType();
                        //             $stringSkus = $rule->getExtensionAttributes()->getAmpromoRule()->getSku();
                        //             $skus = explode(',', $stringSkus);
                        //             $getSimpleAction = $rule->getSimpleAction();
                        //             switch ($getSimpleAction) {
                        //                 // Auto add promo items with products
                        //                 case 'ampromo_items':
                        //                     // One of the SKUs below
                        //                     if ($getAmpromoRule == 1 && count($skus) > 1) {
                        //                         foreach($skus as $key => $sku) {
                        //                             // get value de check radio
                        //                             $getQuoteItem = $quoteFactory->create()->addFieldToFilter('quote_id', $quoteId)
                        //                                                 ->addFieldToFilter('cart_promo_parent_id', $productId)
                        //                                                 ->addFieldToFilter('cart_promo_qty', (int)$rule->getDiscountAmount())
                        //                                                 ->addFieldToFilter('cart_promo_item_ids', $cartPromoItemIds)
                        //                                                 ->addFieldToFilter('cart_promo_parent_item_id', $item['item_id'])
                        //                                                 ->addFieldToFilter('cart_promo_ids', $rule->getRuleId())
                        //                                                 ->addFieldToFilter('cart_promo_option', 'ampromo_items')
                        //                                                 ->addFieldToFilter('sku', $sku);
                        //                             // $this->writeLog($getQuoteItem->getSelect()->__toString());

                        //                             $getLastItem = $getQuoteItem->getLastItem();
                        //                             if ($getLastItem->getSku() == $sku) {
                        //                                 $productPromo = $objectManager->create('Magento\Catalog\Model\Product')->load($getLastItem->getProductId());
                        //                                 $hasProductPromoUrl = $this->hasProductUrl($productPromo);
                        //                                 $getProductPromoUrl = $productPromo->getProductUrl();
                        //                                 $dataProdPromo['getProductPromoUrl'] = $getProductPromoUrl;
                        //                                 $dataProdPromo['hasProductPromoUrl'] = $hasProductPromoUrl;
                        //                                 $dataProdPromo['productNamePromo'] = $getLastItem->getName();
                        //                                 $cartPriceRules_1_N[$rule->getName()] = $dataProdPromo;
                        //                             }
                        //                         }
                        //                     } else {
                        //                         if ($getAmpromoRule == 0 && count($skus) > 1) {
                        //                             foreach($skus as $key => $sku) {
                        //                                 // get value de check radio
                        //                                 $getQuoteItem = $quoteFactory->create()->addFieldToFilter('quote_id', $quoteId)
                        //                                                     ->addFieldToFilter('cart_promo_parent_id', $productId)
                        //                                                     ->addFieldToFilter('cart_promo_qty', (int)$rule->getDiscountAmount())
                        //                                                     ->addFieldToFilter('cart_promo_item_ids', $cartPromoItemIds)
                        //                                                     ->addFieldToFilter('cart_promo_parent_item_id', $item['item_id'])
                        //                                                     ->addFieldToFilter('cart_promo_option', 'ampromo_items')
                        //                                                     ->addFieldToFilter('sku', $sku);
    
                        //                                 $getLastItem = $getQuoteItem->getLastItem();
                        //                                 if ($getLastItem->getSku() == $sku) {
                        //                                     $productPromo = $objectManager->create('Magento\Catalog\Model\Product')->load($getLastItem->getProductId());
                        //                                     // $productBrandPromo = $productBrandHelper->getFirstBrandByProduct($productPromo);
    
                        //                                     // if (!isset($productBrandPromo)) {
                        //                                     //     $productBrandPromo = '';
                        //                                     // } else {
                        //                                     //     $productBrandPromo = $productBrandPromo->getName();
                        //                                     // }
                        //                                     // $productModelPromo = $productPromo->getModel();
                        //                                     // $imagePromo = $imageHelper->init($productPromo, 'product_page_image_thumbnail')->setImageFile($productPromo->getImage())->getUrl();
                        //                                     // if (isset($imagePromo) && !($this->url_exists($imagePromo))) {
                        //                                     //     $imagePromo = $imageHelper->getDefaultPlaceholderUrl('thumbnail');
                        //                                     // }
                        //                                     // if (!isset($imagePromo)) {
                        //                                     //     $imagePromo = $imageHelper->getDefaultPlaceholderUrl('thumbnail');
                        //                                     // }
                        //                                     $hasProductPromoUrl = $this->hasProductUrl($productPromo);
                        //                                     $getProductPromoUrl = $productPromo->getProductUrl();
                        //                                     // $cartPriceRulesSkus[$rule->getRuleId()]['imagePromo'] = $imagePromo;
                        //                                     // $cartPriceRulesSkus[$rule->getRuleId()]['productModelPromo'] = $productModelPromo;
                        //                                     // $cartPriceRulesSkus[$rule->getRuleId()]['getProductPromoUrl'] = $getProductPromoUrl;
                        //                                     // $cartPriceRulesSkus[$rule->getRuleId()]['hasProductPromoUrl'] = $hasProductPromoUrl;
                        //                                     // $cartPriceRulesSkus[$rule->getRuleId()]['productBrandPromo'] = $productBrandPromo;
                        //                                     // $cartPriceRulesSkus[$rule->getRuleId()]['productNamePromo'] = $getLastItem->getName();
                        //                                     // $cartPriceRulesSkus[$rule->getRuleId()]['ruleNamePromo'] = $rule->getName();
                        //                                     $dataProdPromo['getProductPromoUrl'] = $getProductPromoUrl;
                        //                                     $dataProdPromo['hasProductPromoUrl'] = $hasProductPromoUrl;
                        //                                     $dataProdPromo['productNamePromo'] = $getLastItem->getName();
                        //                                     // $dataProdPromo['getRuleName'] = $rule->getName();
                        //                                     // $dataProdPromo['getRuleId'] = $rule->getRuleId();
                        //                                     $cartPriceRulesSkus[$sku] = $dataProdPromo;
                        //                                     // $cartPriceRulesIds[$sku] = $rule->getRuleId();
                        //                                 }
                        //                             }
                        //                             array_push($cartPriceRulesIds, $rule->getName());
                        //                             $result['quoteItemData'][$index][$rule->getName()] = $cartPriceRulesSkus;
                        //                         } else {
                        //                             $sku = $skus[0];
                        //                             // get value de check radio
                        //                             $getQuoteItem = $quoteFactory->create()->addFieldToFilter('quote_id', $quoteId)
                        //                                                 ->addFieldToFilter('cart_promo_parent_id', $productId)
                        //                                                 ->addFieldToFilter('cart_promo_qty', (int)$rule->getDiscountAmount())
                        //                                                 ->addFieldToFilter('cart_promo_item_ids', $cartPromoItemIds)
                        //                                                 ->addFieldToFilter('cart_promo_parent_item_id', $item['item_id'])
                        //                                                 ->addFieldToFilter('cart_promo_option', 'ampromo_items')
                        //                                                 ->addFieldToFilter('sku', $sku);
                        //                             // $this->writeLog($getQuoteItem->getSelect()->__toString());

                        //                             $getLastItem = $getQuoteItem->getLastItem();
                        //                             if ($getLastItem->getSku() == $sku) {
                        //                                 $productPromo = $objectManager->create('Magento\Catalog\Model\Product')->load($getLastItem->getProductId());
                        //                                 // $productBrandPromo = $productBrandHelper->getFirstBrandByProduct($productPromo);

                        //                                 // if (!isset($productBrandPromo)) {
                        //                                 //     $productBrandPromo = '';
                        //                                 // } else {
                        //                                 //     $productBrandPromo = $productBrandPromo->getName();
                        //                                 // }
                        //                                 // $productModelPromo = $productPromo->getModel();
                        //                                 // $imagePromo = $imageHelper->init($productPromo, 'product_page_image_thumbnail')->setImageFile($productPromo->getImage())->getUrl();
                        //                                 // if (isset($imagePromo) && !($this->url_exists($imagePromo))) {
                        //                                 //     $imagePromo = $imageHelper->getDefaultPlaceholderUrl('thumbnail');
                        //                                 // }
                        //                                 // if (!isset($imagePromo)) {
                        //                                 //     $imagePromo = $imageHelper->getDefaultPlaceholderUrl('thumbnail');
                        //                                 // }
                        //                                 $hasProductPromoUrl = $this->hasProductUrl($productPromo);
                        //                                 $getProductPromoUrl = $productPromo->getProductUrl();
                        //                                 // $dataProdPromo['imagePromo'] = $imagePromo;
                        //                                 // $dataProdPromo['productModelPromo'] = $productModelPromo;
                        //                                 $dataProdPromo['getProductPromoUrl'] = $getProductPromoUrl;
                        //                                 $dataProdPromo['hasProductPromoUrl'] = $hasProductPromoUrl;
                        //                                 // $dataProdPromo['productBrandPromo'] = $productBrandPromo;
                        //                                 $dataProdPromo['productNamePromo'] = $getLastItem->getName();
                        //                                 $cartPriceRulesSku[$rule->getName()] = $dataProdPromo;
                        //                                 // $this->writeLog($cartPriceRulesSku);
                        //                             }
                        //                         }
                        //                     }
                        //                 break;
                        //             }
                        //         }
                        //     }
                        // }
                        // $this->writeLog($product->getProductUrl());
                        // $reviewFactory->getEntitySummary($product, $storeId);
                        // $ratingSummary = $product->getRatingSummary()->getRatingSummary();
                        // $reviewsCount = $product->getRatingSummary()->getReviewsCount();

                        // $result['quoteItemData'][$index]['cart_price_rules_1_N'] = $cartPriceRules_1_N;
                        // $result['quoteItemData'][$index]['cartPriceRulesSku'] = $cartPriceRulesSku;
                        // // $result['quoteItemData'][$index]['cartPriceRulesSkus'] = $cartPriceRulesSkus;
                        // $result['quoteItemData'][$index]['cartPriceRulesIds'] = $cartPriceRulesIds;
                        // $result['quoteItemData'][$index]['salesRuleIds'] = $salesRuleIds;
                        // $result['quoteItemData'][$index]['renameRule'] = $renameRule;
                        // $result['quoteItemData'][$index]['productUrlRule'] = $productUrlRule;
                        // $result['quoteItemData'][$index]['ratingSummary'] = $ratingSummary;
                        // $result['quoteItemData'][$index]['reviewsCount'] = $reviewsCount;
                        $result['quoteItemData'][$index]['getModel'] = $product->getModel();
                        $result['quoteItemData'][$index]['productBrand'] = $productBrandName;
                        $result['quoteItemData'][$index]['guarantee'] = $product->getGuarantee();
                        $result['quoteItemData'][$index]['productUnit'] = $product->getProductUnit();
                        $result['quoteItemData'][$index]['image_url'] = $image_url;
                        $result['quoteItemData'][$index]['getNameLongHtml'] = $product->getNameLongHtml();
                        $result['quoteItemData'][$index]['getNameShort'] = $product->getNameShort();
                        $result['quoteItemData'][$index]['hasProductUrl'] = $hasProductUrl;
                        $result['quoteItemData'][$index]['ruleNames'] = $ruleNames;
                        $result['quoteItemData'][$index]['defaultStock'] = $defaultStock;
                        $result['quoteItemData'][$index]['getProductUrl'] = $product->getProductUrl();
                    }
                }
                $this->writeLog('--------------------------------------------------------------------------');
            }
        } catch (\Exception $e) {
            $this->writeLog($e);
        }
        return $result;
    }

    public function url_exists($url) {
        $url_headers = get_headers($url);
        if(!$url_headers || $url_headers[0] == 'HTTP/1.0 404 Not Found') {
            $exists = false;
        }
        else {
            $exists = true;
        }
        return $exists;
    }

    protected function hasProductUrl($product)
    {
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
	* @param $info
	* @param $type  [error, warning, info]
	* @return 
	*/
	private function writeLog($info, $type = "info") {
		$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/default_config_provider.log');
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
