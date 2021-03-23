<?php
/**
 *
 * SM CartQuickPro - Version 1.1.0
 * Copyright (c) 2017 YouTech Company. All Rights Reserved.
 * @license - Copyrighted Commercial Software
 * Author: YouTech Company
 * Websites: http://www.magentech.com
 */
 
namespace  Sm\CartQuickPro\Controller\Product\Compare;

use Magento\Framework\Exception\NoSuchEntityException;

class Remove extends \Magento\Catalog\Controller\Product\Compare
{
    /**
     * Remove item from compare list
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $productId = (int)$this->getRequest()->getParam('product');
		$result = [];
		$params = $this->getRequest()->getParams();

        // add current click product
        $current_click_product = $productId;


        if ($productId) {
            $storeId = $this->_storeManager->getStore()->getId();
            try {
                $product = $this->productRepository->getById($productId, false, $storeId);
            } catch (NoSuchEntityException $e) {
                $product = null;
            }

            if ($product) {
                /** @var $item \Magento\Catalog\Model\Product\Compare\Item */
                $item = $this->_compareItemFactory->create();
                if ($this->_customerSession->isLoggedIn()) {
                    $item->setCustomerId($this->_customerSession->getCustomerId());
                } elseif ($this->_customerId) {
                    $item->setCustomerId($this->_customerId);
                } else {
                    $item->addVisitorId($this->_customerVisitor->getId());
                }

                $item->loadByProduct($product);
                /** @var $helper \Magento\Catalog\Helper\Product\Compare */
                $helper = $this->_objectManager->get('Magento\Catalog\Helper\Product\Compare');
                if ($item->getId()) {
                    $item->delete();
                    $productName = $this->_objectManager->get('Magento\Framework\Escaper')
                        ->escapeHtml($product->getName());
                    // $this->messageManager->addSuccess(
                    //     __('You removed product %1 from the comparison list.', $productName)
                    // );
                    $this->_eventManager->dispatch(
                        'catalog_product_compare_remove_product',
                        ['product' => $item]
                    );
                    $helper->calculate();
					if (isset($params['isComparePage'])){
						$_layout  = $this->_objectManager->get('Magento\Framework\View\LayoutInterface');
						$_layout->getUpdate()->load(['cartquickpro_product_compare_remove']);
						$_layout->generateXml();
						$_output = $_layout->getOutput();
						$result['content'] = $_output;
						$result['isComparePageContent'] =  true;
					}
					$result['messages'] =  __('You removed product %1 from the comparison list.', $productName);
					$result['success'] = true;
					
                }
            }
        }

		$compare = $this->_objectManager->get('Magento\Catalog\Helper\Product\Compare');
        $result['isCompareBtn'] =   (!isset($params['isComparePage']) && $compare->getItemCount()) ? true : false ;

        // add more json
        $compareProducts = $this->_objectManager->get('\Magento\Catalog\CustomerData\CompareProducts');
        $helperImport = $this->_objectManager->get('\Magento\Catalog\Helper\Image');
        $priceHelper = $this->_objectManager->create('Magento\Framework\Pricing\Helper\Data'); // Instance of Pricing Helper
        $compareProducts = $compareProducts->getSectionData();
        $_info_products = array();

        // get products
        $i = 0;
        foreach ($compareProducts['items'] as $_product) {
            $product_tmp = $this->productRepository->getById($_product['id'], false, $storeId);
            // image url
            $imageUrl = $helperImport->init($product_tmp, 'product_page_image_small')
                ->setImageFile($product_tmp->getImage())
                ->resize(200)
                ->getUrl();

            // get format price
            if($product_tmp->getFinalPrice() > 0){
                $formattedPrice = $priceHelper->currency($product_tmp->getFinalPrice(), true, false);
            }else{
                $formattedPrice = __('Price Contact');
            }
            

            $_info_products[$i]['name'] = $product_tmp->getName();
            $_info_products[$i]['url'] = $product_tmp->getProductUrl();
            $_info_products[$i]['image'] = $imageUrl;
            $_info_products[$i]['price'] = $formattedPrice;
            $_info_products[$i]['remove_url'] = $compare->getPostDataRemove($product_tmp);
            $i++;
        }

        // permission add products
        $permission_add = $this->getPermissionAddCompare($compareProducts['items']);
        if($permission_add == false){
            $result['same_primary_category'] = $permission_add;
            $result['message_same_category'] = '<div class="message_same_category">'.__('Please choose to compare products in the same category').'</div>';
        }else{
            $result['same_primary_category'] = $permission_add;
            $result['message_same_category'] = '';
        }

        
        // Get compare products configuration
        $compareConfig = $this->_objectManager->get('\Chottvn\Frontend\Helper\Compare')->getCompareProductsConfiguration();
        // Get limit desktop = 3, tablet = 3, mobile = 2
        $limitProducts = $this->_objectManager->get('\Chottvn\Frontend\Helper\DetectMobile');
        if($limitProducts->isMobile() == true && $limitProducts->isTablet() == false){
            $num_limit_products = $compareConfig->mobile;
        }elseif($limitProducts->isTablet() == true){
            $num_limit_products = $compareConfig->tablet;
        }else{
            $num_limit_products = $compareConfig->desktop;
        }

        // additional info
        $result['action_page'] = 'compare-remove';
        $result['items'] = $_info_products;
        $result['count'] = $compareProducts['count'];
        $result['compare_url'] = $compareProducts['listUrl'];
        $result['limit'] = $num_limit_products;
        $result['current_click_product'] = $current_click_product;
		return $this->_jsonResponse($result);
    }
	
	protected function _jsonResponse($result)
    {
        return $this->getResponse()->representJson(
            $this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode($result)
        );
    }


    protected function getPermissionAddCompare($compare_products){
        // default value
        $permission_add = true;
        $storeId = $this->_storeManager->getStore()->getId();

        if($compare_products){
            // get first product
            $product = $this->productRepository->getById($compare_products[0]['id'], false, $storeId);

            // check same category
            if($compare_products && $product){
                // get last category
                $list_categories = $product->getCategoryIds();
                $category = $list_categories[count($list_categories)-1];
                $cate = $this->_objectManager->create('Magento\Catalog\Model\Category')->load($category);

                // check foreach in compare products
                foreach ($compare_products as $p) {
                    // get first product in case
                    $current_product_id = $p['id'];
                    $current_product = $this->productRepository->getById($current_product_id, false, $storeId);

                    // Get last current category
                    $list_current_categories = $current_product->getCategoryIds();
                    $current_category = $list_current_categories[count($list_current_categories)-1];

                    // get permission category
                    $current_cate = $this->_objectManager->create('Magento\Catalog\Model\Category')->load($current_category);
                    if (null !== $current_cate->getCustomAttribute('chottvn_compare_with_product_attribute')) {
                        $permission_cate = explode(',', $current_cate->getCustomAttribute('chottvn_compare_with_product_attribute')->getValue());
                    }else{
                        $permission_cate = explode(',', $current_cate->getId());
                    }

                    // check in_array category
                    if(!in_array($cate->getId(), $permission_cate)){
                        $permission_add = false;
                        break;
                    }
                }            
            }else{
                return true;
            }
        }

        return $permission_add;
    }
}
