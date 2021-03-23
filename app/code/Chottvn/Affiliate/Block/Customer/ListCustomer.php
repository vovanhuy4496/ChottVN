<?php 

namespace Chottvn\Affiliate\Block\Customer;

class ListCustomer extends \Magento\Review\Block\Customer\ListCustomer
{
     /**
     * Initializes toolbar
     *
     * @return \Magento\Framework\View\Element\AbstractBlock
     */
    protected function _prepareLayout()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customerSession = $objectManager->create("Magento\Customer\Model\Session");
        $groupId = 1;
        if($customerSession->isLoggedIn()){
            $groupId = $customerSession->getCustomer()->getGroupId();
        }
         // add Home breadcrumb
         $breadcrumbs = $this->getLayout()->getBlock('breadcrumbs');
         if ($breadcrumbs) {
             $breadcrumbs->addCrumb(
                 'home',
                 [
                     'label' => __('Home'),
                     'title' => __('Go to Home Page'),
                     'link' => $this->_storeManager->getStore()->getBaseUrl()
                 ]
                 );
         }
        if($groupId == 4){
            $breadcrumbs->addCrumb(
                'affiliate',
                [
                    'label' => __('Account Information Affiliate'),
                    'title' => __('Account Information Affiliate'),
                    'link' => $this->_storeManager->getStore()->getBaseUrl().'customer/account'
                ]
            );
        }else{
            $breadcrumbs->addCrumb(
                'account',
                [
                    'label' => __('Account Information Customer'),
                    'title' => __('Account Information Customer'),
                    'link' =>  $this->_storeManager->getStore()->getBaseUrl().'customer/account'
                ]
                );
        }
        $breadcrumbs->addCrumb(
            'review',
            [
                'label' => __('My Product Reviews'),
                'title' => __('My Product Reviews')
            ]
        );
        return parent::_prepareLayout();
    }

}