<?php
/**
 *
 * SM CartQuickPro - Version 1.1.0
 * Copyright (c) 2017 YouTech Company. All Rights Reserved.
 * @license - Copyrighted Commercial Software
 * Author: YouTech Company
 * Websites: http://www.magentech.com
 */
 
namespace Sm\CartQuickPro\Controller\Cart;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Add extends \Magento\Checkout\Controller\Cart
{
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    protected $stockItemRepository;

    protected $cartHelper;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param CustomerCart $cart
     * @param ProductRepositoryInterface $productRepository
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        CustomerCart $cart,
        \Magento\CatalogInventory\Model\Stock\StockItemRepository $stockItemRepository,
        \Magento\Checkout\Helper\Cart $cartHelper,
        ProductRepositoryInterface $productRepository
    ) {
        parent::__construct(
            $context,
            $scopeConfig,
            $checkoutSession,
            $storeManager,
            $formKeyValidator,
            $cart
        );
        $this->productRepository = $productRepository;
        $this->stockItemRepository = $stockItemRepository;
        $this->cartHelper = $cartHelper;
    }

    /**
     * Initialize product instance from request data
     *
     * @return \Magento\Catalog\Model\Product|false
     */
    protected function _initProduct()
    {
        $productId = (int)$this->getRequest()->getParam('product');
        if ($productId) {
            $storeId = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getId();
            try {
                return $this->productRepository->getById($productId, false, $storeId);
            } catch (NoSuchEntityException $e) {
                return false;
            }
        }
        return false;
    }

    /**
     * Add product to shopping cart action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        $result = [];
		if (!$this->_formKeyValidator->validate($this->getRequest())) {
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }
		
        $params = $this->getRequest()->getParams();
        try {
            if (isset($params['qty'])) {
                $filter = new \Zend_Filter_LocalizedToNormalized(
                    ['locale' => $this->_objectManager->get('Magento\Framework\Locale\ResolverInterface')->getLocale()]
                );
                $params['qty'] = $filter->filter($params['qty']);
            }

            $product = $this->_initProduct();
            $related = $this->getRequest()->getParam('related_product');

            /**
             * Check product availability
             */
            if (!$product) {
                return $this->goBack();
            }

            $this->cart->addProduct($product, $params);
            if (!empty($related)) {
                $this->cart->addProductsByIds(explode(',', $related));
            }

            $this->cart->save();

            /**
             * @todo remove wishlist observer \Magento\Wishlist\Observer\AddToCart
             */
            $this->_eventManager->dispatch(
                'checkout_cart_add_product_complete',
                ['product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse()]
            );

            if (!$this->_checkoutSession->getNoCartRedirect(true)) {
				if (!$this->cart->getQuote()->getHasError()) {
                    $message = __(
                        'You added %1 to your shopping cart.',
                        $product->getName()
                    );
                    $this->messageManager->addSuccessMessage($message);
					$result['success'] = true;
					$result['messages'] =  $message;
					if (isset($params['isCheckoutPage'])){
						$_layout  = $this->_objectManager->get('Magento\Framework\View\LayoutInterface');
						$_layout->getUpdate()->load([ 'cartquickpro_checkout_cart_index', 'checkout_cart_item_renderers','checkout_item_price_renderers']);
						$_layout->generateXml();
						$_output = $_layout->getOutput();
						$result['content'] = $_output;
						$result['isPageCheckoutContent'] =  true;
					}
                }
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            // get product id
            $product_id = $product->getId();

            // get salable stock for product
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
            $stockResolver = $objectManager->get('\Magento\InventorySalesApi\Api\StockResolverInterface');
            $productSalableQty = $objectManager->get('\Magento\InventorySalesApi\Api\GetProductSalableQtyInterface');
            $websiteCode = $storeManager->getWebsite()->getCode();
            $stock = $stockResolver->execute(\Magento\InventorySalesApi\Api\Data\SalesChannelInterface::TYPE_WEBSITE, $websiteCode);
            $stockId = $stock->getStockId();
            $product_sku = $product->getSku();
            $salable_stock_qty = $productSalableQty->execute($product_sku, $stockId) > 0 ? $productSalableQty->execute($product_sku, $stockId):0;

            // get current add
            $current_add_qty_to_cart = isset($params['qty']) ? (int) $params['qty']:1;

            // added product to cart
            $items = $this->cartHelper->getQuote()->getItems();

            $added_product_to_cart = 0;
            if($items){
                foreach ($items as $item){
                    if($product_id == $item->getProduct_id()){
                        $added_product_to_cart = $item->getQty();
                    }
                }
            }
            
            // "The requested qty is not available","Số lượng yêu cầu không có sẵn"
            switch ($e->getMessage()) {
                case 'The requested qty is not available':
                case 'Số lượng yêu cầu không có sẵn':
                case 'Sản phẩm bạn vừa thêm không có sẵn.':
                    $actual_number_in_cart = $added_product_to_cart - $current_add_qty_to_cart;
                    $actual_number_in_cart = $actual_number_in_cart > 0 ? $actual_number_in_cart:0;

                    if($actual_number_in_cart == 0 && $salable_stock_qty == 0){
                        $result['messages'] = $e->getMessage();
                        $result['error_code'] = 'contact-us';
                        $result['success'] = false;
                        $result['html_input'] = '<a href="'.$product->getProductUrl(false).'\'#stockalert\'" title="'.$product->getName().'"><button title="'.__('Contact when stock is available').'" class="stock unavailable btn-action btn-cart"><span>'.__('Contact when stock is available').'</span></button></a>';
                    }else{
                        $result['messages'] = $e->getMessage();
                        $result['error_code'] = 'out-of-stock';
                        $result['success'] = false;
                        $result['html_input'] = __('Not enough required amount.');
                    }

                    $result['actual_number_in_cart'] = $actual_number_in_cart;
                    $result['salable_stock_qty'] = $salable_stock_qty;
                    $result['action_page'] = 'add_to_cart';
                    $result['product_id'] = $product_id;
                    break;
                
                default:
                    if ($this->_checkoutSession->getUseNotice(true)) {
                        $this->messageManager->addNotice(
                            $this->_objectManager->get('Magento\Framework\Escaper')->escapeHtml($e->getMessage())
                        );
                        $result['messages'] =  $this->_objectManager->get('Magento\Framework\Escaper')->escapeHtml($e->getMessage());
                        $result['success'] = true;
                    } else {
                        $messages = array_unique(explode("\n", $e->getMessage()));
                        foreach ($messages as $message) {
                            $this->messageManager->addError(
                                $this->_objectManager->get('Magento\Framework\Escaper')->escapeHtml($message)
                            );
                        }
                        
                        $result['messages'] = join(', ', $messages);
                        $result['success'] = false;
                    }
                    break;

                    $storeManager = $this->_objectManager->get('\Magento\Store\Model\StoreManagerInterface');
                    $baseUrl= $storeManager->getStore()->getBaseUrl();
                    $url = $baseUrl.'cartquickpro/catalog_product/options/id/'.$params['product'];
                    if (!$url) {
                        $cartUrl = $this->_objectManager->get('Magento\Checkout\Helper\Cart')->getCartUrl();
                        $url = $this->_redirect->getRedirectUrl($cartUrl);
                    }
                    
                    $result['url']  =  $url;
            }
            // print_r(get_class_methods($e));exit;

        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('We can\'t add this item to your shopping cart right now.'));
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
			$result['messages'] =  __('We can\'t add this item to your shopping cart right now.');
			$result['success'] = false;
			
        }
		$result['isAddToCartBtn'] =   (!isset($params['isCheckoutPage']) && $this->cart->getItemsCount()) ? true : false ;
		
		return $this->_jsonResponse($result);
    }
	
	protected function _jsonResponse($result)
    {
        return $this->getResponse()->representJson(
            $this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode($result)
        );
    }

    /**
     * Resolve response
     *
     * @param string $backUrl
     * @param \Magento\Catalog\Model\Product $product
     * @return $this|\Magento\Framework\Controller\Result\Redirect
     */
    protected function goBack($backUrl = null, $product = null)
    {
        if (!$this->getRequest()->isAjax()) {
            return parent::_goBack($backUrl);
        }

        $result = [];

        if ($backUrl || $backUrl = $this->getBackUrl()) {
            $result['backUrl'] = $backUrl;
        } else {
            if ($product && !$product->getIsSalable()) {
                $result['product'] = [
                    'statusText' => __('Out of stock')
                ];
            }
        }

        $this->getResponse()->representJson(
            $this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode($result)
        );
    }
}
