<?php
/**
 *
 * SM CartQuickPro - Version 1.1.0
 * Copyright (c) 2017 YouTech Company. All Rights Reserved.
 * @license - Copyrighted Commercial Software
 * Author: YouTech Company
 * Websites: http://www.magentech.com
 */
 
namespace Chottvn\Sales\Rewrite\Sm\CartQuickPro\Controller\Sidebar;

use Magento\Checkout\Model\Sidebar;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Response\Http;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Json\Helper\Data;
use Psr\Log\LoggerInterface;

class UpdateItemQty extends \Sm\CartQuickPro\Controller\Sidebar\UpdateItemQty
{
    /**
     * @var Sidebar
     */
    protected $sidebar;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Data
     */
    protected $jsonHelper;

    /**
     * @param Context $context
     * @param Sidebar $sidebar
     * @param LoggerInterface $logger
     * @param Data $jsonHelper
     * @codeCoverageIgnore
     */
    public function __construct(
        Context $context,
        Sidebar $sidebar,
        LoggerInterface $logger,
        Data $jsonHelper
    ) {
        parent::__construct(
            $context,
            $sidebar,
            $logger,
            $jsonHelper
        );
     }

    /**
     * @return $this
     */
    public function execute()
    {
        $result = [];
		$itemId = (int)$this->getRequest()->getParam('item_id');
        $itemQty = (int)$this->getRequest()->getParam('item_qty');
        $params = $this->getRequest()->getParams();
        try {            
            $quoteFactory = $this->_objectManager->create('Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory');
            $cart = $this->_objectManager->get('Magento\Checkout\Model\Cart');
            $productRepository = $this->_objectManager->get('Magento\Catalog\Api\ProductRepositoryInterface');

            $quote = $cart->getQuote();
            $quoteItems = $quoteFactory->create()->addFieldToFilter('quote_id', $quote->getId())
                                            ->addFieldToFilter('cart_promo_parent_item_id', $itemId);

            if (count($quoteItems->getData()) > 0) {                
                foreach ($quoteItems as $item) {
                    $qtyPromo = (int)$itemQty * (int)$item->getCartPromoQty();
                    
                    if (!empty($item->getCartPromoIds())) {
                        $productPromo = $productRepository->get($item->getSku());

                        $defaultStockQty = $this->checkDefaultStockPromo($productPromo, (int)$qtyPromo);
                        if (!empty($defaultStockQty)) {
                            return $this->_jsonResponse($defaultStockQty);
                        }
                    }
                    $item->setQty($qtyPromo);
                    $item->save();
                }
            }
            $this->sidebar->checkQuoteItem($itemId);
            $this->sidebar->updateQuoteItem($itemId, $itemQty);            

			$result['success'] = true;
			$result['messages'] =  __('Item was updated successfully.');
			if (isset($params['isCheckoutPage'])) {
				$_layout  = $this->_objectManager->get('Magento\Framework\View\LayoutInterface');
				$_layout->getUpdate()->load([ 'cartquickpro_checkout_cart_index', 'checkout_cart_item_renderers','checkout_item_price_renderers']);
				$_layout->generateXml();
				$_output = $_layout->getOutput();
				$result['content'] = $_output;
				$result['isPageCheckoutContent'] =  true;
			}
        } catch (LocalizedException $e) {
			$result['success'] = false;
			$result['messages'] = $e->getMessage();
        } catch (\Exception $e) {
            $this->logger->critical($e);
			$result['success'] = false;
			$result['messages'] = $e->getMessage();
        }
		$cart = $this->_objectManager->get('Magento\Checkout\Model\Cart');
        $result['isAddToCartBtn'] = (!isset($params['isCheckoutPage']) && $cart->getItemsCount()) ? true : false ;
        
		return $this->_jsonResponse($result);
    }

    /**
     * Compile JSON response
     *
     * @param string $error
     * @return Http
     */
	
	protected function _jsonResponse($result)
    {
        return $this->getResponse()->representJson(
             $this->jsonHelper->jsonEncode($result)
        );
    }
	
    protected function jsonResponse($error = '')
    {
        return $this->getResponse()->representJson(
            $this->jsonHelper->jsonEncode($this->sidebar->getResponseData($error))
        );
    }

    public function checkDefaultStockPromo($product, $requestQty)
    {
        $result = array();
        $defaultStockCustom = $product->getDefaultStockCustom(); // so luong ton hien tai (real time)
        $sumQtyCurrentInQuoteItem = $product->sumQtyCurrentInQuoteItem(); // sum qty cua product hien tai trong table quote_item (real time)
        $defaultStockQty = $defaultStockCustom - $sumQtyCurrentInQuoteItem;

        if ($requestQty <= $defaultStockCustom) {
            return $result;
        }

        if ($defaultStockCustom == 0 || $defaultStockQty == 0) {
            // $result['messages'] =  'Sản phẩm quà tặng '.$product->getNameShort(). ' tạm hết hàng.';
            $result['messages'] = 'Chỉ còn ' . $defaultStockCustom . ' sản phẩm';
            $result['success'] = false;

            return $result;
        }
        $defaultStock = $defaultStockCustom - ($sumQtyCurrentInQuoteItem + $requestQty);
        if ($defaultStock < 0) {
            $result['messages'] =  'Sản phẩm quà tặng '.$product->getNameShort(). ' chỉ còn ' . $defaultStockCustom . ' sản phẩm';
            $result['success'] = false;

            return $result;
        }
        return $result;
    }

    /**
    * @param $info
    * @param $type  [error, warning, info]
    * @return 
    */
    private function writeLog($info, $type = "info") {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/UpdateItemQty_Minicart.log');
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
