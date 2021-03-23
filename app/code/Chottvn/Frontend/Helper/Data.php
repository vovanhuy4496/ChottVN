<?php

namespace Chottvn\Frontend\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
 
class Data extends AbstractHelper
{
	/**
	* @var \Magento\Framework\App\Config\ScopeConfigInterface
	*/
	protected $_scopeConfig;

    protected $_objectManager;
    protected $_storeManager;
    protected $_categoryRepository;

	/**
	* Recipient email config path
	*/
	const XML_PATH_MARKET_PRODUCT_PRICE_ROUND = 'market/product_price/round_product_price';

	public function __construct(
	        Context $context,
            \Magento\Framework\ObjectManagerInterface $objectManager,
            \Magento\Store\Model\StoreManagerInterface $storeManager,
            \Magento\Catalog\Model\CategoryRepository $categoryRepository,
	        ScopeConfigInterface $scopeConfig
	    ){
		$this->_scopeConfig = $scopeConfig;
        $this->_objectManager = $objectManager;
        $this->_storeManager = $storeManager;
        $this->_categoryRepository = $categoryRepository;
	    parent::__construct($context);
	}

    public  function getCategory($categoryId){
        $category = $this->_categoryRepository->get($categoryId, $this->_storeManager->getStore()->getId());

        return $category;
    }
	 
	public function getPrecisionRoundPrice(){
		$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
		return -1 * abs($this->_scopeConfig->getValue(self::XML_PATH_MARKET_PRODUCT_PRICE_ROUND, $storeScope));
	}
	
	public function countProductsByAttribute($attribute_key, $attribute_value = '', $category_id = 0){
        // product colleciton
        $collection = $this->_objectManager->create('Magento\Catalog\Model\ResourceModel\Product\Collection');

        // query by category
        if($category_id){
            $collection->addCategoriesFilter(['eq' => $category_id]);
        }

		// filter type
        if($attribute_value){
            $attr = $this->getAttributeProperties($attribute_key);
            $source = $attr->getSource();
            $frontend_input = $attr->getFrontendInput();
            // check frontend input text, textarea, date, hidden, boolean, multiline, image, multiselect, price, weight, media_image, gallery
            // only filter for text, textarea, boolean, multiselect, select
            switch ($frontend_input) {
                case 'select':
                case 'multiselect':
                    // get Source Option Value
                    $source_value = $attr->getSource()->getOptionId($attribute_value);
                    // filter by source value
                    $collection->addAttributeToFilter($attribute_key, array('eq' => $source_value));
                    break;

                case 'text':
                case 'textarea':
                    // filter by source value
                    $collection->addAttributeToFilter($attribute_key, array('like' => '%'.$attribute_value.'%'));
                    break;

                case 'boolean':
                    // filter by source value
                    $collection->addAttributeToFilter($attribute_key, array('eq' => $attribute_value));
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
        }else{
            $collection->addFieldToFilter($attribute_key, array('like' => '%%'));
        }
        // echo $collection->getSelect();
        return $collection->count();
	}

	public function getAttributeProperties($attr_key){
        $eavConfig = $this->_objectManager->get('\Magento\Eav\Model\Config');
        $attribute = $eavConfig->getAttribute('catalog_product', $attr_key);

        return $attribute;
    }

    public function getFilterableAttributeByCategoryId($categoryId){
        // connection
        $resource = $this->_objectManager->get(\Magento\Framework\App\ResourceConnection::class);
        $connection = $resource->getConnection();

        $select = $connection->select()->from(['ea' => $connection->getTableName('eav_attribute')], 'ea.attribute_id')
        ->join(['eea' => $connection->getTableName('eav_entity_attribute')], 'ea.attribute_id = eea.attribute_id')
        ->join(['cea' => $connection->getTableName('catalog_eav_attribute')], 'ea.attribute_id = cea.attribute_id')
        ->join(['cpe' => $connection->getTableName('catalog_product_entity')], 'eea.attribute_set_id = cpe.attribute_set_id')
        ->join(['ccp' => $connection->getTableName('catalog_category_product')], 'cpe.entity_id = ccp.product_id')
        ->where('cea.is_filterable = ?', 1)
        ->where('ccp.category_id = ?', $categoryId)
        ->group('ea.attribute_id');

        $attributeIds = $connection->fetchCol($select);

        return $attributeIds;
    }
}
?>