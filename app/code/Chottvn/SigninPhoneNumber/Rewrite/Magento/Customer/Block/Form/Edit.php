<?php

/**
 * A Magento 2 module named Chottvn/SigninPhoneNumber
 * Copyright (C) 2020 Chottvn
 *
 * This file included in Chottvn/SigninWithPhoneNumber is licensed under OSL 3.0
 *
 * http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * Please see LICENSE.txt for the full text of the OSL 3.0 license
 */

namespace Chottvn\SigninPhoneNumber\Rewrite\Magento\Customer\Block\Form;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Customer\Model\Session;
use Magento\Newsletter\Model\SubscriberFactory;
use Chottvn\SigninPhoneNumber\Setup\InstallData;
use Chottvn\SigninPhoneNumber\Helper\Data as HelperData;

/**
 * Customer edit form block
 *
 * @api
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 * @since 100.0.2
 */
class Edit extends \Magento\Customer\Block\Form\Edit
{
    /**
     * @var \Chottvn\SigninPhoneNumber\Helper\Data
     */
    private $helperData;
    protected $_coreSession;
    /**
     * @param Context $context
     * @param Session $customerSession
     * @param SubscriberFactory $subscriberFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param AccountManagementInterface $customerAccountManagement
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        SubscriberFactory $subscriberFactory,
        CustomerRepositoryInterface $customerRepository,
        AccountManagementInterface $customerAccountManagement,
        HelperData $helperData,
        \Magento\Framework\Session\SessionManagerInterface $coreSession,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $customerSession,
            $subscriberFactory,
            $customerRepository,
            $customerAccountManagement,
            $data
        );
        $this->_coreSession = $coreSession;
        $this->helperData = $helperData;
    }
    public function _prepareLayout()
    {

        $this->pageConfig->getTitle()->set(__('Loyalty Program'));
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
            )
            ->addCrumb(
                'accountinformation',
                [
                    'label' => __('Infor Account Affiliate'),
                    'title' => __('Infor Account Affiliate')
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
                )
                ->addCrumb(
                    'accountinformation',
                    [
                        'label' => __('Account Customer'),
                        'title' => __('Account Customer')
                    ]
                );
        }
       
        return parent::_prepareLayout();
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->helperData->isActive();
    }

    /**
     * Get customer custom phone number attribute as value.
     *
     * @return string Customer phone number value.
     */
    public function getPhoneNumber()
    {
        $phoneAttribute = $this->getCustomer()
            ->getCustomAttribute(InstallData::PHONE_NUMBER);
        return $phoneAttribute ? (string) $phoneAttribute->getValue() : '';
    }

    public function getMySession(){
        $this->_coreSession->start();
        return $this->_coreSession->getMessage();
    }
    
    public function unMySession(){
        $this->_coreSession->start();
        return $this->_coreSession->unsMessage();
    }
}
