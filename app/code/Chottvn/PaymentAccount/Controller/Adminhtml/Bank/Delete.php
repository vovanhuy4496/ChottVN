<?php
/**
 * Copyright © (c) Cho Truc Tuyen 2020 All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Chottvn\PaymentAccount\Controller\Adminhtml\Bank;

class Delete extends \Chottvn\PaymentAccount\Controller\Adminhtml\Bank
{

    /**
     * Delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        // check if we know what should be deleted
        $id = $this->getRequest()->getParam('bank_id');
        if ($id) {
            try {
                // init model and delete
                $model = $this->_objectManager->create(\Chottvn\PaymentAccount\Model\Bank::class);
                $model->load($id);
                $model->delete();
                // display success message
                $this->messageManager->addSuccessMessage(__('You deleted the Bank.'));
                // go to grid
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addErrorMessage($e->getMessage());
                // go back to edit form
                return $resultRedirect->setPath('*/*/edit', ['bank_id' => $id]);
            }
        }
        // display error message
        $this->messageManager->addErrorMessage(__('We can\'t find a Bank to delete.'));
        // go to grid
        return $resultRedirect->setPath('*/*/');
    }
}

