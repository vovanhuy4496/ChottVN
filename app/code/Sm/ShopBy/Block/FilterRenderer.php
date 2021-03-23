<?php
/**
 *
 * SM Shop By - Version 2.0.0
 * Copyright (c) 2017 YouTech Company. All Rights Reserved.
 * @license - Copyrighted Commercial Software
 * Author: YouTech Company
 * Websites: http://www.magentech.com
 */
 
namespace Sm\ShopBy\Block;

use Magento\Catalog\Model\Layer\Filter\FilterInterface;

class FilterRenderer extends \Magento\LayeredNavigation\Block\Navigation\FilterRenderer
{
	public function render(FilterInterface $filter)
    {
    	// get query
    	$query = trim($this->getRequest()->getParam('q'));
    	switch ($filter->getRequestVar()) {
    		case 'product_brand':
    			$filter_items = $this->getSortItems($filter->getItems());
    			break;
    		
    		default:
    			$filter_items = $filter->getItems();
    			break;
    	}
    	
    	if($query){
    		foreach ($filter_items as $filter_item) {
	    		// value filter
	    		$value_filter = $filter_item->getValueString();
	    		// filter info
	    		$filter_info = $filter_item->getFilter();
	    		$request_filter = $filter_info->getRequestVar();
	    		$filter_item->setData('count',$this->getCountProductCollection($request_filter, $value_filter));
	    	}
    	}
    	
        $this->assign('filterItems', $filter_items);
		$this->assign('filter' , $filter);
        $html = $this->_toHtml();
        $this->assign('filterItems', []);
        return $html;
    }

    protected function _sortItems($a,$b) {
        return strnatcmp($a->getLabel(), $b->getLabel());
    }

    public function getSortItems($items)
    {
        usort($items, array($this, '_sortItems'));

        return $items;
    }

    public function getCountProductCollection($request_filter, $value_filter){
    	$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    	$collection = $objectManager->create(\Magento\Catalog\Model\ResourceModel\Product\Collection::class);
    	// get select
    	$collection->addAttributeToSelect('entity_id');

		// 1. get query
		$query = trim($this->getRequest()->getParam('q'));
		if($query){
			$this->_getQueryCollection($collection,$query);
		}

    	// 2. request category
		$category_req = $this->getRequest()->getParam('cat') ? $this->getRequest()->getParam('cat'):'';
		if($category_req != ''){
			$collection->addCategoriesFilter(['eq' => $category_req]);
		}

		// add from filter
		switch ($request_filter) {
			case 'cat':
				$collection->addCategoriesFilter(['eq' => $value_filter]);
				break;
			
			default:
				$collection->addFieldToFilter($request_filter, array('eq' => $value_filter));	
				break;
		}

		// request
    	$requests = $this->getRequest()->getParams();
    	unset($requests['config']);
    	unset($requests['type_ajax']);
    	unset($requests['ajax']);
    	unset($requests['q']);
    	unset($requests['cat']);
    	unset($requests['order_by']);

    	foreach ($requests as $key_req => $value_req) {
    		$collection->addFieldToFilter($key_req, array('eq' => $value_req));	
    	}

		// echo $collection->getSelect();
		return $collection->count();
    }

    // get query collection from catalogsearch_fulltext_scope<store_id>
	private function _getQueryCollection(& $collection,$query) {
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$resource = $objectManager->create(\Magento\Framework\App\ResourceConnection::class);
		$storeManager = \Magento\Framework\App\ObjectManager::getInstance()->get('\Magento\Store\Model\StoreManagerInterface');

		// store id
		$storeId = (int)$storeManager->getStore()->getId();

		// connection
		$connection  = $resource->getConnection();
		$table_search_fulltext = trim('catalogsearch_fulltext_scope'.$storeId);
		$select = $connection
			->select()
			->from($connection->getTableName($resource->getTableName($table_search_fulltext)), ['*'])
			->where("data_simple_index like '%".$this->convertName($query)."%'")
			->group('entity_id');
		$collection->getSelect()
			->joinInner(['qc' => $select],
				'qc.entity_id = e.entity_id');
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
    
    public function getPriceRange($filter){
    	$return = [];
    	if($filter->getName()){
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$category = $objectManager->get('Magento\Framework\Registry')->registry('current_category');
			$currentCategory = $objectManager->get('Magento\Framework\Registry')->registry('current_category_filter');
			$category = !empty($category) ? $category : $currentCategory;
			if (!empty($category)) {
				$categoryFactory =  $objectManager->get('Magento\Catalog\Model\CategoryFactory');
				$categoryLoad = $categoryFactory->create()->load($category->getId());
				$collection = $categoryLoad->getProductCollection()->addAttributeToSelect('*')->addMinimalPrice()->addFinalPrice();
				$storeManager = $objectManager->get('Magento\Store\Model\StoreManagerInterface');
				$currencyCode = $storeManager->getStore()->getCurrentCurrencyCode();
				$currency = $objectManager->create('Magento\Directory\Model\CurrencyFactory')->create()->load($currencyCode);
				$currencySymbol = !empty($currency->getCurrencySymbol()) ? $currency->getCurrencySymbol() : $currencyCode;
				$currencyRate = $storeManager->getStore()->getCurrentCurrencyRate();
				$priceArr = $filter->getResource()->loadPrices(10000000000);
				$return['rate'] = $currencyRate;
				$return['min_standard'] = $collection->getMinPrice();
				$return['max_standard'] = $collection->getMaxPrice();
				$return['min_value'] = $return['min_standard'];
				$return['max_value'] = $return['max_standard'];
				$requestPrice = $this->getRequest()->getParam('price');
				if (!empty($requestPrice)){
					$tmp = explode('-', $requestPrice);
					$return['min_value'] = (isset($tmp[0]) && !empty($tmp[0])) ? $tmp[0] :  $return['min_standard'];
					$return['min_value'] = round($return['min_value']*$return['rate'], 2) <  $return['min_standard'] ?   $return['min_standard'] : round($return['min_value']*$return['rate'], 2); 
					$return['max_value'] = (isset($tmp[1]) && !empty($tmp[1])) ? $tmp[1] - 0.01 :  $return['max_standard'];
					$return['max_value'] = round($return['max_value']*$return['rate'], 2) > $return['max_standard'] ?   $return['max_standard'] : round($return['max_value']*$return['rate'], 2); 
				}
				$return['currency_symbol'] = $currencySymbol;
			}
    	}
    	return $return;
    }
    
    public function getFilterUrl($filter){
    		$query = ['price'=> ''];
    	 return $this->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true, '_query' => $query]);
    }
}