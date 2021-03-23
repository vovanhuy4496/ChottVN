<?php

namespace Chottvn\Address\Controller\Adminhtml\Township;

class Delete extends \Chottvn\Address\Controller\Adminhtml\Address
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Chottvn_Address::township_delete';
    
    /**
     *
     * @return \Magento\Framework\View\Result\PageFactory
     */
    public function execute()
    {
        // check if we know what should be deleted
        $township_id = $this->getRequest()->getParam('township_id');
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($township_id) {
            try {
                // init model and delete
                $township = $this->townshipFactory->create();
                $this->townshipResource->load($township, $township_id);
                $township_name = $township->getDefaultName();
                $this->townshipResource->delete($township);
                $this->messageManager->addSuccessMessage(__('The township %1 has been deleted.', $township_name));
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addErrorMessage($e->getMessage());
                // go back to edit form
                return $resultRedirect->setPath('*/*/edit', ['township_id' => $township_id]);
            }
        }
        // display error message
        $this->messageManager->addErrorMessage(__('Township to delete was not found.'));
        // go to grid
        return $resultRedirect->setPath('*/*/');
    }
}
