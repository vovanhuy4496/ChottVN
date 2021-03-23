<?php
namespace Chottvn\Brand\Block;

class BrandCategories extends \Magento\Framework\View\Element\Template
{
  /**
  * Core registry
  *
  * @var \Magento\Framework\Registry
  */
  protected $_coreRegistry = null;

  /**
  * Catalog layer
  *
  * @var \Magento\Catalog\Model\Layer
  */
  protected $_catalogLayer;

  /**
  * @var \Magento\Catalog\Helper\Category
  */
  protected $_brandHelper;

  protected $_groupModel;

  protected $_resourceConnection;
  protected $_storeManager;
  protected $_categoryRepository;
  protected $_categoryCollectionFactory;

  /**
  * @param \Magento\Framework\View\Element\Template\Context $context       
  * @param \Magento\Catalog\Model\Layer\Resolver            $layerResolver 
  * @param \Magento\Framework\Registry                      $registry      
  * @param \Ves\Brand\Helper\Data                           $brandHelper   
  * @param \Ves\Brand\Model\Group                           $groupModel    
  * @param array                                            $data          
  */
  public function __construct(
    \Magento\Framework\View\Element\Template\Context $context,
    \Magento\Catalog\Model\Layer\Resolver $layerResolver,
    \Magento\Framework\Registry $registry,
    \Ves\Brand\Helper\Data $brandHelper,
    \Ves\Brand\Model\Group $groupModel,
    \Magento\Framework\App\ResourceConnection $resourceConnection,
    \Magento\Store\Model\StoreManagerInterface $storeManager,
    \Magento\Catalog\Model\CategoryRepository $categoryRepository,
    \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
    array $data = []
  ) {
    $this->_brandHelper = $brandHelper;
    $this->_catalogLayer = $layerResolver->get();
    $this->_coreRegistry = $registry;
    $this->_groupModel = $groupModel;
    $this->_resourceConnection = $resourceConnection;
    $this->_storeManager = $storeManager;
    $this->_categoryRepository = $categoryRepository;
    $this->_categoryCollectionFactory = $categoryCollectionFactory;
    parent::__construct($context, $data);
  }

  /**
   * @return string
   */
  public function getCategoriesByBrand($brand_id, $primary_category = 1, $level = 0)
  {
    $result = array();

    // connection
    $connection = $this->_resourceConnection->getConnection();

    // query
    $query = "SELECT DISTINCT(ccpis1.category_id) 
              FROM catalog_category_product_index_store1 AS ccpis1
              LEFT JOIN catalog_product_entity_int AS cpei
              ON cpei.entity_id = ccpis1.product_id
              LEFT JOIN eav_attribute AS ea
              on ea.attribute_id = cpei.attribute_id
              WHERE ea.attribute_code = 'product_brand' AND cpei.value = '".$brand_id."'";

    // get categories
    $categories = $connection->fetchAll($query);

    $cate = array();
    foreach ($categories as $cate_id) {
      $cate[] = $cate_id['category_id'];
      // $category = $this->getCategory($cate_id['category_id']);
      // if($category->getData('chottvn_is_category_nh_product_attribute')){
      //   $cate = [
      //           'name' => $category->getName(),
      //           'url' => $category->getUrl(),
      //           'is_primary' => $category->getData('chottvn_is_category_nh_product_attribute')
      //         ];
      //   $result[] = $cate;
      // }
    }

    $categoryCollection = $this->getPrimaryCategoryCollection($cate, $primary_category, $level);

    foreach ($categoryCollection as $category) {
      $cate_tmp = [
              'id' => $category->getId(),
              'name' => $category->getName(),
              'parent_id' => $category->getParentCategory()->getId(),
              'url' => $category->getUrl(),
              'is_primary' => $category->getData('chottvn_is_category_nh_product_attribute')
            ];
      $result[] = $cate_tmp;
    }

    return $this->buildTree($result);
  }

  function buildTree(array $elements, $parentId = 2) {
    $branch = array();
    foreach ($elements as $element) {
      if ($element['parent_id'] == $parentId) {
        $children = $this->buildTree($elements, $element['id']);
        if ($children) {
            $element['children'] = $children;
        }
        $branch[] = $element;
      }
    }

    return $branch;
  }


  public  function getCategory($cate_id)
  {
  $category = $this->_categoryRepository->get($cate_id, $this->_storeManager->getStore()->getId());

  return $category;
  }

  public function getCurrentBrand()
  {
    $brand = $this->_coreRegistry->registry('current_brand');
    if ($brand) {
        $this->setData('current_brand', $brand);
    }
    return $brand;
  }

  public function getPrimaryCategoryCollection($cateids = array(), $primary_category = 1, $level = 0) {
    $collection = $this->_categoryCollectionFactory->create();

    if(!empty($cateids)){
        $collection->addFieldToFilter('entity_id', array(
                                        'in' => $cateids)
                                     );
    }

    if($level){
      $collection->addLevelFilter($level);
    }

    $collection->addAttributeToSelect('*');
    $collection->addIsActiveFilter();
    $collection->getSelect()->order('ABS(chottvn_category_position_attribute)');
    $collection->addAttributeToSort('chottvn_category_position_attribute');

    if($primary_category){
      $collection->addAttributeToFilter('chottvn_is_category_nh_product_attribute', ['eq' => 1]);
    }

    return $collection;
  }
}