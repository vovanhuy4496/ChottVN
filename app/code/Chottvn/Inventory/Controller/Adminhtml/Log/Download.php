<?php
/**
 * Copyright © © 2020 chotructuyen.co All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Chottvn\Inventory\Controller\Adminhtml\Log;

class Download extends \Chottvn\Inventory\Controller\Adminhtml\Log
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Chottvn_Inventory::log_view';

    protected $resultPageFactory;

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    private $fileFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory
    ) {        
        parent::__construct($context, $coreRegistry);
        $this->resultPageFactory = $resultPageFactory;
        $this->fileFactory = $fileFactory;
    }

    /**
     * Edit action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try{
            // 1. Get ID and create model
            $id = $this->getRequest()->getParam('log_id');
            $logModel = $this->_objectManager->create(\Chottvn\Inventory\Model\Log::class);        
            $logModel->load($id);
            if (!$logModel->getId()) {
                $this->messageManager->addErrorMessage(__('This log is no longer exists.'));
                /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }else{
                $fileName = $logModel->getFileName();
                $logType  = $logModel->getLogType();
                $filePathSub = 'chottvn_inventory/'.$logType.'/' . $fileName;

                $validator = new \Zend_Validate_File_Exists();
                $validator->addDirectory(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR);               
                if($validator->isValid($filePathSub)){
                    $this->fileFactory->create(
                        $fileName,
                        [
                            'type' => "filename",
                            'value' => $filePathSub,
                            'rm' => false,
                        ],
                        \Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR,
                        'application/octet-stream'
                    );
                } else {
                    $this->messageManager->addErrorMessage(__('This file is no longer exists.'));
                    /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                    $resultRedirect = $this->resultRedirectFactory->create();
                    return $resultRedirect->setPath('*/*/');
                    }                
            }        
        }catch(\Exception $e){
            $this->messageManager->addErrorMessage(__($e));
            $resultRedirect = $this->resultRedirectFactory->create();
                    return $resultRedirect->setPath('*/*/');
        }
        
    }
}

