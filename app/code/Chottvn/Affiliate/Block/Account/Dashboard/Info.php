<?php 

namespace Chottvn\Affiliate\Block\Account\Dashboard;

class Info extends \Magento\Customer\Block\Account\Dashboard\Info
{
   /**
     * Prepare layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customerSession = $objectManager->create("Magento\Customer\Model\Session");
        $groupId = 1;
        if($customerSession->isLoggedIn()){
            $groupId = $customerSession->getCustomer()->getGroupId();
        }
        if($groupId == 4){
            $code = 'affiliate';
            $content = 'Account Information Affiliate';
            $link =  $this->_storeManager->getStore()->getBaseUrl().'customer/account';
        }else{
            $code = 'account';
            $content = 'Account Information Customer';
            $link =  $this->_storeManager->getStore()->getBaseUrl().'customer/account';
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
            )->addCrumb(
                $code,
                [
                    'label' => __($content),
                    'title' => __($content),
                ]
                );
        }
        return parent::_prepareLayout();
    }
}