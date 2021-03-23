<?php

/**
 * Copyright © © 2020 chotructuyen.co All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Chottvn\Inventory\Controller\Adminhtml\CategoryPosition;

class Run extends \Magento\Backend\App\Action 
{

    protected $resultPageFactory;
    protected $fileFactory;
    protected $csvProcessor;
    protected $directoryList;
    protected $logRepository;
    protected $eavConfig;
    protected $categoryCollectionFactory;
    protected $categoryRepository;
    protected $getConnection;
    protected $categoryProductTable;

    /**
     * Notifier Pool
     *
     * @var NotifierPool
     */
    protected $messageManager;


    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context  $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\File\Csv $csvProcessor,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Chottvn\Inventory\Api\LogRepositoryInterface $logRepository,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magento\Catalog\Model\CategoryRepository $categoryRepository,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->fileFactory = $fileFactory;
        $this->csvProcessor = $csvProcessor;
        $this->directoryList = $directoryList;
        $this->logRepository = $logRepository;
        $this->eavConfig = $eavConfig;
        $this->messageManager = $messageManager;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->categoryRepository = $categoryRepository;
        $this->getConnection = $resourceConnection->getConnection();
        $this->categoryProductTable = $resourceConnection->getTableName('catalog_category_product');
        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $formKeyIsValid = $this->_formKeyValidator->validate($this->getRequest());
        $isPost = $this->getRequest()->isPost();
        if (!$formKeyIsValid || !$isPost) {
            $this->messageManager->addError(__('Bad Request'));
            return $resultRedirect->setPath(
                '*/*/new',
                ['_current' => true]
            );
        }
        try{
            // initialize
            $all_childrens = array();
            $arr_categories = array(2);
            // get all category ids
            $categories = $this->getChildrenAllCategories($arr_categories,$all_childrens);
            // echo '<pre>';print_r($categories);echo '</pre>';exit;
            $position = 1;
            foreach ($categories as $category_id) {
                // update custom position
                $this->updatePositionToCategory($category_id,$position);
                // update table catalog_category_product
                $this->updatePositionProductsByCategoryId($category_id, $position);
                $position++;
            }

            $this->messageManager->addSuccess(__('Position in Product/Category is updated. Please wait for reindex to run in 5p and check again.'));
            return $resultRedirect->setPath(
                '*/*/script',
                ['_current' => true]
            );
        }
        catch(\Exception $e){
            $this->messageManager->addError($e->getMessage());
            return $resultRedirect->setPath(
                '*/*/new',
                ['_current' => true]
            );
        }
    }

    public function updatePositionToCategory($categoryId, $position){
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $category = $objectManager->create('Magento\Catalog\Model\CategoryRepository')->get($categoryId);
        
        // saving data
        $category->setData('chottvn_category_position_attribute',(int)$position);
        $category->save();
    }

    public function updatePositionProductsByCategoryId($categoryId, $position){
        $adapter = $this->getConnection;

        if($categoryId && $position){
            // where category id
            $where = array(
                'category_id = ?' => (int) $categoryId
            );
            // update to position
            $bind = array('position' => (int) $position);

            // update sql data
            $adapter->update($this->categoryProductTable, $bind, $where);
        }
    }

    public function getChildrenAllCategories($categories = array(),$all_childrens = array()){
        foreach ($categories as $cate) {
            // add id to array
            $all_childrens[] = $cate;

            // get list category by current category
            $tmp_child_cates = $this->getChildrenCategories($cate);

            // check count > 0
            if($tmp_child_cates->count() > 0){
                $child_cates = array();
                foreach ($tmp_child_cates as $child) {
                    $child_cates[] = $child->getId();
                }
                // de uy lay du lieu tiep theo
                $all_childrens = $this->getChildrenAllCategories($child_cates,$all_childrens);
            }
        }

        return $all_childrens;
    }

    public function getChildrenCategories($categoryId){
        $category = $this->categoryRepository->get($categoryId);

        return $category->getChildrenCategories();
    }

    public function getCategoryCollection($isActive = true, $level = false, $sortBy = false, $pageSize = false, $cateids = array()) {
        $collection = $this->categoryCollectionFactory->create();

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
}
