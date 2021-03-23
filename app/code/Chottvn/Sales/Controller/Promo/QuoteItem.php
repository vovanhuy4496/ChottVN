<?php
namespace Chottvn\Sales\Controller\Promo;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Registry;

class QuoteItem extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Quote\Api\Data\CartItemInterfaceFactory
     */
    protected $cartItemFactory;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $product;

    protected $cartRepositoryInterface;

    protected $quoteFactory;

    protected $cart;

    protected $resultFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Quote\Api\Data\CartItemInterfaceFactory $cartItemFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $product,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepositoryInterface,
        \Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory $quoteFactory
    ) {
        $this->resultFactory = $context->getResultFactory();
        $this->cartItemFactory = $cartItemFactory;
        $this->product = $product;
        $this->cart = $cart;
        $this->cartRepositoryInterface = $cartRepositoryInterface;
        $this->quoteFactory = $quoteFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $itemManagement = $objectManager->get('Chottvn\Sales\Rewrite\Amasty\Checkout\Model\ItemManagement');
        $status = true;
        $checkoutSession = $objectManager->get('Magento\Checkout\Model\Session');
        $quote = $checkoutSession->getQuote();
        $quoteId = $quote->getId();
        $defaultStockQty = array();

        try {
            if ($this->getRequest()->isAjax() && $this->getRequest()->getParam('type') == 'checkDefaultStock') {
                $itemId = $this->getRequest()->getParam('itemId');
                $formData = $this->getRequest()->getParam('formData');
                $params = $itemManagement->parseStr($formData);
                $inputQty = (int)$params['qty'];
                $quoteItem = $this->quoteFactory->create()->addFieldToFilter('quote_id', $quoteId)
                                                        ->addFieldToFilter('item_id', $itemId);
                $quoteItem = $quoteItem->getLastItem();

                if ($quoteItem->getProductType() == 'configurable') {
                    $productSimple = $this->quoteFactory->create()->addFieldToFilter('quote_id', $quoteId)
                                                                  ->addFieldToFilter('parent_item_id', $itemId)
                                                                  ->addFieldToFilter('product_type', 'simple');
                    $productSimpleLast = $productSimple->getLastItem();
                    $productSimpleId = $productSimpleLast->getProductId();
                    if (!empty($productSimpleId)) {
                        $productSimple = $objectManager->create('Magento\Catalog\Model\Product')->load($productSimpleId);
                        $defaultStockQty = $this->checkDefaultStock($productSimple, $inputQty, $this->sumQtyWithoutProduct($productSimpleLast->getId(), $quoteId, $productSimpleLast->getProductId()));
                    }
                } else {
                    $productMain = $objectManager->create('Magento\Catalog\Model\Product')->load($quoteItem->getProductId());
                    $defaultStockQty = $this->checkDefaultStock($productMain, $inputQty, $this->sumQtyWithoutProduct($itemId, $quoteId, $quoteItem->getProductId()));
                }

                // $this->writeLog($checkDefaultStock);
                if (!empty($defaultStockQty)) {
                    $status = false;

                    $response = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
                                    ->setData([
                                        'status' => $status,
                                        'defaultStockQty' => $defaultStockQty
                                    ]);
                    return $response;
                } else {
                    $quoteItems = $this->quoteFactory->create()->addFieldToFilter('quote_id', $quoteId)
                                                            ->addFieldToFilter('cart_promo_parent_item_id', $itemId);
                    if (count($quoteItems->getData()) > 0) {
                        foreach ($quoteItems as $item) {
                            $updateQtyPromo = (int)$inputQty * (int)$item->getCartPromoQty();
            
                            // check default stock sp qtang
                            $product = $this->product->get($item->getSku());
                            // $defaultStockQty = $this->checkDefaultStock($product, (int)$updateQtyPromo);
                            $defaultStockQty = $this->checkDefaultStockPromo($product, $updateQtyPromo, $this->sumQtyWithoutProduct($item->getId(), $quoteId, $item->getProductId()));
                            if (!empty($defaultStockQty)) {
                                // $this->writeLog($defaultStockQty);
                                $status = false;
                                break;
                            }
                        }
                    }
                }

                $response = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
                                    ->setData([
                                        'status' => $status,
                                        'defaultStockQty' => $defaultStockQty
                                    ]);
                
                return $response;
            }
            if ($this->getRequest()->isAjax() && $this->getRequest()->getParam('type') == 'showPromoItems') {
                $productPromo = $this->getProductPromo();

                $response = $this->resultFactory
                    ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
                    ->setData([
                        'status'  => true,
                        'html' => $productPromo
                    ]);               
                return $response;
            }
        } catch(\Exception $e) {
            $this->writeLog($e);
        } 
        return null;
    }

    // public function checkDefaultStockPromo($product, $requestQty)
    // {
    //     $result = array();
    //     $defaultStockCustom = $product->getDefaultStockCustom(); // so luong ton hien tai (real time)
    //     $sumQtyCurrentInQuoteItem = $product->sumQtyCurrentInQuoteItem(); // sum qty cua product hien tai trong table quote_item (real time)
    //     // $this->writeLog('getDefaultStockCustom: '.$defaultStockCustom);
    //     // $this->writeLog('sumQtyCurrentInQuoteItem: '.$sumQtyCurrentInQuoteItem);
    //     $defaultStockQty = $requestQty - $sumQtyCurrentInQuoteItem;
    //     $defaultStock = $defaultStockCustom - $sumQtyCurrentInQuoteItem;

    //     if ($defaultStockCustom >= ($requestQty + $sumQtyCurrentInQuoteItem) || $defaultStockCustom > $requestQty) {
    //         return $result;
    //     }

    //     if ($defaultStock == 0 || $defaultStockCustom == 0 || $defaultStockQty == 0) {
    //         // $result['messages'] =  'Sản phẩm quà tặng '.$product->getNameShort(). ' tạm hết hàng.';
    //         $result['messages'] =  'Sản phẩm quà tặng '.$product->getNameShort(). ' chỉ còn ' . $defaultStockCustom . ' sản phẩm';
    //         $result['error_code'] = 'out-of-stock-promo';
    //         $result['product_id'] = $product->getId();
    //         $result['success'] = false;

    //         return $result;
    //     }
    //     if (($requestQty > $defaultStockCustom)) {
    //         // $result['messages'] =  'Sản phẩm quà tặng '.$product->getNameShort(). ' chỉ còn ' . $defaultStock . ' sản phẩm';
    //         $result['messages'] =  'Sản phẩm quà tặng '.$product->getNameShort(). ' chỉ còn ' . $defaultStockCustom . ' sản phẩm';
    //         $result['error_code'] = 'contact-us-promo';
    //         $result['product_id'] = $product->getId();
    //         $result['success'] = false;

    //         return $result;
    //     }
    //     return $result;
    // }

    public function checkDefaultStockPromo($product, $requestQty, $sumQtyWithoutProduct)
    {
        $result = array();

        $defaultStockCustom = $product->getDefaultStockCustom(); // so luong ton hien tai (real time)

        if ($defaultStockCustom == 0) {
            $result['messages'] =  'Sản phẩm quà tặng '.$product->getNameShort(). ' chỉ còn ' . $defaultStockCustom . ' sản phẩm';
            $result['error_code'] = 'out-of-stock-promo';
            $result['success'] = false;

            return $result;
        }

        if (($requestQty > $defaultStockCustom) || (($requestQty + $sumQtyWithoutProduct) > $defaultStockCustom)) {
            $result['messages'] =  'Sản phẩm quà tặng '.$product->getNameShort(). ' chỉ còn ' . $defaultStockCustom . ' sản phẩm';
            $result['error_code'] = 'contact-us-promo';
            $result['success'] = false;

            return $result;
        }

        return $result;
    }

    public function getProductPromo() {
        try {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $checkoutSession = $objectManager->get('Magento\Checkout\Model\Session');
            $productBrandHelper = $objectManager->get('Ves\Brand\Helper\ProductBrand');
            $imageHelper = $objectManager->get('Magento\Catalog\Helper\Image');
            $quoteFactory = $objectManager->create('Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory');

            $cartPriceRulesSku = array();
            $itemIds = array();
            $quote = $checkoutSession->getQuote();
            $quoteId = $quote->getId();
            $products = $quoteFactory->create()->addFieldToFilter('quote_id', $quoteId)
                                            ->addFieldToFilter(
                                            ['cart_promo_option','cart_promo_option'],
                                                [
                                                    ['eq' => 'ampromo_spent'],
                                                    ['eq' => 'ampromo_cart']
                                                ]
                                            );
            // $this->writeLog($products->getSelect()->__toString());
            $allItems = $quote->getAllItems();
            foreach($products as $item) {
                $productId = $item->getProductId();
                foreach ($allItems as $_item) {
                    if ($_item->getProduct()->getId() == $productId) {
                        $productPromo = $objectManager->create('Magento\Catalog\Model\Product')->load($productId);
                        $salesRuleId = $_item->getAmpromoRuleId();
                        if (empty($salesRuleId)) {
                            $salesRuleId = $_item->getAppliedRuleIds();
                        }
                        // $this->writeLog('salesRuleId: '.$salesRuleId);
                            if ($salesRuleId) {
                                $salesRule = $objectManager->get('\Magento\SalesRule\Api\RuleRepositoryInterface');
                                $rule = $salesRule->getById($salesRuleId);
                                if ($rule && $rule->getIsActive()) {
                                    $stringSkus = $rule->getExtensionAttributes()->getAmpromoRule()->getSku();
                                    $skus = explode(',', $stringSkus);
                                    // $this->writeLog($skus);
                                    $simpleAction = $rule->getSimpleAction();
                                    // $this->writeLog('getSimpleAction: '.$simpleAction);
                                    switch ($simpleAction) {
                                        case 'ampromo_cart':
                                            if ($skus && count($skus) > 0) {
                                                foreach($skus as $key => $sku) {
                                                    // $this->writeLog($item->getSku());
                                                    // $this->writeLog($sku);
                                                    if ($item->getSku() == $sku) {
                                                        $productBrandPromo = $productBrandHelper->getFirstBrandByProduct($productPromo);
            
                                                        if (!isset($productBrandPromo)) {
                                                            $productBrandPromo = '';
                                                        } else {
                                                            $productBrandPromo = $productBrandPromo->getName();
                                                        }
                                                        $productModelPromo = $productPromo->getModel();
                                                        $imagePromo = $imageHelper->init($productPromo, 'product_page_image_thumbnail')->setImageFile($productPromo->getImage())->getUrl();
                                                        if (isset($imagePromo) && !($this->url_exists($imagePromo))) {
                                                            $imagePromo = $imageHelper->getDefaultPlaceholderUrl('thumbnail');
                                                        }
                                                        if (!isset($imagePromo)) {
                                                            $imagePromo = $imageHelper->getDefaultPlaceholderUrl('thumbnail');
                                                        }
                                                        $hasProductPromoUrl = $this->hasProductUrl($productPromo);
                                                        $getProductPromoUrl = $productPromo->getProductUrl();
                                                        $dataProdPromo['imagePromo'] = $imagePromo;
                                                        $dataProdPromo['productModelPromo'] = $productModelPromo;
                                                        $dataProdPromo['getProductPromoUrl'] = $getProductPromoUrl;
                                                        $dataProdPromo['hasProductPromoUrl'] = $hasProductPromoUrl;
                                                        $dataProdPromo['productBrandPromo'] = $productBrandPromo;
                                                        $dataProdPromo['productNamePromo'] = $item->getName();
                                                        $dataProdPromo['getRuleName'] = $rule->getName();
                                                        $cartPriceRulesSku[$item->getCartPromoParentItemId().'_'.$sku] = $dataProdPromo;
                                                        $itemIds[$item->getCartPromoParentItemId().'_'.$sku] = $item->getCartPromoParentItemId();
                                                    }
                                                }
                                            }
                                        break;
                                        case 'ampromo_spent':
                                            if ($skus && count($skus) > 0) {
                                                foreach($skus as $key => $sku) {
                                                    // $this->writeLog($item->getSku());
                                                    // $this->writeLog($sku);
                                                    if ($item->getSku() == $sku) {
                                                        $productBrandPromo = $productBrandHelper->getFirstBrandByProduct($productPromo);
            
                                                        if (!isset($productBrandPromo)) {
                                                            $productBrandPromo = '';
                                                        } else {
                                                            $productBrandPromo = $productBrandPromo->getName();
                                                        }
                                                        $productModelPromo = $productPromo->getModel();
                                                        $imagePromo = $imageHelper->init($productPromo, 'product_page_image_thumbnail')->setImageFile($productPromo->getImage())->getUrl();
                                                        if (isset($imagePromo) && !($this->url_exists($imagePromo))) {
                                                            $imagePromo = $imageHelper->getDefaultPlaceholderUrl('thumbnail');
                                                        }
                                                        if (!isset($imagePromo)) {
                                                            $imagePromo = $imageHelper->getDefaultPlaceholderUrl('thumbnail');
                                                        }
                                                        $hasProductPromoUrl = $this->hasProductUrl($productPromo);
                                                        $getProductPromoUrl = $productPromo->getProductUrl();
                                                        $dataProdPromo['imagePromo'] = $imagePromo;
                                                        $dataProdPromo['productModelPromo'] = $productModelPromo;
                                                        $dataProdPromo['getProductPromoUrl'] = $getProductPromoUrl;
                                                        $dataProdPromo['hasProductPromoUrl'] = $hasProductPromoUrl;
                                                        $dataProdPromo['productBrandPromo'] = $productBrandPromo;
                                                        $dataProdPromo['productNamePromo'] = $item->getName();
                                                        $dataProdPromo['getRuleName'] = $rule->getName();
                                                        $cartPriceRulesSku[$item->getCartPromoParentItemId().'_'.$sku] = $dataProdPromo;
                                                        $itemIds[$item->getCartPromoParentItemId().'_'.$sku] = $item->getCartPromoParentItemId();
                                                    }
                                                }
                                            }
                                        break;
                                    }
                                }
                            }
                        // }
                    }
                }
            }
            $data = "";
            $data .= $this->getProductPromoHtml($cartPriceRulesSku, $itemIds);
            return $data;
        } catch (Exception $e) {
            $this->writeLog($e);
            return "";
        }
    }

    public function getProductPromoHtml($cartPriceRulesSku, $itemIds) {
        $html = '';
        // $this->writeLog($cartPriceRulesSku);
        if ($cartPriceRulesSku) {
            foreach($cartPriceRulesSku as $key => $item) {
                $html = $html.'<li class="product-item" id="ampromo_cart_'.$itemIds[$key].'">';
                $html = $html.'<div class="row product">';
                $html = $html.'<div class="col-xl-3 col-lg-12 col-md-12 col-sm-3 col-4 product-image-container">';
                if ($item['hasProductPromoUrl']) {
                    $html = $html.'<a target="_blank" class="product-image-wrapper" href="'.$item['getProductPromoUrl'].'">';
                    $html = $html.'<img src="'.$item['imagePromo'].'">';
                    $html = $html.'</a>';
                } else {
                    $html = $html.'<span class="product-image-wrapper">';
                    $html = $html.'<img src="'.$item['imagePromo'].'">';
                    $html = $html.'</span>';
                }
                $html = $html.'</div>';
                $html = $html.'<div class="col-xl-9 col-lg-12 col-md-12 col-sm-9 col-8 product-item-details">';
                $html = $html.'<span class="rule-name-promo">'.$item['getRuleName'].'</span>';
                $html = $html.'<div class="row product-item-inner">';
                if ($item['hasProductPromoUrl']) {
                    $html = $html.'<a target="_blank" class="product-item-name product-item-detail" href="'.$item['getProductPromoUrl'].'">'.$item['productNamePromo'].'</a>';
                } else {
                    $html = $html.'<span class="product-item-name product-item-detail">'.$item['productNamePromo'].'</span>';
                }
                $html = $html.'</div>';
                $html = $html.'<div class="product-item-merge-span">';
                if ($item['productBrandPromo']) {
                    $html = $html.'<span class="product-item-detail">'.__('Brands: ').$item['productBrandPromo'].'</span>';
                }
                if ($item['productModelPromo']) {
                    $html = $html.'<span class="product-item-detail">'.__('Model: ').$item['productModelPromo'].'</span>';
                }
                $html = $html.'</div>';
                $html = $html.'</div>';
                $html = $html.'</div>';
                $html = $html.'</li>';
            }
        }
        return $html;
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

    public function checkDefaultStock($product, $requestQty, $sumQtyWithoutProduct)
    {
        $result = array();

        $defaultStockCustom = $product->getDefaultStockCustom(); // so luong ton hien tai (real time)

        if ($defaultStockCustom == 0) {
            $result['messages'] = 'Tạm hết hàng';
            $result['error_code'] = 'out-of-stock';
            $result['success'] = false;

            return $result;
        }

        if (($requestQty > $defaultStockCustom) || (($requestQty + $sumQtyWithoutProduct) > $defaultStockCustom)) {
            $result['messages'] = 'Chỉ còn ' . $defaultStockCustom . ' sản phẩm';
            $result['error_code'] = 'contact-us';
            $result['success'] = false;

            return $result;
        }

        return $result;
    }

    public function sumQtyWithoutProduct($itemId, $quoteId, $productId)
    {
        try {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
            $connection = $resource->getConnection();
            if (isset($quoteId)) {
                $sql = 'SELECT sku, SUM(qty) AS qty FROM quote_item WHERE parent_item_id IS NULL AND quote_id = '.$quoteId.' AND product_id = '.$productId.' AND item_id NOT IN ('.$itemId.') GROUP BY sku';
                // $this->writeLog($sql);
                $result = $connection->fetchRow($sql);
                // $this->writeLog($result);
                if (isset($result['qty'])) {
                    return (int)$result['qty'];
                }
            }
            return 0;
        } catch(\Exception $e) {
            $this->writeLog($e);
            return 0;
        }
    }

    /**
	* @param $info
	* @param $type  [error, warning, info]
	* @return 
	*/
	private function writeLog($info, $type = "info") {
		$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/chottvn_sales.log');
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