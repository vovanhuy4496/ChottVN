<?php
declare(strict_types=1);

namespace Chottvn\Finance\Controller\Adminhtml\TransactionType;

class Edit extends \Chottvn\Finance\Controller\Adminhtml\TransactionType
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Chottvn_Finance::transactiontype_update';

    protected $resultPageFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context, $coreRegistry);
    }

    /**
     * Edit action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        // 1. Get ID and create model
        $id = $this->getRequest()->getParam('transactiontype_id');
        $model = $this->_objectManager->create(\Chottvn\Finance\Model\TransactionType::class);
        
        // 2. Initial checking
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This Transactiontype no longer exists.'));
                /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }
        $this->_coreRegistry->register('chottvn_finance_transactiontype', $model);
        
        // 3. Build edit form
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $this->initPage($resultPage)->addBreadcrumb(
            $id ? __('Edit Transactiontype') : __('New Transactiontype'),
            $id ? __('Edit Transactiontype') : __('New Transactiontype')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Transactiontypes'));
        $resultPage->getConfig()->getTitle()->prepend($model->getId() ? __('Edit Transactiontype %1', $model->getId()) : __('New Transactiontype'));
        return $resultPage;
    }
}

