<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Chottvn\Affiliate\Controller\Adminhtml\Index;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;

class Index extends \Chottvn\Affiliate\Controller\Adminhtml\Index implements HttpGetActionInterface
{
    /**
     * Affiliates list action
     *
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Forward
     */
    public function execute()
    {
        if ($this->getRequest()->getQuery('ajax')) {
            $resultForward = $this->resultForwardFactory->create();
            $resultForward->forward('grid');
            return $resultForward;
        }
        $resultPage = $this->resultPageFactory->create();
        /**
         * Set active menu item
         */
        $resultPage->setActiveMenu('Chottvn_Affiliate::affiliate_manage');
        $resultPage->getConfig()->getTitle()->prepend(__('Affiliates'));

        /**
         * Add breadcrumb item
         */
        $resultPage->addBreadcrumb(__('Affiliates'), __('Affiliates'));
        $resultPage->addBreadcrumb(__('Manage Affiliates'), __('Manage Affiliates'));

        $this->_getSession()->unsCustomerData();
        $this->_getSession()->unsCustomerFormData();

        return $resultPage;
    }
}
