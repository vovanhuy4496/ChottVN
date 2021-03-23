<?php

namespace Chottvn\Address\Controller\Adminhtml\Region;

class Edit extends \Chottvn\Address\Controller\Adminhtml\Address
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Chottvn_Address::region_edit';

    /**
     * Edit Region page
     *
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        // Get ID and create model
        $region_id = (int) $this->getRequest()->getParam('region_id');
        $region = $this->regionFactory->create();
        $region->setData([]);
        // Initial checking
        if ($region_id && $region_id > 0) {
            $this->regionResource->load($region, $region_id);
            if (!$region->getRegionId()) {
                $this->messageManager->addErrorMessage(__('This region no longer exists.'));
                /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
            $default_name = $region->getDefaultName();
        }

        $formData = $this->_session->getFormData(true);
        if (!empty($formData)) {
            $region->setData($formData);
        }

        $this->coreRegistry->register('address_region', $region);

        // Build edit form
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_initAction();
        $resultPage->addBreadcrumb(
            $region_id ? __('Edit Region') : __('New Region'),
            $region_id ? __('Edit Region') : __('New Region')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Regions Manager'));
        $resultPage->getConfig()->getTitle()->prepend(
            $region_id ? 'Edit: '.$default_name.' ('.$region_id.')' : __('New Region')
        );

        return $resultPage;
    }
}
