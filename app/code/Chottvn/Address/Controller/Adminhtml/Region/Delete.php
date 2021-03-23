<?php

namespace Chottvn\Address\Controller\Adminhtml\Region;

class Delete extends \Chottvn\Address\Controller\Adminhtml\Address
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Chottvn_Address::region_delete';
    /**
     *
     * @return \Magento\Framework\View\Result\PageFactory
     */
    public function execute()
    {
        // check if we know what should be deleted
        $region_id = $this->getRequest()->getParam('region_id');
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($region_id) {
            try {
                // init model and delete
                $region = $this->regionFactory->create();
                $this->regionResource->load($region, $region_id);
                $region_name = $region->getDefaultName();
                $this->regionResource->delete($region);
                $this->messageManager->addSuccessMessage(__('The region %1 has been deleted.', $region_name));
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addErrorMessage($e->getMessage());
                // go back to edit form
                return $resultRedirect->setPath('*/*/edit', ['region_id' => $region_id]);
            }
        }
        // display error message
        $this->messageManager->addErrorMessage(__('Region to delete was not found.'));
        // go to grid
        return $resultRedirect->setPath('*/*/');
    }
}
