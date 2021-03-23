<?php
/**
 *
 * SM CartQuickPro - Version 1.1.0
 * Copyright (c) 2017 YouTech Company. All Rights Reserved.
 * @license - Copyrighted Commercial Software
 * Author: YouTech Company
 * Websites: http://www.magentech.com
 */
 
namespace Sm\CartQuickPro\Controller\Product\Compare;

use Magento\Framework\Exception\NoSuchEntityException;

class Add extends \Magento\Catalog\Controller\Product\Compare
{
    /**
     * Add item to compare list
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if (!$this->_formKeyValidator->validate($this->getRequest())) {
            return $resultRedirect->setRefererUrl();
        }

        $productId = (int)$this->getRequest()->getParam('product');
		$params = $this->getRequest()->getParams();
		$result = [];

        // add current click product
        $current_click_product = $productId;

        // Get compare products configuration
        $compareConfig = $this->_objectManager->get('\Chottvn\Frontend\Helper\Compare')->getCompareProductsConfiguration();

        // 1. Get number products in compare
        $compareProducts = $this->_objectManager->get('\Magento\Catalog\CustomerData\CompareProducts');
        $compareProducts = $compareProducts->getSectionData();
        $count_compare_products = $compareProducts['count'];
        $_products = array();

        // 2. Get limit desktop = 3, tablet = 3, mobile = 2
        $limitProducts = $this->_objectManager->get('\Chottvn\Frontend\Helper\DetectMobile');
        if($limitProducts->isMobile() == true && $limitProducts->isTablet() == false){
            $num_limit_products = $compareConfig->mobile;
        }elseif($limitProducts->isTablet() == true){
            $num_limit_products = $compareConfig->tablet;
        }else{
            $num_limit_products = $compareConfig->desktop;
        }

        // If add more over limit > notify to custom
        if($count_compare_products >= $num_limit_products){
            $result['messages'] = __('You have selected a sufficient number of (%1) products to compare', $num_limit_products);
            $result['success'] = false;
            // get permission add to compare
            $permission_add = $this->getPermissionAddCompare($compareProducts['items']);

            // check not same primary category
            if($permission_add == false){
                $result['same_primary_category'] = $permission_add;
                $result['message_same_category'] = '<div class="message_same_category">'.__('Please choose to compare products in the same category').'</div>';
            }else{
                $result['same_primary_category'] = $permission_add;
                $result['message_same_category'] = '';
            }
        }elseif($productId && ($this->_customerVisitor->getId() || $this->_customerSession->isLoggedIn())) {
            $storeId = $this->_storeManager->getStore()->getId();
            try {
                $product = $this->productRepository->getById($productId, false, $storeId);
            } catch (NoSuchEntityException $e) {
                $product = null;
            }

            // get permission add to compare
            $permission_add = $this->getPermissionAddCompare($compareProducts['items'], $product);

            // check permission add product
            if ($product) {
                // 3. check products is exist in compare list
                $is_exist_product = false;
                $compared_products = $compareProducts['items'];
                foreach ($compared_products as $prod) {
                    if($prod['id'] == $productId){
                        $is_exist_product = true;break;
                    }
                }

                // add sp to compare list
                if($is_exist_product == false){
                    $this->_catalogProductCompareList->addProduct($product);
                }
                $productName = $this->_objectManager->get('Magento\Framework\Escaper')->escapeHtml($product->getName());
                // $this->messageManager->addSuccess(__('You added product %1 to the comparison list.', $productName));
                $this->_eventManager->dispatch('catalog_product_compare_add_product', ['product' => $product]);

                // re compare and send message
                $this->_objectManager->get('Magento\Catalog\Helper\Product\Compare')->calculate();
                if($is_exist_product == true){
                    $result['messages'] = __('%1 is already on the compare list', $productName);
                    $result['success'] = false;
                }else{
                    $result['messages'] = __('You added product %1 to the comparison list.', $productName);
                    $result['success'] = true;
                }

                // check not same primary category
                if($permission_add == false){
                    $result['same_primary_category'] = $permission_add;
                    $result['message_same_category'] = '<div class="message_same_category">'.__('Please choose to compare products in the same category').'</div>';
                }else{
                    $result['same_primary_category'] = $permission_add;
                    $result['message_same_category'] = '';
                }
                
            }
            // elseif($product && $permission_add == false){
            //     // case don't compare with category
            //     $result['messages'] = __('Please choose to compare products in the same category');
            //     $result['success'] = false;
            // }
            
			if (isset($params['isComparePage'])){
				$_layout  = $this->_objectManager->get('Magento\Framework\View\LayoutInterface');
				$_layout->getUpdate()->load(['cartquickpro_product_compare_remove']);
				$_layout->generateXml();
				$_output = $_layout->getOutput();
				$result['content'] = $_output;
				$result['isComparePageContent'] =  true;
			}
			
        }

        // compare data
        $compare = $this->_objectManager->get('Magento\Catalog\Helper\Product\Compare');

        // re-get data compare
        $compareProducts_reconnect = $this->_objectManager->create('\Magento\Catalog\CustomerData\CompareProducts');
        $helperImport = $this->_objectManager->get('\Magento\Catalog\Helper\Image');
        $priceHelper = $this->_objectManager->create('Magento\Framework\Pricing\Helper\Data'); // Instance of Pricing Helper
        $compareProducts_reconnect = $compareProducts_reconnect->getSectionData();
        $_info_products = array();
        $storeId = $this->_storeManager->getStore()->getId();
        $i = 0;
        foreach ($compareProducts_reconnect['items'] as $_product) {
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

        $num_compare_products = !empty($compareProducts_reconnect['items']) ? count($compareProducts_reconnect['items']) : 0;

        // check if
        if (($compareProducts_reconnect['count'] > $num_compare_products) && !empty($product)){
            $i_tmp = $compareProducts_reconnect['count']-1;

            // image url
            $imageUrl = $helperImport->init($product, 'product_page_image_small')
                ->setImageFile($product->getImage())
                ->resize(200)
                ->getUrl();

            // get format price
            if($product->getFinalPrice() > 0){
                $formattedPrice = $priceHelper->currency($product->getFinalPrice(), true, false);
            }else{
                $formattedPrice = __('Price Contact');
            }
            

            $_info_products[$i_tmp]['name'] = $product->getName();
            $_info_products[$i_tmp]['url'] = $product->getProductUrl();
            $_info_products[$i_tmp]['image'] = $imageUrl;
            $_info_products[$i_tmp]['price'] = $formattedPrice;
            $_info_products[$i_tmp]['remove_url'] = $compare->getPostDataRemove($product);
        }

        $result['isCompareBtn'] =   (!isset($params['isComparePage']) && $compare->getItemCount()) ? true : false ;
        $result['count'] = $compareProducts_reconnect['count'];
        $result['limit'] = $num_limit_products;
        $result['items'] = $_info_products;
        $result['action_page'] = 'compare';
        $result['compare_url'] = $compareProducts_reconnect['listUrl'];
        $result['detect_mobile'] = $compareConfig;
        $result['current_click_product'] = $current_click_product;
		return $this->_jsonResponse($result);
    }
	
	protected function _jsonResponse($result)
    {
        return $this->getResponse()->representJson(
            $this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode($result)
        );
    }

    protected function getPermissionAddCompare($compare_products, $product = array()){
        // default value
        $permission_add = true;
        $storeId = $this->_storeManager->getStore()->getId();

        if(empty($product)){
            // get first product
            $product = $this->productRepository->getById($compare_products[0]['id'], false, $storeId);
        }
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

        return $permission_add;
    }
}
