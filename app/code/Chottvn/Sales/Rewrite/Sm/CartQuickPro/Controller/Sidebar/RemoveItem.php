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

class RemoveItem extends \Sm\CartQuickPro\Controller\Sidebar\RemoveItem
{
    /**
     * @var \Magento\Checkout\Model\Sidebar
     */
    protected $sidebar;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    private $formKeyValidator;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Checkout\Model\Sidebar $sidebar
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Checkout\Model\Sidebar $sidebar,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        // $this->sidebar = $sidebar;
        // $this->logger = $logger;
        // $this->jsonHelper = $jsonHelper;
        // $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context,$sidebar,$logger,$jsonHelper,$resultPageFactory);
    }

    /**
     * @return $this
     */
    public function execute()
    {
		$result = [];
        if (!$this->getFormKeyValidator()->validate($this->getRequest())) {
            return $this->resultRedirectFactory->create()->setPath('*/cart/');
        }
        $itemId = (int)$this->getRequest()->getParam('item_id');
		$params = $this->getRequest()->getParams();  
        try {
            // Promo ampro_items will delete by Amasty_Promo Observer
            $this->sidebar->checkQuoteItem($itemId);
            $this->sidebar->removeQuoteItem($itemId);
            // >> To guarantee the 
            /*
            $quoteItemFactory = $this->_objectManager->create('Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory');
            $quoteItemsSub = $quoteItemFactory->create()->addFieldToFilter('cart_promo_parent_item_id', $itemId);                        
            if (count($quoteItemsSub->getData()) > 0) {             
                foreach ($quoteItemsSub as $itemSub) {                                       
                    $this->sidebar->removeQuoteItem($itemSub->getId());
                }             
            }*/

			$result['success'] = true;
			$result['messages'] =  __('Item was removed successfully.');
			if (isset($params['isCheckoutPage'])){
				$_layout  = $this->_objectManager->get('Magento\Framework\View\LayoutInterface');
				$_layout->getUpdate()->load([ 'cartquickpro_checkout_cart_index', 'checkout_cart_item_renderers','checkout_item_price_renderers']);
				$_layout->generateXml();
				$_output = $_layout->getOutput();
				$result['content'] = $_output;
				$result['isPageCheckoutContent'] =  true;
			}
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
			$result['success'] = false;
			$result['messages'] =  $e->getMessage();
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $result['success'] = false;
			$result['messages'] =  $e->getMessage();
        }
		$cart = $this->_objectManager->get('Magento\Checkout\Model\Cart');
		$result['isAddToCartBtn'] =   (!isset($params['isCheckoutPage']) && $cart->getSummaryQty()) ? true : false ;
		return $this->_jsonResponse($result);
    }

    /**
     * Compile JSON response
     *
     * @param string $error
     * @return \Magento\Framework\App\Response\Http
     */
	protected function _jsonResponse($result)
    {
        return $this->getResponse()->representJson(
             $this->jsonHelper->jsonEncode($result)
        );
    } 
	 
    protected function jsonResponse($error = '')
    {
        $response = $this->sidebar->getResponseData($error);

        return $this->getResponse()->representJson(
            $this->jsonHelper->jsonEncode($response)
        );
    }

    /**
     * @return \Magento\Framework\Data\Form\FormKey\Validator
     * @deprecated
     */
    private function getFormKeyValidator()
    {
        if (!$this->formKeyValidator) {
            $this->formKeyValidator = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Framework\Data\Form\FormKey\Validator::class);
        }
        return $this->formKeyValidator;
    }
     /**
    * @param $info
    * @param $type  [error, warning, info]
    * @return 
    */
    private function writeLog($info, $type = "info") {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/sm_cartquickpro.log');
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
