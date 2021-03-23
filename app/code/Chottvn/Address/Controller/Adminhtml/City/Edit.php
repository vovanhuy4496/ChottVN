<?php

namespace Chottvn\Address\Controller\Adminhtml\City;

class Edit extends \Chottvn\Address\Controller\Adminhtml\Address
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Chottvn_Address::city_edit';

    /**
     * Edit City page
     *
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        // Get ID and create model
        $city_id = (int) $this->getRequest()->getParam('city_id');
        $city = $this->cityFactory->create();
        $city->setData([]);
        // Initial checking
        if ($city_id && $city_id > 0) {
            $this->cityResource->load($city, $city_id);
            if (!$city->getCityId()) {
                $this->messageManager->addErrorMessage(__('This city no longer exists.'));
                /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
            $default_name = $city->getDefaultName();
        }

        $formData = $this->_session->getFormData(true);
        if (!empty($formData)) {
            $city->setData($formData);
        }

        $this->coreRegistry->register('address_city', $city);

        // Build edit form
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_initAction();
        $resultPage->addBreadcrumb(
            $city_id ? __('Edit City') : __('New City'),
            $city_id ? __('Edit City') : __('New City')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Cities Manager'));
        $resultPage->getConfig()->getTitle()->prepend(
            $city_id ? 'Edit: '.$default_name.' ('.$city_id.')' : __('New City')
        );

        return $resultPage;
    }
}
