<?php
/**
 * Copyright © (c) Cho Truc Tuyen 2020 All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Chottvn\PaymentAccount\Controller\Adminhtml\CustomerBankAccount;

class Edit extends \Chottvn\PaymentAccount\Controller\Adminhtml\CustomerBankAccount
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Chottvn_PaymentAccount::customerbankaccount_update';

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
        $id = $this->getRequest()->getParam('customerba_id');
        $model = $this->_objectManager->create(\Chottvn\PaymentAccount\Model\CustomerBankAccount::class);
        
        // 2. Initial checking
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This CustomerBankAccount no longer exists.'));
                /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }
        $this->_coreRegistry->register('chottvn_paymentaccount_customerbankaccount', $model);
        
        // 3. Build edit form
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $this->initPage($resultPage)->addBreadcrumb(
            $id ? __('Edit CustomerBankAccount') : __('New CustomerBankAccount'),
            $id ? __('Edit CustomerBankAccount') : __('New CustomerBankAccount')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('CustomerBankAccounts'));
        $resultPage->getConfig()->getTitle()->prepend($model->getId() ? __('Edit CustomerBankAccount %1', $model->getId()) : __('New CustomerBankAccount'));
        return $resultPage;
    }
}

