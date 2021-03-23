<?php

namespace Chottvn\Address\Controller\Adminhtml\Township;

class Edit extends \Chottvn\Address\Controller\Adminhtml\Address
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Chottvn_Address::township_edit';

    /**
     * Edit Township page
     *
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        // Get ID and create model
        $township_id = (int) $this->getRequest()->getParam('township_id');
        $township = $this->townshipFactory->create();
        $township->setData([]);
        // Initial checking
        if ($township_id && $township_id > 0) {
            $this->townshipResource->load($township, $township_id);
            if (!$township->getTownshipId()) {
                $this->messageManager->addErrorMessage(__('This township no longer exists.'));
                /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
            $default_name = $township->getDefaultName();
        }

        $formData = $this->_session->getFormData(true);
        if (!empty($formData)) {
            $township->setData($formData);
        }

        $this->coreRegistry->register('address_township', $township);

        // Build edit form
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_initAction();
        $resultPage->addBreadcrumb(
            $township_id ? __('Edit Township') : __('New Township'),
            $township_id ? __('Edit Township') : __('New Township')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Township Manager'));
        $resultPage->getConfig()->getTitle()->prepend(
            $township_id ? 'Edit: '.$default_name.' ('.$township_id.')' : __('New Township')
        );

        return $resultPage;
    }
}
