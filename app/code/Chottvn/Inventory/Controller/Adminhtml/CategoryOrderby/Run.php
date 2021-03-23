<?php

/**
 * Copyright © © 2020 chotructuyen.co All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Chottvn\Inventory\Controller\Adminhtml\CategoryOrderby;

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
    protected $categoryFactory;

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
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
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
        $this->categoryFactory = $categoryFactory;
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
            $orderBy = $this->getRequest()->getParam('orderby');

            if($orderBy){
                $parserOrderBy = json_decode($orderBy);

                foreach ($parserOrderBy as $order) {
                    // get category id
                    $categoryId = $this->getCategoryIdByRequestPath($order->key);
                    // update data order by attribute
                    if($categoryId){
                        $this->updateOrderByAttrToCategory($categoryId, $order->orderby);
                    }
                }
            }else{
                // input message
                $this->messageManager->addError(__('Please input Order By for Category.'));
                // return data
                return $resultRedirect->setPath(
                    '*/*/script',
                    ['_current' => true]
                );
            }

            // input message
            $this->messageManager->addSuccess(__('Order By in Category is updated.'));
            // return data
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

    public function updateOrderByAttrToCategory($categoryId, $orderby){
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $category = $objectManager->create('Magento\Catalog\Model\CategoryRepository')->get($categoryId);
        
        // saving data
        $category->setData('chottvn_orderby_attribute',trim($orderby));
        $category->save();
    }

    public function getCategoryIdByUrlKey($url_key){
        $categories = $this->categoryFactory->create()
            ->getCollection()
            ->addAttributeToFilter('url_key',$url_key)
            ->addAttributeToSelect(['entity_id']);
        
        if($categories->count() > 0){
            return $categories->getFirstItem()->getEntityId();
        }else{
            return 0;
        }
    }

    public function getCategoryIdByRequestPath($request_path){
        $categories = $this->categoryFactory->create()
            ->getCollection()
            ->addNameToResult()
            ->addUrlRewriteToResult()
            ->addAttributeToSelect(['entity_id']);

        $categories->getSelect()->where('request_path = ?', $request_path.'.html');

        if($categories->count() > 0){
            return $categories->getFirstItem()->getEntityId();
        }else{
            return 0;
        }
    }
}
