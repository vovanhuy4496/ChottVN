<?php 

namespace Chottvn\Affiliate\Block;

class Newsletter extends \Magento\Customer\Block\Newsletter
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
            'newsletter',
            [
                'label' => __('Newsletter Subscriptions'),
                'title' => __('Newsletter Subscriptions')
            ]
        );
        return parent::_prepareLayout();
    }
   

}