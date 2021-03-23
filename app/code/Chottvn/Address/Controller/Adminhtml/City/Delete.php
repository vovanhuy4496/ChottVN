<?php

namespace Chottvn\Address\Controller\Adminhtml\City;

class Delete extends \Chottvn\Address\Controller\Adminhtml\Address
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Chottvn_Address::city_delete';

    /**
     *
     * @return \Magento\Framework\View\Result\PageFactory
     */
    public function execute()
    {
        // check if we know what should be deleted
        $city_id = $this->getRequest()->getParam('city_id');
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($city_id) {
            try {
                // init model and delete
                $city = $this->cityFactory->create();
                $this->cityResource->load($city, $city_id);
                $city_name = $city->getDefaultName();
                $this->cityResource->delete($city);
                $this->messageManager->addSuccessMessage(__('The city %1 has been deleted.', $city_name));
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addErrorMessage($e->getMessage());
                // go back to edit form
                return $resultRedirect->setPath('*/*/edit', ['city_id' => $city_id]);
            }
        }
        // display error message
        $this->messageManager->addErrorMessage(__('City to delete was not found.'));
        // go to grid
        return $resultRedirect->setPath('*/*/');
    }
}
