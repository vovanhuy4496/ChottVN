<?php
/**
 * Venustheme
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Venustheme.com license that is
 * available through the world-wide-web at this URL:
 * http://www.venustheme.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category   Venustheme
 * @package    Ves_Brand
 * @copyright  Copyright (c) 2014 Venustheme (http://www.venustheme.com/)
 * @license    http://www.venustheme.com/LICENSE-1.0.html
 */
namespace Ves\Brand\Helper;

class ProductBrand extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Group Collection
     */
    protected $_groupCollection;
    /**
     * Collection
     */
    protected $_brandCollection;

    /** @var \Magento\Store\Model\StoreManagerInterface */
    protected $_storeManager;

    /**
     * Brand config node per website
     *
     * @var array
     */
    protected $_config = [];

    /**
     * Template filter factory
     *
     * @var \Magento\Catalog\Model\Template\Filter\Factory
     */
    protected $_templateFilterFactory;

    /**
     * @var \Magento\Cms\Model\Template\FilterProvider
     */
    protected $_filterProvider;

    protected $_request;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Ves\Brand\Model\Group $groupCollection,
        \Ves\Brand\Model\Brand $brandCollection,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        \Magento\Framework\App\ResourceConnection $resource
        ) {
        parent::__construct($context);
        $this->_filterProvider = $filterProvider;
        $this->_storeManager = $storeManager;
        $this->_groupCollection = $groupCollection;
        $this->_brandCollection = $brandCollection;
        $this->_request = $context->getRequest();
        $this->_resource = $resource;
    }    

    public function getBrandsByProduct($product) {
        $connection = $this->_resource->getConnection();
        $table_name = $this->_resource->getTableName('ves_brand_product');
        $brandIds = $connection->fetchCol(" SELECT brand_id FROM ".$table_name." WHERE product_id = ".$product->getId());
        // $this->writeLog($brandIds);
        // $this->writeLog(count($brandIds));
        // $this->writeLog(empty($brandIds));
        if($brandIds && count($brandIds) > 0 && !empty($brandIds)) {
            $collection = $this->_brandCollection->getCollection()
                ->setOrder('position','ASC')
                ->addFieldToFilter('status',1);
            $collection->getSelect()->where('brand_id IN (?)', $brandIds);
            return $collection;
        }
        return null;
    }

    public function getFirstBrandByProduct($product) {
        $brands = $this->getBrandsByProduct($product);
        if (!empty($brands)) {
            return $brands->getFirstItem();
        } else {
            return null;
        }
    }
    public function getBrandsByOrder($orderItem) {
        $brandIds = $orderItem->getData('product_brand_id');
        if($brandIds && !empty($brandIds)) {
            $collection = $this->_brandCollection->getCollection()
                ->setOrder('position','ASC')
                ->addFieldToFilter('status',1);
            $collection->getSelect()->where('brand_id IN (?)', $brandIds);
            return $collection;
        }
        return null;
    }
    public function getBrandById($brand_id) {
        if($brand_id) {
            $collection = $this->_brandCollection->getCollection()
                ->addFieldToFilter('status',1);
            $collection->getSelect()->where('brand_id='.(int)$brand_id);
            return $collection->getFirstItem();
        }
        return null;
    }
    public function getFirstBrandFromOrderItem($orderItem) {
        $brands = $this->getBrandsByOrder($orderItem);
        if (!empty($brands)) {
            return $brands->getFirstItem();
        } else {
            return null;
        }
    }
    /**
	* @param $info
	* @param $type  [error, warning, info]
	* @return 
	*/
	private function writeLog($info, $type = "info") {
		$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/get_first_brand_product.log');
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