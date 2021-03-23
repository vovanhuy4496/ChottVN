<?php
/**
 *
 * SM Listing Tabs - Version 2.5.0
 * Copyright (c) 2017 YouTech Company. All Rights Reserved.
 * @license - Copyrighted Commercial Software
 * Author: YouTech Company
 * Websites: http://www.magentech.com
 */

namespace Sm\ListingTabs\Block;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Framework\UrlFactory;

class ListingTabs extends \Magento\Catalog\Block\Product\AbstractProduct
{
	protected $_config = null;
    protected $_resource;
	protected $_storeManager;
    protected $_scopeConfig;
	protected $_storeId;
	protected $_storeCode;
	protected $_catalogProductVisibility;
	protected $_review;
	protected $_objectManager;
	protected $viewedProductIds;
	protected $_categoryCollectionFactory;
	protected $_customerSession;
	protected $_ruleResource;
	protected $_resultJsonFactory;
	protected $_registry;
	

    public function __construct(
		\Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\ResourceConnection $resource,
		\Magento\Catalog\Model\Product\Visibility $catalogProductVisibility,
		\Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
		\Magento\Customer\Model\Session $customerSession,
		\Magento\CatalogRule\Model\ResourceModel\Rule $ruleResource,
		\Magento\Review\Model\Review $review,
		\Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
		\Magento\Framework\Registry $registry,
		\Magento\Catalog\Block\Product\Context $context,
        array $data = [],
		$attr = null
    ) {
		$this->_objectManager = $objectManager;
		$this->_customerSession = $customerSession;
        $this->_resource = $resource;
        $this->_ruleResource = $ruleResource;
		$this->_storeManager = $context->getStoreManager();
        $this->_scopeConfig = $context->getScopeConfig();
		$this->_catalogProductVisibility = $catalogProductVisibility;
		$this->_resultJsonFactory = $resultJsonFactory;
		$this->_storeId=(int)$this->_storeManager->getStore()->getId();
		$this->_storeCode=$this->_storeManager->getStore()->getCode();
		$this->_categoryCollectionFactory = $categoryCollectionFactory;
		$this->_review = $review;
		$this->_registry = $registry;
		if ($context->getRequest() && $context->getRequest()->isAjax()) {

			// xu ly ajax lay tu catalog product
			$type_ajax = $context->getRequest()->getParam('type_ajax') ? $context->getRequest()->getParam('type_ajax'):'default';
			if($type_ajax == 'catalog' && empty($context->getRequest()->getParam('ajax_listingtab_data'))){
				$this->_config =  (array) json_decode($context->getRequest()->getParam('config'));
			}elseif($context->getRequest()->getParam('ajax_listingtab_data')){
				$this->_config =  (array) json_decode($context->getRequest()->getParam('ajax_listingtab_data'));
			}else{
				$this->_config =  $context->getRequest()->getParam('config');
			}
		} else {
			$this->_config = $this->_getCfg($attr, $data);
		}
		$viewedProductIds = $context->getRequest()->getParam('viewed_product_ids');
        parent::__construct($context, $data);
    }
	
	public function _getCfg($attr = null , $data = null)
	{
		$defaults = [];
		$_cfg_xml = $this->_scopeConfig->getValue('listingtabs',\Magento\Store\Model\ScopeInterface::SCOPE_STORE,$this->_storeCode);
		if (empty($_cfg_xml)) return;
		$groups = [];
		foreach ($_cfg_xml as $def_key => $def_cfg) {
			$groups[] = $def_key;
			foreach ($def_cfg as $_def_key => $cfg) {
				$defaults[$_def_key] = $cfg;
			}
		}
		
		if (empty($groups)) return;
		$cfgs = [];
		foreach ($groups as $group) {
			$_cfgs = $this->_scopeConfig->getValue('listingtabs/'.$group.'',\Magento\Store\Model\ScopeInterface::SCOPE_STORE,$this->_storeCode);
			foreach ($_cfgs as $_key => $_cfg) {
				$cfgs[$_key] = $_cfg;
			}
		}

		if (empty($defaults)) return;
		$configs = [];
		foreach ($defaults as $key => $def) {
			if (isset($defaults[$key])) {
				$configs[$key] = $cfgs[$key];
			} else {
				unset($cfgs[$key]);
			}
		}
		$cf = ($attr != null) ? array_merge($configs, $attr) : $configs;
		$this->_config = ($data != null) ? array_merge($cf, $data) : $cf;
		return $this->_config;
	}

	public function _getConfig($name = null, $value_def = null)
	{
		if (is_null($this->_config)) $this->_getCfg();
		if (!is_null($name)) {
			$value_def = isset($this->_config[$name]) ? $this->_config[$name] : $value_def;
			return $value_def;
		}
		return $this->_config;
	}

	public function _setConfig($name, $value = null)
	{

		if (is_null($this->_config)) $this->_getCfg();
		if (is_array($name)) {
			$this->_config = array_merge($this->_config, $name);

			return;
		}
		if (!empty($name) && isset($this->_config[$name])) {
			$this->_config[$name] = $value;
		}
		return true;
	}

	public function getConfigProductAttributesHtmlRender($product,$config){
		$renderer = $this->getDetailsRenderer($product->getTypeId());
    if ($renderer) {
        $renderer->setProduct($product);
        $renderer->setData('config_data', $config);
        return $renderer->toHtml();
    }
    return '';
	}
	
	public function getProductDetailsHtml(\Magento\Catalog\Model\Product $product)
  {
    $renderer = $this->getDetailsRenderer($product->getTypeId());
    if ($renderer) {
        $renderer->setProduct($product);
        $renderer->setData('config_data', $this->_getCfg());
        return $renderer->toHtml();
    }
    return '';
  }

  public function getDetailsRenderer($type = null)
  {
    if ($type === null || $type !== 'configurable') {
       $type = 'default';
			return null;
    }
    $rendererList = $this->getDetailsRendererList();
    if ($rendererList) {
      return $rendererList->getRenderer($type, 'default');
    }
    return null;
  }

	private function isHomepage(){
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$request = $objectManager->get('Magento\Framework\App\Action\Context')->getRequest();
		if ($request->getFullActionName() == 'cms_index_index') {
			return true;
		}
		return true;
	}
	
	protected function getDetailsRendererList()
    {	
		$name_layout = $this->getNameInLayout();
		if ($this->_isAjax()) {
			$name_layout =  $this->getRequest()->getPost('moduleid');
		}
		return $this->getDetailsRendererListName() ? $this->getLayout()->getBlock(
			$this->getDetailsRendererListName()
		) : $this->getChildBlock(
			$name_layout.'.details.renderers'
		);
	}
	
	private function _getNameLayout() {
		$name_layout = $this->getNameInLayout();
		if ($this->_isAjax()) {
			$name_layout =  $this->getRequest()->getPost('moduleid');
		}
		return $name_layout;
	}
	
	public function _tagId()
	{
		$tag_id = $this->_getNameLayout().$this->generateBlockName(5,time());
		$tag_id = strpos($tag_id, '.') !== false ? str_replace('.', '_', $tag_id) : $tag_id;
		return $tag_id;
	}

	function generateBlockName($length = 5,$timestamp = 0) {
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen($characters);
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[rand(0, $charactersLength - 1)];
		}
		return $randomString.$timestamp;
	}
	
	protected function _prepareLayout()
	{
		$name_layout = $this->_getNameLayout();
		
		$this->getLayout()->addBlock(
			'Magento\Framework\View\Element\RendererList',
			$name_layout.'.renderlist',
			$this->getNameInLayout(),
			$name_layout.'.details.renderers'
		);
		$this->getLayout()->addBlock(
			'Sm\ListingTabs\Block\Product\Renderer\Listing\Configurable',
			$name_layout.'.colorswatches',
			$name_layout.'.renderlist',
			'configurable'
			)->setTemplate('Sm_ListingTabs::product/listing/renderer.phtml')->setData(['tagid' => $this->_tagId(),'name_layout'=>$name_layout]);
	}
	
	public function _isAjax()
	{
		$isAjax = $this->getRequest()->isAjax();
		$is_ajax_listing_tabs = $this->getRequest()->getPost('is_ajax_listing_tabs');
		if ($isAjax && $is_ajax_listing_tabs == 1) {
			return true;
		} else {
			return false;
		}
	}
	
	protected function _toHtml()
    {
		if (!(int)$this->_getConfig('isactive', 1)) return ;
		if ($this->_isAjax()) {
			$datacustom_content = $this->getRequest()->getPost('datacustomcontent');
			//$template_file = "default_items.phtml";
			
			if($datacustom_content == 'data-custom-content'){
				$template_file = "default_items_v3.phtml";
			} else if($datacustom_content == 'data-custom-left'){
				$template_file = "default_items_v4.phtml";
			} else if($datacustom_content == 'data-custom-center'){
				$template_file = "default_items_v6.phtml";
			} else {
				$detectMobile = $this->_objectManager->get('\Chottvn\Frontend\Helper\DetectMobile');
				$view_mode = $this->_getConfig('view_mode') ? $this->_getConfig('view_mode') : "grid";
				
				if($view_mode == 'grid'){
					$template_file = "default_items.phtml";
				}else{
					// detect mobile
					if($detectMobile->isMobile() == true && $detectMobile->isTablet() == false){
						$template_file = "list_mobile_items.phtml";
					}else{
						$template_file = "list_items.phtml";
					}
					
				}
			}
		}else{
			$template_file = $this->getTemplate();
			$template_file = (!empty($template_file)) ? $template_file : "Sm_ListingTabs::default.phtml";
		}
        $this->setTemplate($template_file);
        return parent::_toHtml();
    }
	
	public function _getList (){
		// {{block class="Sm\ListingTabs\Block\ListingTabs" template="Sm_ListingTabs::default.phtml" title="Khuyến mãi mỗi ngày" type_show="loadmore" type_listing="all" display_countdown="0" type_filter="all_categories" field_tabs="" type_product="" category_select="" category_tabs="2" order_by="created_at" order_dir="DESC" limitation="18"  css_class="hot-list-products" is_show_product_action_bar="1" is_show_tabs="1" }}
		$type_show = $this->_getConfig('type_show');
		$type_listing = $this->_getConfig('type_listing');
		$under_price = $this->_getConfig('under_price');
		$tabs_select = $this->_getConfig('tabs_select');
		$category_select = $this->_getConfig('category_select');
		$order_by = $this->_getConfig('order_by');
		$order_dir = $this->_getConfig('order_dir');
		$limitation = $this->_getConfig('limitation');
		$type_filter = $this->_getConfig('type_filter');
		$category_id = $this->_getConfig('category_tabs');
		$field_tabs = $this->_getConfig('field_tabs');
		$root_cate = $this->_getConfig('field_tabs') ? $this->_getConfig('root_category'):2;
		$current_page_type = $this->_getConfig('current_page_type') ? $this->_getConfig('current_page_type'):'catalog';
		

		$list = [];
		$cat_filter = [];
		$_objectManager = \Magento\Framework\App\ObjectManager::getInstance();

		switch($type_filter){
			case 'categories':
				$catids = array();
				if (!empty($category_id)){
					switch ($category_id) {
						case 'auto':
						   	switch ($current_page_type) {
						   		case 'search':
						   			$catids[] = 2;
						   			break;
						   		
						   		default:
						   			//get current category
								   	$current_category = $this->_objectManager->get('Magento\Framework\Registry')->registry('current_category');
								   	if (isset($current_category)){
								   		$catids[] = $current_category->getId();
								   	}
						   			break;
						   	}
							break;
						
						default:
							$catids = explode(',',$category_id);
							break;
					}
					$all_childrens = $this->_getAllChildren($catids);
					if (!empty($all_childrens)){
						$flag = true;
						foreach($all_childrens as $key => $children){
							$count = $this->_getProductsBasic($children, true);
							$cat_children = implode(',',  $children);
							if ($count > 0){
								$object_manager = $_objectManager->create('Magento\Catalog\Model\Category')->load($key);
								$list[$key]['name_tab'] =  $object_manager->getName();
								if ($object_manager->getId() == $root_cate){
									$list[$key]['name_tab'] = __('All');
								}
								$list[$key]['count'] = $count;
								$list[$key]['id_tab'] = $key;
								$list[$key]['cat_children'] = $cat_children;
								if ($flag){
									$list[$key]['sel'] = 'active';
									$list[$key]['products_list'] = $this->_getProductsBasic($children);
									$flag = false;
								}
							}
						}
					}
				}
			break;
			case 'all_categories':
				if (!empty($category_id)){
					$all_childrens = array();
					
					// Get level
					//print_r($category_id);exit;
					$category_level = $this->_getConfig('category_level') ? $this->_getConfig('category_level') : 0;
					if ($category_level != 0){
						// check category_tabs if active will get category_level
						$catids = explode(',',$category_id);
						$cate_active_ids = $this->_getActiveCategories($catids);
						if(in_array($root_cate, $catids)){
							$cate_list = $this->getCategoryCollection(true,$category_level,true,false);
						}else{
							$cate_list = $this->getCategoryCollection(true,$category_level,true,false,$cate_active_ids);
						}
						foreach ($cate_list as $cl) {
							$all_childrens[] = $cl->getId();
						}
					}else{
						$catids = explode(',',$category_id);
						$all_childrens = $this->_getAllChildren($catids);

						$tmp_all_childrens = array();
						// Merge array in array to array
						foreach ($all_childrens as $child) {
							foreach ($child as $value) {
								$tmp_all_childrens[] = $value;
							}
						}
						// Sort categories id by position
						$all_childrens = $this->getCatgoryIdsSortByPosition($tmp_all_childrens, true, true, false);
						
					}

					// get all_childrens active cates
					$all_childrens = $this->_getActiveCategories($all_childrens);

					if (!empty($all_childrens)){
						$flag = true;
						foreach($all_childrens as $children){
							switch ($children) {
								case $root_cate:
									// $key = $children;
									// $children = $all_childrens;
									// $count = $this->_getProductsBasic($children, true);
									// $cat_children = implode(',',  $children);
									$count = 0;
									break;
								
								default:
									$key = $children;
									$children = array($children);
									$count = $this->_getProductsBasic($children, true);
									$cat_children = implode(',',  $children);
									break;
							}
							if ($count > 0){
								$object_manager = $_objectManager->create('Magento\Catalog\Model\Category')->load($key);
								$list[$key]['name_tab'] =  $object_manager->getName();
								if ($object_manager->getId() == $root_cate){
									$list[$key]['name_tab'] = __('Highlights');
								}
								$list[$key]['count'] = $count;
								$list[$key]['id_tab'] = $key;
								$list[$key]['cat_children'] = $cat_children;
								if ($flag){
									$list[$key]['sel'] = 'active';
									$list[$key]['products_list'] = $this->_getProductsBasic($children);
									$flag = false;
								}
							}
						}
					}
				}
			break;
			case 'fieldproducts':
				if (!empty($category_select)){
					$catids = explode(',',$category_select);
					$all_childrens = $this->_getAllChildren($catids, true);
					$count = $this->_getProductsBasic($all_childrens, true);
					if (!empty($field_tabs) && $count > 0){
						$tabs = explode(',',$field_tabs);
						$flag = true;
						foreach($tabs as $key => $tab){
							$list[$tab]['name_tab'] =  $this->getLabel($tab);
							$list[$tab]['count'] = $count;
							$list[$tab]['id_tab'] = $tab;
							$list[$tab]['cat_children'] = implode(',',$all_childrens);
							if ($flag){
								$list[$tab]['sel'] = 'active';
								$list[$tab]['products_list'] = $this->_getProductsBasic($all_childrens, false, $tab);
								$flag = false;
							}
						}
					}
				}
			break;
			case 'accessories':
				// use register current product, not use session
				// $session_viewed_product_id = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Catalog\Model\Session')->getData('last_viewed_product_id');
				$session_viewed_product_id = $this->_getConfig('current_product_id');
				
				$summary_product_accessories = array();
				$product_kind = '';
				if($this->getCurrentProductById($session_viewed_product_id)){
					$current_product_accessories = $this->getCurrentProductById($session_viewed_product_id);

					// get phu kien chinh cua product hien tai dang xem
					$primary_related_products = array();
					if($current_product_accessories->getData('primary_related_products')){
						$primary_related_products = explode(',', $current_product_accessories->getData('primary_related_products'));
					}

					// get phu kien su dung chung cua product hien tai dang xem
					$secondary_related_products = array();
					if($current_product_accessories->getData('secondary_related_products')){
						$secondary_related_products = explode(',', $current_product_accessories->getData('secondary_related_products'));
					}

					// merge array & unique
					$summary_product_accessories = array_merge($primary_related_products, $secondary_related_products);
					$summary_product_accessories = array_unique($summary_product_accessories);

					// get product kind
					$product_kind = $current_product_accessories->getResource()->getAttribute('product_kind')->getFrontend()->getValue($current_product_accessories);
				}

				$show_product_kind_list = $this->_getConfig('show_product_kind_list') ? $this->_getConfig('show_product_kind_list'):'accessories,product';
				$show_product_kind_list = explode(',', $show_product_kind_list);

				// check product_kind
				switch ($product_kind) {
					case 'accessories':
						if(in_array($product_kind, $show_product_kind_list)){
							$product_accessories_ids = $this->_getProductIdsByAccessories($summary_product_accessories);

							if($product_accessories_ids){
								$catids = array();
								if (!empty($category_id)){
									$catids = explode(',',$category_id);
									$all_childrens = $this->_getAllChildren($catids);
									if (!empty($all_childrens)){
										$flag = true;
										foreach($all_childrens as $key => $children){
											$count = $this->_getProductsBasic($children, true,false,$product_kind,$product_accessories_ids);
											$cat_children = implode(',',  $children);
											if ($count > 0){
												$object_manager = $_objectManager->create('Magento\Catalog\Model\Category')->load($key);
												$list[$key]['name_tab'] =  $object_manager->getName();
												if ($object_manager->getId() == $root_cate){
													$list[$key]['name_tab'] = __('All');
												}
												$list[$key]['count'] = $count;
												$list[$key]['id_tab'] = $key;
												$list[$key]['cat_children'] = $cat_children;
												$list[$key]['product_kind'] = $product_kind;
												if ($flag){
													$list[$key]['sel'] = 'active';
													$list[$key]['products_list'] = $this->_getProductsBasic($children,false,false,$product_kind,$product_accessories_ids);
													$flag = false;
												}
											}
										}
									}
								}
							}
						}
						break;

					case 'product':
						if(in_array($product_kind, $show_product_kind_list)){
							$catids = array();
							if (!empty($category_id)){
								$catids = explode(',',$category_id);
								$all_childrens = $this->_getAllChildren($catids);
								if (!empty($all_childrens)){
									$flag = true;
									foreach($all_childrens as $key => $children){
										$count = $this->_getProductsBasic($children,true,false,$product_kind);
										$cat_children = implode(',',  $children);
										if ($count > 0){
											$object_manager = $_objectManager->create('Magento\Catalog\Model\Category')->load($key);
											$list[$key]['name_tab'] =  $object_manager->getName();
											if ($object_manager->getId() == $root_cate){
												$list[$key]['name_tab'] = __('All');
											}
											$list[$key]['count'] = $count;
											$list[$key]['id_tab'] = $key;
											$list[$key]['cat_children'] = $cat_children;
											$list[$key]['product_kind'] = $product_kind;
											if ($flag){
												$list[$key]['sel'] = 'active';
												$list[$key]['products_list'] = $this->_getProductsBasic($children,false,false,$product_kind);
												$flag = false;
											}
										}
									}
								}
							}
						}
						break;
				}
			break;
		}
		return $list;
	}	
	
	public function _ajaxLoad(){
		$catids = $this->getRequest()->getPost('catids');
		$tab_id = $this->getRequest()->getPost('tab_id');

		$type_filter = $this->_getConfig('type_filter');
		if ($type_filter == 'fieldproducts'){
			return  $this->_getProductsBasic($catids, false, $tab_id);
		}else{
			return $this->_getProductsBasic($catids);
		}
	}
	
	public function getLabel($filter)
	{
		switch ($filter) {
			case 'name':
				return __('Name');
			case 'entity_id':
				return __('Id');
			case 'price':
				return __('Price');
			case 'lastest_products':
				return __('New Products');
			case 'num_rating_summary':
				return __('Top Rating');
			case 'num_reviews_count':
				return __('Most Reviews');
			case 'num_view_counts':
				return __('Most Viewed');
			case 'ordered_qty':
				return __('Most Selling');
		}
	}
	
	private function _getAllChildren($catids, $group = false) {
		$_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$list = [];
		$cat_tmps = '';
		!is_array($catids) && $catids = preg_split('/[\s|,|;]/', $catids, -1, PREG_SPLIT_NO_EMPTY);
		if (!empty($catids) && is_array($catids)){
			foreach($catids as $i => $catid ) {
				$object_manager = $_objectManager->create('Magento\Catalog\Model\Category')->load($catid);
				if ($group){
					$cat_tmps .= $object_manager->getAllChildren().($i < count($catids) - 1 ? ',' : '');
				}else{
					$list[$catid] = $object_manager->getAllChildren(true);
				}
				
			}
			if ($group){
				if (!empty($cat_tmps)){
					$list = explode(',',$cat_tmps);
					return array_unique($list);
				}
			}
		}
		return $list;
	}

	public function _getActiveCategories($catids) {
		$_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$list = [];
		if (!empty($catids) && is_array($catids)){
			foreach($catids as $i => $catid ) {
				$object_manager = $_objectManager->create('Magento\Catalog\Model\Category')->load($catid);
				if ($object_manager->getIsActive()) {
					$list[] = $catid;
				}
				
			}
		}
		return $list;
	}

	public function getCatgoryIdsSortByPosition($cateids = array(), $isActive = true, $sortBy = false, $pageSize = false){
		$category_ids = array();
		$collection = $this->_categoryCollectionFactory->create();

		// get by list ids
		if(!empty($cateids)){
            $collection->addFieldToFilter('entity_id', array(
                                            'in' => $cateids)
                                         );
        }

        // select only active categories
        if ($isActive) {
            $collection->addIsActiveFilter();
        }

        // sort categories by some value
        if ($sortBy) {
            $collection->addOrderField($sortBy);
            $collection->addAttributeToSort('position');
        }

        // set pagination
        if ($pageSize) {
            $collection->setPageSize($pageSize); 
        }

        foreach ($collection as $col) {
        	$category_ids[] = $col->getId();
        }
        
        return $category_ids;
	}

	public function getCategoryCollection($isActive = true, $level = false, $sortBy = false, $pageSize = false, $cateids = array()) {
        $collection = $this->_categoryCollectionFactory->create();

        if(!empty($cateids)){
            $collection->addFieldToFilter('parent_id', array(
                                            'in' => $cateids)
                                         );
        }

        $collection->addAttributeToSelect('*');
        
        // select only active categories
        if ($isActive) {
            $collection->addIsActiveFilter();
        }
        // select categories of certain level
        // fix for khodungcu get only level
        if ($level) {
            //$collection->addLevelFilter($level);
            $collection->addFieldToFilter('level', ['eq' => $level]);
        }

        // sort categories by some value
        if ($sortBy) {
            $collection->addOrderField($sortBy);
            $collection->addAttributeToSort('position');
        }

        // set pagination
        if ($pageSize) {
            $collection->setPageSize($pageSize); 
        }
        
        return $collection;
    }
	
	public function _getOrderFields(& $collection , $tab = false) {
		$multi_order_by = $this->_getConfig('multi_order_by') ? json_decode(stripslashes($this->_getConfig('multi_order_by'))):'';
		$order_by = $tab ? $tab : $this->_getConfig('order_by');
		$order_dir = $this->_getConfig('order_dir');
		$arr_exists_orders = array('entity_id','name','lastest_products','created_at','price','num_rating_summary','num_view_counts','num_reviews_count','ordered_qty','viewed_products_added_at');

		if($multi_order_by){
			$order_by_string = array();
			foreach ($multi_order_by as $orderby_attr) {
				switch ($orderby_attr->order_by) {
					case 'viewed_products_added_at':
						$customerSession = $this->_objectManager->create(\Magento\Customer\Model\Session::class);
						if($customerSession->isLoggedIn()){
							$order_by_string[] = $orderby_attr->order_by.' '.$orderby_attr->order_dir;
						}
						break;
					
					default:
						$order_by_string[] = $orderby_attr->order_by.' '.$orderby_attr->order_dir;
						break;
				}

				// trick add attribute left join query
				// check in array data
				if(!in_array($orderby_attr->order_by, $arr_exists_orders)){
					$collection->addFieldToFilter($orderby_attr->order_by, array('like' => '%%'));
				}
			}
			// add order 
			$collection->getSelect()->order($order_by_string);
		}else{
			switch ($order_by) {
				default:
				case 'entity_id':
				case 'name':
					$collection->setOrder($order_by, $order_dir);
					//$collection->getSelect()->order($order_by.' '. $order_dir );
					break;
				case 'lastest_products':		
				case 'created_at':
					$tab ? $collection->getSelect()->order('created_at  DESC') : $collection->getSelect()->order('created_at ' . $order_dir . '');
					break;
				case 'price':
					$collection->getSelect()->order('final_price ' . $order_dir . '');
					break;
				case 'num_rating_summary':
					$tab ? $collection->getSelect()->order('num_rating_summary DESC') : $collection->getSelect()->order('num_rating_summary ' . $order_dir . '');
					break;
				case 'num_reviews_count':
					$tab ? $collection->getSelect()->order('num_reviews_count DESC') : $collection->getSelect()->order('num_reviews_count ' . $order_dir . '');
					break;
				case 'num_view_counts':
					$tab ? $collection->getSelect()->order('num_view_counts DESC') : $collection->getSelect()->order('num_view_counts ' . $order_dir . '');
					break;
				case 'ordered_qty':
					$tab ?  $collection->getSelect()->order('ordered_qty DESC') :  $collection->getSelect()->order('ordered_qty ' . $order_dir . '');
					break;
				
			}
		}
		
		
		return $collection;
	}
	
	public function _getViewedProductIdsByCurrentSession(){
		$ids = [];
		$customerSession = $this->_objectManager->create(\Magento\Customer\Model\Session::class);
		$sessionInterface = $this->_objectManager->create(\Magento\Framework\Session\SessionManagerInterface::class);
		
		var_dump($customerSession->isLoggedIn());
		var_dump($sessionInterface->getVisitorData()["visitor_id"]);
		var_dump($customerSession->getCustomer()->getId()); 
		$resource = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
		$connection = $resource->getConnection();
		$tableName = $resource->getTableName('report_viewed_product_index'); //gives table name with prefix
		 
		// Select Data from table
		$sql = "SELECT product_id FROM " . $tableName;
		$binds = [];
		if($customerSession->isLoggedIn()){
			$sql = $sql . " WHERE customer_id IN (:customer_id)";
			$binds['customer_id'] = $customerSession->getCustomer()->getId();
		}else{
			$sql = $sql . " WHERE visitor_id IN (:visitor_id)";
			$binds['visitor_id'] = $sessionInterface->getVisitorData()["visitor_id"];
		}
		$result = $connection->fetchAll($sql,$binds); // gives associated array, table fields as key in array.
		$ids = [];
		// Get Ids
		foreach ($result as $key => $value) {
			$ids[]= $value["product_id"];
		}
		// Return		
		return $ids;
	}
	public function _isGuest(){
		$customerSession = $this->_objectManager->create(\Magento\Customer\Model\Session::class);
		return !$customerSession->isLoggedIn();
	}
	public function _getViewedProductIdsByCustomerId($customerId){
		$ids = [];		
		$resource = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
		$connection = $resource->getConnection();
		$tableName = $resource->getTableName('report_viewed_product_index'); //gives table name with prefix
		 
		// Select Data from table
		$sql = "SELECT product_id FROM " . $tableName;
		$binds = [];
		$sql = $sql . " WHERE customer_id IN (:customer_id)";
		$binds['customer_id'] = $customerId;
		$result = $connection->fetchAll($sql,$binds); // gives associated array, table fields as key in array.
		$ids = [];
		// Get Ids
		foreach ($result as $key => $value) {
			$ids[]= $value["product_id"];
		}
		// Return		
		return $ids;
	}

	public function getCurrentProductById($product_id){      
		return \Magento\Framework\App\ObjectManager::getInstance()->get('\Magento\Catalog\Model\ProductRepository')->getById($product_id);
    }

	public function _getProductIdsByAccessories($product_skus){
		// print_r($product_skus);exit;
		$product_ids = array();

		// get list products
		$products =$this->_objectManager->create('\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory')->create();
		$products->addAttributeToSelect('entity_id','sku');
		$products->addFieldToFilter('sku', array(
                                        'in' => $product_skus)
                                        );
		// add order 
		$order_by_string = "FIELD(sku,'".implode("','", $product_skus)."')";
		$products->getSelect()->order($order_by_string);

		foreach ($products as $product) {
			$product_ids[] = $product->getId();
		}

		return $product_ids;
	}


	public function _getProductIdsByRules($rule){
		$product_ids = array();

		// get list products
		$products =$this->_objectManager->create('\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory')->create();
		$products->addAttributeToSelect('*');
		// Check that product in Cart Rules
		// if true => will add product ids into array
		foreach ($products as $product) {
			switch ($rule) {
				case 'all_rules':
					$validate_cartrules = $this->_hasProductInCartRules($product);
					$validate_catalogrules = $this->_hasProductInCatalogRules($product);
					if($validate_cartrules == true || $validate_catalogrules == true){
						$product_ids[] = $product->getId();
					}
					break;

				case 'cart_rules':
					$validate_cartrules = $this->_hasProductInCartRules($product);
					if($validate_cartrules == true){
						$product_ids[] = $product->getId();
					}
					break;

				case 'catalog_rules':
					$validate_catalogrules = $this->_hasProductInCatalogRules($product);
					if($validate_catalogrules == true){
						$product_ids[] = $product->getId();
					}
					break;
			}
			
		}

		return $product_ids;
	}

	public function _hasProductInCartRules($product){
        // get info cart price rule
        $ruleNames = array();
        $rules = $this->_objectManager->create('Magento\SalesRule\Model\RuleFactory')->create();
        $rules=$rules->getCollection();

        $objDate = $this->_objectManager->create('Magento\Framework\Stdlib\DateTime\DateTime');
        $_currentTime = strtotime($objDate->date());

        // include customer group id when user logged in
        $customerGroupId = 0; 
        if($this->_customerSession->isLoggedIn()){
            $customerGroupId = $this->_customerSession->getCustomer()->getGroupId();
        }

        foreach ($rules as $rule) {
            $fromDate = $rule->getFromDate();
            $toDate = $rule->getToDate();
            $isActive = $rule->getIsActive();
            $stopRulesProcessing = $rule->getStopRulesProcessing();

            // Check status cart rule is active
            // check stop processing is 0 
            // (maximum qty discount = max number) => stop process = 1
            if  (
                isset($fromDate) 
                && $_currentTime >= strtotime($fromDate)
                && ($isActive == 1 && $stopRulesProcessing == 0)
                && (strtotime($toDate) >= $_currentTime || !isset($toDate))
                ) 
            {
                $item = $this->_objectManager->create('Magento\Catalog\Model\Product');
                $item->setProduct($product);

                if ( $rule->getActions()->validate($item)
                    //&& ( $rule->getData('simple_action') == 'ampromo_items' )
                    && $product->getPrice() > 0
                    && in_array($customerGroupId, $rule->getCustomerGroupIds())
                    ) 
                {
                    return true;
                }
            }
        }

        return false;
    }

    public function _hasProductInCatalogRules($product){
        // get info catalog price rule
        $priceRules = null;
        $rd = null;
        $productId = $product->getId();
        $price = $product->getPriceInfo()->getPrice('regular_price')->getAmount()->getValue();
        $storeId = $product->getStoreId();
        $dateTs = $this->_localeDate->scopeTimeStamp($storeId);
        $websiteId = $this->_storeManager->getStore($storeId)->getWebsiteId();

        // include customer group id when user logged in
        $customerGroupId = 0; 
        if($this->_customerSession->isLoggedIn()){
            $customerGroupId = $this->_customerSession->getCustomer()->getGroupId();
        }

        // get list catalog price rules name
        $ruleNames = array();
        $rules =  $this->_ruleResource->getRulesFromProduct($dateTs, $websiteId, $customerGroupId, $product->getId());
        
        // sort by priority
        $sort = array();
        foreach ($rules as $key => $row)
        {
            $sort[$key] = $row['sort_order'];
        }
        array_multisort($sort, SORT_ASC, $rules);

        // Get applied rules
        // Case action_stop (Discard subsequent rules)
        // case 1: A, B cùng priority, A tạo trước chọn yes, B tạo sau chọn yes|no => nó apply A
        // case 2: A, B cùng priority, A tạo trước chọn no, B tạo sau chọn yes => nó apply A + B
        // case 3: A priority 0, B priority 1, A, B chọn yes => nó apply A
        // case 4: A priority 0, B priority 1, A chọn no, B chọn yes => nó apply A + B
        foreach ($rules as $rule) {
            if($product->getPrice() > 0){
                return true;
            }
            if($rule['action_stop'] == 1){break;}
        }

        return false;
    }

	public function _getProductsBasic($catids = null, $count = false , $tab = false, $product_kind = 'product', $accepted_product_ids = array())
	{
		//---> Get Config
		$type_product = $this->_getConfig('type_product');
		$type_filter = $this->_getConfig('type_filter');
		$limit = $this->_getConfig('limitation');    
		$type_listing = $this->_getConfig('type_listing');   
		$under_price = $this->_getConfig('under_price', '4.99'); 	
        $catids =  $catids == null ? $this->_getConfig('category_tabs') : $catids;        
		!is_array($catids) && $catids = preg_split('/[\s|,|;]/', $catids, -1, PREG_SPLIT_NO_EMPTY);
		$without_product_ids =  $this->_getConfig('without_product_ids'); 
		!is_array($without_product_ids) && $without_product_ids = preg_split('/[\s|,|;]/', $without_product_ids, -1, PREG_SPLIT_NO_EMPTY);
		$category_level = $this->_getConfig('category_level') ? $this->_getConfig('category_level') : 0;
		$current_page = $this->_getConfig('current_page') ? $this->_getConfig('current_page') : 'block';
		$order_by = $this->_getConfig('order_by') ? $this->_getConfig('order_by') : 'created_at';
		$order_dir = $this->_getConfig('order_dir') ? $this->_getConfig('order_dir') : 'DESC';
		$current_page_type = $this->_getConfig('current_page_type') ? $this->_getConfig('current_page_type'):'catalog';
		
		// Filter Attribute
		$filtered_attr_products = array_filter($this->_getConfig(), function($k){
			return preg_match('/^ecep_attr_/', $k);
		}, ARRAY_FILTER_USE_KEY);

		//---> Prepare Collection
		$collection =$this->_objectManager->create(\Magento\Catalog\Model\ResourceModel\Product\Collection::class);
		$isIgnoreFilterCategory = false;
		$customerSession = $this->_objectManager->create(\Magento\Customer\Model\Session::class);

		
		if ($type_listing == 'under'){
			$collection->addPriceDataFieldFilter('%s < %s', ['final_price', $under_price]);
		}
		$collection->addMinimalPrice()
			->addFinalPrice()
			->addTaxPercents()
			->addAttributeToSelect($this->_catalogConfig->getProductAttributes())
			->addAttributeToSelect('special_from_date')
			->addAttributeToSelect('special_to_date')
			->addUrlRewrite()
			->setStoreId($this->_storeId)
			->addAttributeToFilter('status',\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);		
		if (!empty($without_product_ids)){
			$collection->addFieldToFilter('entity_id', array(
                                        'nin' => $without_product_ids)
                                        );
		}
	    if ($type_listing == 'deals'){
			$now = date('Y-m-d H:i:s');
			$collection->addAttributeToFilter('special_price', ['neq' => ''])
					   ->addAttributeToFilter('special_from_date', ['lteq' => date('Y-m-d  H:i:s', strtotime($now))])
					   ->addAttributeToFilter('special_to_date', ['gteq' => date('Y-m-d  H:i:s', strtotime($now))]);
		}
		// if ($type_product == 'on_sale'){
		// 	$collection->getSelect()->where('price_index.final_price < price_index.price');		
		// }
		switch ($type_product) {
			case 'on_sale':
				$collection->getSelect()->where('price_index.final_price < price_index.price');	
				break;
			
			case 'on_news_from_to_date':
				$todayDate = date('Y-m-d');
				$collection->addAttributeToFilter('news_from_date', array('date' => true, 'to' => $todayDate))
				->addAttributeToFilter('news_to_date', array('or'=> array(
                    0 => array('date' => true, 'from' => $todayDate),
                    1 => array('is' => new \Zend_Db_Expr('null')))
                ), 'left')
                ->addAttributeToSort('news_from_date', 'desc');
				break;

			case 'viewed':
				if($customerSession->isLoggedIn()){
					$customerId =  $customerSession->getCustomer()->getId();
					$collection->addFieldToFilter('entity_id', array(
	                                        'in' => $this->_getViewedProductIdsByCustomerId($customerId)
	                                        )
	                                    );
					// get viewed_products_added_at
					$this->_getAddedAtViewedProducts($collection);
				}else{
					$isIgnoreFilterCategory = true;
					$recentlyViewedProductIds = $this->getRequest()->getPost('viewed_product_ids');	
					$collection->addFieldToFilter('entity_id', array(
	                                        'in' => $recentlyViewedProductIds
	                                        )
	                                    );
				}
				break;
			
			case 'all_rules':
			case 'cart_rules':
			case 'catalog_rules':

				$listed_product_ids = $this->_getProductIdsByRules($type_product);
				$collection->addFieldToFilter('entity_id', array(
		                                        'in' => $listed_product_ids
		                                        )
		                                    );
				break;

			case 'customer_data':
				$collection->getSelect()->columns('(COALESCE(num_view_counts,0)+COALESCE(ordered_qty*10,0)) AS priority_bs_mvp');
				$collection->getSelect()->where('(bs.product_id IS NOT NULL OR num_view_counts IS NOT NULL)');
				$collection->getSelect()->reset(\Zend_Db_Select::ORDER)->order('priority_bs_mvp desc');
				break;

			case 'accessories':
				switch ($product_kind) {
					case 'accessories':
						// filter by accepted_product_id
						$collection->addFieldToFilter('entity_id', array(
				                                    	'in' => $accepted_product_ids
				                                    	)
				                                    );
						// add order 
						$order_by_string = "FIELD(e.entity_id,'".implode("','", $accepted_product_ids)."')";
						$collection->getSelect()->order($order_by_string);
						break;
					
					case 'product':
						$session_viewed_product_id = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Catalog\Model\Session')->getData('last_viewed_product_id');
						if($this->getCurrentProductById($session_viewed_product_id)){
							$viewed_product = $this->getCurrentProductById($session_viewed_product_id);
							$viewed_product_sku = $viewed_product->getSku();
							// search filter by attribute
							$collection->addFieldToFilter(
							    array(
							        array('attribute' => 'primary_related_products', 'like' => '%'.$viewed_product_sku.'%'),
							        array('attribute' => 'secondary_related_products', 'like' => '%'.$viewed_product_sku.'%')
							    )
							);
						}
						break;
				}
				
				break;
		}
		if ($type_listing == 'random'){
			$collection->getSelect()->orderRand();
		}

		// Filter Attribute
		// filter type
        if(!empty($filtered_attr_products)){
            foreach ($filtered_attr_products as $attr_key => $attr_value) {
                $attr_key = explode('ecep_attr_', $attr_key);
                $attr = $this->getAttributeProperties($attr_key[1]);
                $source = $attr->getSource();
                $frontend_input = $attr->getFrontendInput();

                // check frontend input text, textarea, date, hidden, boolean, multiline, image, multiselect, price, weight, media_image, gallery
                // only filter for text, textarea, boolean, multiselect, select
                switch ($frontend_input) {
                    case 'select':
                    case 'multiselect':
                        // get Source Option Value
                        $source_value = $attr->getSource()->getOptionId($attr_value);
                        // filter by source value
                        $collection->addAttributeToFilter($attr_key[1], array('eq' => $source_value));
                        break;

                    case 'text':
                    case 'textarea':
                        // filter by source value
                        $collection->addAttributeToFilter($attr_key[1], array('like' => '%'.$attr_value.'%'));
                        break;

                    case 'boolean':
                        // filter by source value
                        $collection->addAttributeToFilter($attr_key[1], array('eq' => $attr_value));
                        break;

                    case 'date':
                    case 'hidden':
                    case 'multiline':
                    case 'image':
                    case 'price':
                    case 'weight':
                    case 'media_image':
                    case 'gallery':
                        break;
                }
            }
        }

        // Filter From Get Requests
        if($current_page == 'catalog'){
        	$this->_addRequestFilterToCollection($collection);
        }
				
		//$collection->addAttributeToFilter('is_saleable', 1, 'left');			
		$collection->addAttributeToFilter('is_saleable', [1], 'left'); //Fix 2.3.3 must be of the type array or nul
		
		// Check case category level
		// will get all products in child categories
		if($category_level != 0){
			$child_cateids = $this->_getAllChildren($catids);
			$child_cateids = $child_cateids[$catids[0]];
			
			// Filter WHERE cate ids IN list cates in products
			$collection->addCategoriesFilter(['in' => $child_cateids]);
		}else{
			if ($isIgnoreFilterCategory == false){
				$category_req = '';
				if($this->_isAjax()){
		    		$requests = $this->getRequest()->getParam('get_request');
		    		// request category
		    		$category_req = isset($requests['cat']) ? $requests['cat']:'';
		    	}else{
		    		$requests = $this->getRequest()->getParams();
		    		// request category
		    		$category_req = $this->getRequest()->getParam('cat') ? $this->getRequest()->getParam('cat'):'';
		    	}

		    	if($category_req != ''){
		    		$collection->addCategoriesFilter(['eq' => $category_req]);
		    	}else{
		    		(!empty($catids) && $catids) ? $collection->addCategoriesFilter(['in' => $catids]) : '';
		    	}
			}
		}

		$collection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds());
		$this->_getViewedCount($collection);
		$this->_getOrderedQty($collection);
		$this->_getReviewsCount($collection);
		$this->_getMaxPositionCollection($collection);

		$tab ? $this->_getOrderFields($collection , $tab) : $this->_getOrderFields($collection);

		// // get query
		// $query = trim($this->getRequest()->getParam('q'));
		// if($query){
		// 	$collection->addFieldToFilter('name', array('like' => '%'.$query.'%'));	
		// }

		if($current_page == 'catalog'){
			// Search feature
			if($current_page_type == 'search'){
				// check type load
				if($this->_isAjax()){
		    		$requests = $this->getRequest()->getParam('get_request');
		    		// query
		    		$query = isset($requests['q']) ? trim($requests['q']):'';
		    	}else{
		    		$requests = $this->getRequest()->getParams();
		    		// query
		    		$query = $this->getRequest()->getParam('q') ? trim($this->getRequest()->getParam('q')):'';
		    	}
		    	// query 
		    	if($query){
					//$collection->addFieldToFilter('name', array('like' => '%'.$query.'%'));	
					$this->_getQueryCollection($collection,$query);
				}
			}

			// reset order
			$collection->getSelect()->reset(\Zend_Db_Select::ORDER);
        	$category = $this->_objectManager->create('Magento\Catalog\Model\Category')->load($catids[0]);
			
			// orderby attribute
			$order_by_attribute = $category->getData('chottvn_orderby_attribute') ? json_decode($category->getData('chottvn_orderby_attribute')):array();

			if($this->_isAjax()){
	    		$requests = $this->getRequest()->getParam('get_request');
	    		// product_list_dir
				// product_list_order
	    		$product_list_order = isset($requests['order_by']) ? $requests['order_by']:'';
	    		$product_list_dir = isset($requests['order_by']) ? $requests['order_by']:'';
	    	}else{
	    		$requests = $this->getRequest()->getParams();
	    		// product_list_dir
				// product_list_order
	    		$product_list_order = $this->getRequest()->getParam('order_by') ? $this->getRequest()->getParam('order_by'):'';
				$product_list_dir = $this->getRequest()->getParam('order_dir') ? $this->getRequest()->getParam('order_dir'):'';
	    	}

	    	// this is old code
	    	// trick add attribute left join query
			// get key array
			$key_requests = array();
			foreach ($requests as $kreq => $vreq) {
				$key_requests[] = $kreq;
			}

			// if($product_list_order != '' || $product_list_dir != ''){
			// 	$product_list_order = $product_list_order ? $product_list_order:'position';
			// 	$product_list_dir = $product_list_dir ? $product_list_dir:'asc';

				
			// 	$collection->getSelect()->order($product_list_order.' '.$product_list_dir);
			// }else{
			// 	if(!empty($order_by_attribute)){
			// 		$order_by_string = array();
			// 		foreach ($order_by_attribute as $orderby_attr) {
			// 			$order_by_string[] = $orderby_attr->order_name.' '.$orderby_attr->order_type;

			// 			// trick add attribute left join query
			// 			// check in array data
			// 			if(!in_array($orderby_attr->order_name, $key_requests)){
			// 				$collection->addAttributeToFilter(array(array('attribute'=> $orderby_attr->order_name,'like' => '%%')),'','left');
			// 			}
						
			// 		}
			// 		$collection->addAttributeToFilter(array(array('attribute'=> 'power','like' => '%%')),'','left');
			// 		$collection->getSelect()->reset(\Zend_Db_Select::ORDER);
			// 		$collection->getSelect()->order($order_by_string);
			// 	}
			// }

	    	// thu tu uu tien 1
			// if($product_list_order != '' || $product_list_dir != ''){
			// 	$product_list_order = $product_list_order ? $product_list_order:'position';
			// 	$product_list_dir = $product_list_dir ? $product_list_dir:'asc';

			// 	// trick add attribute left join query
			// 	// check in array data

			// 	$collection->getSelect()->order($product_list_order.' '.$product_list_dir);
			// }

			// thu tu uu tien 1
			if($product_list_order != ''){
				$product_list_order = $product_list_order ? $product_list_order:$order_by;
				$product_list_dir = $product_list_dir ? $product_list_dir:$order_dir;

				// trick add attribute left join query
				// check in array data
				$sorter_config = $this->_getSorterConfiguration();
				
				// get string order by
				$str_order_by = $order_by.' '.$order_dir;
				if(!empty($sorter_config)){
					foreach ($sorter_config as $sort) {
						if($sort->sort_by == $product_list_order){
							$tmp_order_by = explode(',', $sort->order_by);
							$str_order_by = array();
							foreach ($tmp_order_by as $tmp_order) {
								$str_order_by[] = trim($tmp_order);
							}

							if($sort->addfilter != ''){
								$addfilter = explode(',', $sort->addfilter);
								foreach ($addfilter as $vfilter) {
									//$collection->addFieldToFilter($vfilter, array('like' => '%%'));
									$collection->addFieldToFilter(
										array(
											array('attribute'=> $vfilter,'like' => '%%'),
											array('attribute'=> $vfilter,'null' => true)
										),
										'',
										'left'
									);
								}
								
							}
							break;
						}
					}
				}
			
				$collection->getSelect()->order($str_order_by);
			}

			// add order 
			// thu tu uu tien 2
			$collection->getSelect()->order('position_cat ASC');

			// thu tu uu tien 3
			if(!empty($order_by_attribute)){
				$order_by_string = array();
				foreach ($order_by_attribute as $orderby_attr) {
					// trick add attribute left join query
					// check in array data
					if(!in_array($orderby_attr->order_name, $key_requests)){
						$orderby_attr_name = $orderby_attr->order_name;
						$orderby_attr_type = isset($orderby_attr->order_type) ? $orderby_attr->order_type : 'asc';

						switch ($orderby_attr->order_name) {
							case 'product_brand':
								$collection->addAttributeToFilter(
									array(
										array('attribute'=> $orderby_attr_name,'like' => '%%'),
										array('attribute'=> $orderby_attr_name,'null' => true)
									),
									'',
									'left'
								);
								$this->_getProductBrandNameCollection($collection);
								$order_by_string[] = 'brand_name'.' '.$orderby_attr_type;
								break;
							
							default:
								if($orderby_attr_name !='position'){
									$collection->addAttributeToFilter(
										array(
											array('attribute'=> $orderby_attr_name,'like' => '%%'),
											array('attribute'=> $orderby_attr_name,'null' => true)
										),
										'',
										'left'
									);
								}
								$order_by_string[] = $orderby_attr_name.' '.$orderby_attr_type;
								break;
						}
						// if($orderby_attr->order_name !='position'){
						// 			$collection->addAttributeToFilter(
						// 				array(
						// 					array('attribute'=> $orderby_attr->order_name,'like' => '%%'),
						// 					array('attribute'=> $orderby_attr->order_name,'null' => true)
						// 				),
						// 				'',
						// 				'left'
						// 			);
						// 		}
						
						//$collection->addFieldToFilter($orderby_attr->order_name);
					}
				}
				// add order 
				$collection->getSelect()->order($order_by_string);
			}
			// $todayDate = date('Y-m-d');
			// 	$collection->addAttributeToFilter('news_from_date', array('date' => true, 'to' => $todayDate))
			// 	->addAttributeToFilter('news_to_date', array('or'=> array(
   //                  0 => array('date' => true, 'from' => $todayDate),
   //                  1 => array('is' => new \Zend_Db_Expr('null')))
   //              ), 'left')
   //              ->addAttributeToSort('news_from_date', 'desc');
			// echo $collection->getSelect();
			// echo '<pre>';
			// print_r(json_decode($category->getData('chottvn_orderby_attribute')));
			// echo '</pre>';
			//$collection->getSelect()->order('created_at  DESC')
        }

        // echo $collection->getSelect();exit;
		$collection->clear();
		$collection->getSelect()->distinct(true);
		
		//---> Return
		if ($count){
			return $collection->count();
		}
		$start = (int)$this->getRequest()->getPost('ajax_listingtabs_start');
		if (!$start) $start = 0;
		$_limit = $limit;
		$_limit = $_limit <= 0 ? 0 : $_limit;
		$collection->getSelect()->limit($_limit, $start);
		return  $collection;
	}

	public function getAttributeProperties($attr_key){
        $eavConfig = $this->_objectManager->get('\Magento\Eav\Model\Config');
        $attribute = $eavConfig->getAttribute('catalog_product', $attr_key);

        return $attribute;
    }

    private function _getNewProduct(& $collection) {
		$connection  = $this->_resource->getConnection();
	    // GET NEW DATA
	    // $query = "SELECT '1' AS is_new_arrival
	    // 				,`e`.`entity_id`
	    // 			FROM `catalog_product_entity` AS `e`
					// INNER JOIN `catalog_product_entity_datetime` AS `at_news_from_date_default` ON (`at_news_from_date_default`.`entity_id` = `e`.`entity_id`)
					// AND (`at_news_from_date_default`.`attribute_id` = '94')
					// AND `at_news_from_date_default`.`store_id` = 0
					// LEFT JOIN `catalog_product_entity_datetime` 
					// 	AS `at_news_from_date` 
					// 	ON (`at_news_from_date`.`entity_id` = `e`.`entity_id`)
					// 		AND (`at_news_from_date`.`attribute_id` = '94')
					// 		AND (`at_news_from_date`.`store_id` = 1)
					// LEFT JOIN `catalog_product_entity_datetime` 
					// 	AS `at_news_to_date_default` 
					// 	ON (`at_news_to_date_default`.`entity_id` = `e`.`entity_id`)
					// 		AND (`at_news_to_date_default`.`attribute_id` = '95')
					// 		AND `at_news_to_date_default`.`store_id` = 0
					// LEFT JOIN `catalog_product_entity_datetime` 
					// 	AS `at_news_to_date` 
					// 	ON (`at_news_to_date`.`entity_id` = `e`.`entity_id`)
					// 		AND (`at_news_to_date`.`attribute_id` = '95')
					// 		AND (`at_news_to_date`.`store_id` = 1)
					// WHERE (IF(`at_news_from_date`.`value_id` > 0, `at_news_from_date`.`value`, `at_news_from_date_default`.`value`) <= '2020-06-15 00:00:00')
					//   		AND (((((IF(`at_news_to_date`.`value_id` > 0, `at_news_to_date`.`value`, `at_news_to_date_default`.`value`) >= '2020-06-15 00:00:00') 
					//   		OR (IF(`at_news_to_date`.`value_id` > 0, `at_news_to_date`.`value`, `at_news_to_date_default`.`value`) IS NULL)))))"; 
		$select = $connection
            ->select()
            ->from($connection->getTableName($this->_resource->getTableName('catalog_product_entity')), array())
            ->join(array("at_news_from_date_default" => "catalog_product_entity_datetime"), "(`at_news_from_date_default`.`entity_id` = `catalog_product_entity`.`entity_id`)
					AND (`at_news_from_date_default`.`attribute_id` = '94')
					AND `at_news_from_date_default`.`store_id` = 0",array('1 as news_from_date'))
            ->join(array("at_news_from_date" => "catalog_product_entity_datetime"), "(`at_news_from_date`.`entity_id` = `catalog_product_entity`.`entity_id`)
							AND (`at_news_from_date`.`attribute_id` = '94')
							AND (`at_news_from_date`.`store_id` = 1)",array())
            ->join(array("at_news_to_date_default" => "catalog_product_entity_datetime"), "(`at_news_to_date_default`.`entity_id` = `catalog_product_entity`.`entity_id`)
							AND (`at_news_to_date_default`.`attribute_id` = '95')
							AND `at_news_to_date_default`.`store_id` = 0",array())
            ->join(array("at_news_to_date" => "catalog_product_entity_datetime"), "(`at_news_to_date`.`entity_id` = `catalog_product_entity`.`entity_id`)
							AND (`at_news_to_date`.`attribute_id` = '95')
							AND (`at_news_to_date`.`store_id` = 1)",array())
            ->where("(IF(`at_news_from_date`.`value_id` > 0, `at_news_from_date`.`value`, `at_news_from_date_default`.`value`) <= '2020-06-15 00:00:00')
					  		AND (((((IF(`at_news_to_date`.`value_id` > 0, `at_news_to_date`.`value`, `at_news_to_date_default`.`value`) >= '2020-06-15 00:00:00') 
					  		OR (IF(`at_news_to_date`.`value_id` > 0, `at_news_to_date`.`value`, `at_news_to_date_default`.`value`) IS NULL)))))");
            echo $select->__toString();exit;
        $collection->getSelect()
            ->joinLeft(array('new' => $select),
                'new.new_product_id = e.entity_id', array("new.news_from_date"));
        return $collection;
	}

	// get query collection from catalogsearch_fulltext_scope<store_id>
	private function _getQueryCollection(& $collection,$query) {
		$connection  = $this->_resource->getConnection();
		$table_search_fulltext = trim('catalogsearch_fulltext_scope'.$this->_storeId);
		$select = $connection
			->select()
			->from($connection->getTableName($this->_resource->getTableName($table_search_fulltext)), ['*'])
			->where("data_simple_index like '%".$this->convertName($query)."%'")
			->group('entity_id');
		$collection->getSelect()
			->joinInner(['qc' => $select],
				'qc.entity_id = e.entity_id');
		return $collection;			
	}

	// get max position sort category
	private function _getMaxPositionCollection(& $collection) {
		$connection  = $this->_resource->getConnection();
		$table = trim('catalog_category_product');
		$select = $connection
			->select()
			->from($connection->getTableName($this->_resource->getTableName($table)), ['product_id','MAX(position) AS position_cat'])
			->group('product_id');
		$collection->getSelect()
			->joinInner(['ccp' => $select],
				'ccp.product_id = e.entity_id');
		return $collection;			
	}

	// get max position sort category
	private function _getProductBrandNameCollection(& $collection) {
		$connection  = $this->_resource->getConnection();
		$table = trim('ves_brand');
		$select = $connection
			->select()
			->from($connection->getTableName($this->_resource->getTableName($table)), ['brand_id','name AS brand_name'])
			->group('brand_id');
		$collection->getSelect()
			->joinInner(['vb' => $select],
				'vb.brand_id = at_product_brand.value');
		return $collection;			
	}

    public function convertName($str) {
		$str = preg_replace("/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/", 'a', $str);
		$str = preg_replace("/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/", 'e', $str);
		$str = preg_replace("/(ì|í|ị|ỉ|ĩ)/", 'i', $str);
		$str = preg_replace("/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/", 'o', $str);
		$str = preg_replace("/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/", 'u', $str);
		$str = preg_replace("/(ỳ|ý|ỵ|ỷ|ỹ)/", 'y', $str);
		$str = preg_replace("/(đ)/", 'd', $str);
		$str = preg_replace("/(À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ)/", 'A', $str);
		$str = preg_replace("/(È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ)/", 'E', $str);
		$str = preg_replace("/(Ì|Í|Ị|Ỉ|Ĩ)/", 'I', $str);
		$str = preg_replace("/(Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ)/", 'O', $str);
		$str = preg_replace("/(Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ)/", 'U', $str);
		$str = preg_replace("/(Ỳ|Ý|Ỵ|Ỷ|Ỹ)/", 'Y', $str);
		$str = preg_replace("/(Đ)/", 'D', $str);
		$str = strtolower($str);

		return $str;
	}
	
	private function _getOrderedQty(& $collection) {
		$connection  = $this->_resource->getConnection();
		$select = $connection
            ->select()
            ->from($connection->getTableName($this->_resource->getTableName('sales_bestsellers_aggregated_daily')), array('product_id', 'ordered_qty' => 'SUM(`qty_ordered`)'))
            ->where("store_id=".$this->_storeId."")
			->group('product_id');

        $collection->getSelect()
            ->joinLeft(array('bs' => $select),
                'bs.product_id = e.entity_id');			
        return $collection;
	}

	private function _getAddedAtViewedProducts(& $collection) {
		$connection  = $this->_resource->getConnection();
		$select = $connection
            ->select()
            ->from(
            	$connection->getTableName(
            		$this->_resource->getTableName('report_viewed_product_index')
            	), 
            	array('product_id', 'viewed_products_added_at' => 'added_at')
            )->where("store_id=".$this->_storeId."")
			->group('product_id');

        $collection->getSelect()
            ->joinLeft(array('rvpi' => $select),
                'rvpi.product_id = e.entity_id');			
        return $collection;
	}
	
	private function _getViewedCount(& $collection) {
		$connection  = $this->_resource->getConnection();
		$select = $connection
			->select()
			->from($connection->getTableName($this->_resource->getTableName('report_event')), ['*', 'num_view_counts' => 'COUNT(`event_id`)'])
			->where("event_type_id = 1 AND store_id=".$this->_storeId."")
			->group('object_id');
		$collection->getSelect()
			->joinLeft(['mv' => $select],
				'mv.object_id = e.entity_id');
		return $collection;			
	}
	
	private function _getReviewsCount(& $collection)
	{	$connection  = $this->_resource->getConnection();
		$collection->getSelect()
			->joinLeft(
				["ra" => $connection->getTableName($this->_resource->getTableName('review_entity_summary'))],
				"e.entity_id = ra.entity_pk_value AND ra.store_id=" . $this->_storeId,
				[
					'num_reviews_count' => "ra.reviews_count",
					'num_rating_summary' => "ra.rating_summary"
				]
			);
		return $collection;
	}
	
	public function getAddToCartPostParams(\Magento\Catalog\Model\Product $product)
    {	
        $url = $this->getAddToCartUrl($product);
        return [
            'action' => $url,
            'data' => [
                'product' => $product->getEntityId(),
                \Magento\Framework\App\ActionInterface::PARAM_NAME_URL_ENCODED =>
                    $this->_objectManager->get('\Magento\Framework\Url\Helper\Data')->getEncodedUrl($url),
            ]
        ];
    }
	
	public function getAjaxUrl(){
		return $this->_storeManager->getStore()->getBaseUrl().'listingtabs/index/index';
	}
	
	public function _setSerialize($str)
    {
        $serializer = $this->_objectManager->get('\Magento\Framework\Serialize\Serializer\Json');
		if (!empty($str)) {
			$items = $serializer->serialize($str);
			return $items;
		}
        return true;
    }

    // Get GET request active
    public function _addRequestFilterToCollection($collection){
    	$active_requests = array();

    	if($this->_isAjax()){
    		//$requests = (array) json_decode($this->getRequest()->getParam('get_request'));
    		$requests = $this->getRequest()->getParam('get_request');
    	}else{
    		$requests = $this->getRequest()->getParams();
    	}

		// filter needed request
    	foreach ($requests as $kreq => $vreq) {
    		$attr = $this->getAttributeProperties($kreq);
    		if($attr){
                $frontend_input = $attr->getFrontendInput();

                // check frontend input text, textarea, date, hidden, boolean, multiline, image, multiselect, price, weight, media_image, gallery
                // only filter for text, textarea, boolean, multiselect, select
                switch ($frontend_input) {
                    case 'select':
                    case 'multiselect':
                        // get Source Option Value
                        $source_value = $attr->getSource()->getOptionId($vreq);
                        // filter by source value
                        $collection->addAttributeToFilter($kreq, array('eq' => $vreq));
                        break;

                    case 'text':
                    case 'textarea':
                        // filter by source value
                        $collection->addAttributeToFilter($kreq, array('like' => '%'.$vreq.'%'));
                        break;

                    case 'boolean':
                        // filter by source value
                        $collection->addAttributeToFilter($kreq, array('eq' => $vreq));
                        break;

                    case 'date':
                    case 'hidden':
                    case 'multiline':
                    case 'image':
                    case 'price':
                    case 'weight':
                    case 'media_image':
                    case 'gallery':
                        break;
                }
    		}
    	}

    	return $collection;
    }

    public function _getSorterConfiguration(){
    	$sorter_config = $this->_scopeConfig->getValue('catalog_sorter_configuration/general/json_catalog_sorter_config',\Magento\Store\Model\ScopeInterface::SCOPE_STORE,$this->_storeCode);
		$sorter_config = json_decode($sorter_config);

		return $sorter_config;
    }


    public function _getChildCategoriesByCateId(){
    	// child categories
    	$child = array();
    	$_objectManager = \Magento\Framework\App\ObjectManager::getInstance();

    	// get parent id
    	$category_id = $this->_getConfig('category_tabs') ? $this->_getConfig('category_tabs') : 0;
    	if($category_id){
    		$category = $_objectManager->create('Magento\Catalog\Model\Category')->load($category_id);

    		// get children categories
    		$child = $category->getChildrenCategories();
    	}

    	return $child;
    }
}
