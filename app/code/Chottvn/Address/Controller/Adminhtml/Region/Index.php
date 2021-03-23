<?php

namespace Chottvn\Address\Controller\Adminhtml\Region;

class Index extends \Chottvn\Address\Controller\Adminhtml\Address
{
    /**
     * @return \Magento\Framework\View\Result\PageFactory
     */
    public function execute()
    {
        $resultPage = $this->_initAction();
        $resultPage->addBreadcrumb('Regions Manager', 'Regions Manager');
        $resultPage->getConfig()->getTitle()->prepend(__('Customers'));
        $resultPage->getConfig()->getTitle()->prepend('Regions Manager');
        return $resultPage;
    }
}
