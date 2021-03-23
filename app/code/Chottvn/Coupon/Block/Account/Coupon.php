<?php
/**
 * Copyright © (c) chotructuyen.co All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Chottvn\Coupon\Block\Account;


use \Chottvn\Coupon\Helper\Data as CouponHelper;
use Chottvn\Affiliate\Helper\Account as AffiliateAccountHelper;
use Magento\Customer\Model\Session as CustomerSession;

class Coupon extends \Magento\Framework\View\Element\Template
{

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Chottvn\Coupon\Helper\Data
     */
    public $couponHelper;

    /**
     * @var \Chottvn\Affiliate\Helper\Account
     */
    protected $affiliateAccountHelper;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context  $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        CustomerSession $customerSession,
        CouponHelper $couponHelper,
        AffiliateAccountHelper $affiliateAccountHelper,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        $this->couponHelper = $couponHelper;
        $this->affiliateAccountHelper = $affiliateAccountHelper;
        parent::__construct($context, $data);

        
    }

     /**
     * Prepare layout
     *
     * @return $this
     */    
    protected function _prepareLayout()
    {
        $title = __('My Coupons');
        $this->pageConfig->getTitle()->set($title);
        $group = $this->customerSession->getCustomer()->getGroupId();
        $paramtitle = '';
        $array = '';
        if($group == 4){
           $paramtitle = 'affiliate';
           $array =  [
            'label' => __('Affiliate Program'),
            'title' => __('Affiliate Program'),
            'link' => $this->_storeManager->getStore()->getBaseUrl().'/affiliate'
           ];
        }else{
           $paramtitle = 'account';
           $array =  [
            'label' => __('Account Information Customer'),
            'title' => __('Account Information Customer'),
            'link' => $this->_storeManager->getStore()->getBaseUrl().'/customer/account'
           ];
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
                $paramtitle,$array
            )->addCrumb(
                '',
                [
                    'label' => $title,
                    'title' => $title
                ]
            );
        }

        return parent::_prepareLayout();
    }
    


    /**
     * Get Coupons
     *
     * @return <Array>
     */
    public function getCoupons(){
        $phoneNumber = $this->affiliateAccountHelper->getPhoneNumber($this->customerSession->getId());
        return $this->couponHelper->getCoupons($phoneNumber);
    }


    /**
    * @param $info
    * @param $type  [error, warning, info]
    * @return 
    */
    private function writeLog($info, $type = "info") {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/chottvn_coupon.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        switch($type){
            case "error":
                $logger->err($info);  
                break;
            case "warning":
                $logger->notice($info);  
                break;
            case "info":
                $logger->info($info);  
                break;
            default:
                $logger->info($info);  
        }
    }
}

