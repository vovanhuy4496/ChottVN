<?php
declare(strict_types=1);

namespace Chottvn\Finance\Controller\Adminhtml\TransactionType;

class Delete extends \Chottvn\Finance\Controller\Adminhtml\TransactionType
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
        $id = $this->getRequest()->getParam('transactiontype_id');
        if ($id) {
            try {
                // init model and delete
                $model = $this->_objectManager->create(\Chottvn\Finance\Model\TransactionType::class);
                $model->load($id);
                $model->delete();
                // display success message
                $this->messageManager->addSuccessMessage(__('You deleted the Transactiontype.'));
                // go to grid
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addErrorMessage($e->getMessage());
                // go back to edit form
                return $resultRedirect->setPath('*/*/edit', ['transactiontype_id' => $id]);
            }
        }
        // display error message
        $this->messageManager->addErrorMessage(__('We can\'t find a Transactiontype to delete.'));
        // go to grid
        return $resultRedirect->setPath('*/*/');
    }
}

