<?php

namespace Chottvn\Brand\Block\Ves\Brand\Block;
use Magento\Customer\Model\Context as CustomerContext;

class BrandList extends \Ves\Brand\Block\BrandList
{
    public function getBrandCollection()
    {
        if(!$this->_collection) {
            $number_item = $this->getConfig('brand_block/number_item');
            $brandGroups = $this->getConfig('brand_block/brand_groups');
            $category_id = $this->getConfig('brand_block/category_ids');
            $store = $this->_storeManager->getStore();
            $collection = $this->_brandCollection->getCollection()
            ->setOrder('position','ASC')
            ->addStoreFilter($store)
            ->addFieldToFilter('status',1);
            $brandGroups = explode(',', $brandGroups);

            $collection->setPageSize($number_item)
                ->setCurPage(1)
                ->setOrder('position','ASC');

            if(is_array($brandGroups) && count($brandGroups)>0)
            {
                $collection->addFieldToFilter('group_id',array('in' => $brandGroups));
            }

            if (!empty($category_id)){
                $catIds = explode(',',$category_id);
                $brandCollection = $collection;
                $brandIds = $brandCollection->getColumnValues('brand_id');
                //var_dump($brandIds);die;

                $productFactory = $this->_brandProductFactory->create();
                $productIds = $productFactory->loadProductIdsByBrandIds($brandIds);

                $proCatIds = [];
                $finalProIds = [];
                if(!empty($productIds)) {
                    foreach ($productIds as $productId) {
                        $product = $this->_productFactory->create()->load($productId);
                        $proCats = $product->getCategoryIds();
                        foreach ($proCats as $proCat) {
                            if (in_array($proCat, $catIds)) {
                                $finalProIds = array_merge($finalProIds, [$productId]);
                                break;
                            }
                        }
                        $finalPro = array_unique($finalProIds);
                    }
                }
                $brandProducts = $this->_brandProductFactory->create();
                $finalBrandIds = $brandProducts->loadBrandIdsByProductIds($brandIds, $finalProIds);
                if(!empty($finalBrandIds)) {
                    $collection->addFieldToFilter('main_table.brand_id', ['in' => $finalBrandIds]);
                }
            }

            // sort by name
            $collection->getSelect()->order('name ASC');

            $this->_collection = $collection;
        }
        return $this->_collection;
    }

    public function getCustomBrandCollection($limit = '', $page = 0)
    {
        if(!$this->_collection) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $item_per_page = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('vesbrand/brand_list_page/item_per_page');

            $number_item = $this->getConfig('brand_block/number_item');
            $brandGroups = $this->getConfig('brand_block/brand_groups');
            $category_id = $this->getConfig('brand_block/category_ids');
            $store = $this->_storeManager->getStore();
            $collection = $this->_brandCollection->getCollection()
            ->setOrder('position','ASC')
            ->addStoreFilter($store)
            ->addFieldToFilter('status',1);
            $brandGroups = explode(',', $brandGroups);

            // if($limit == 'all'){
            //     $collection->setOrder('position','ASC');
            // }else{
            //     $collection->setPageSize($number_item)
            //     ->setCurPage($page)
            //     ->setOrder('position','ASC');
            // }

            if(is_array($brandGroups) && count($brandGroups)>0)
            {
                $collection->addFieldToFilter('group_id',array('in' => $brandGroups));
            }

            if (!empty($category_id)){
                $catIds = explode(',',$category_id);
                $brandCollection = $collection;
                $brandIds = $brandCollection->getColumnValues('brand_id');
                //var_dump($brandIds);die;

                $productFactory = $this->_brandProductFactory->create();
                $productIds = $productFactory->loadProductIdsByBrandIds($brandIds);

                $proCatIds = [];
                $finalProIds = [];
                if(!empty($productIds)) {
                    foreach ($productIds as $productId) {
                        $product = $this->_productFactory->create()->load($productId);
                        $proCats = $product->getCategoryIds();
                        foreach ($proCats as $proCat) {
                            if (in_array($proCat, $catIds)) {
                                $finalProIds = array_merge($finalProIds, [$productId]);
                                break;
                            }
                        }
                        $finalPro = array_unique($finalProIds);
                    }
                }
                $brandProducts = $this->_brandProductFactory->create();
                $finalBrandIds = $brandProducts->loadBrandIdsByProductIds($brandIds, $finalProIds);
                if(!empty($finalBrandIds)) {
                    $collection->addFieldToFilter('main_table.brand_id', ['in' => $finalBrandIds]);
                }
            }

            // sort by name
            $collection->getSelect()->order('name ASC');
            if($limit != 'all'){
                $collection->getSelect()->limit($item_per_page, (int)$item_per_page*(int)$page);
            }
            
            // echo $collection->getSelect();
            $this->_collection = $collection;
        }
        return $this->_collection;
    }
}