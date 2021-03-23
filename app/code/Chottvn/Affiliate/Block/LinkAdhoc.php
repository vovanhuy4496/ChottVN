<?php
/**
 * Copyright © (c) Cho Truc Tuyen 2020 All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Chottvn\Affiliate\Block;

use Magento\Customer\Model\Session as CustomerSession;
use Chottvn\Affiliate\Helper\Account as AccountHelper;

class LinkAdhoc extends \Magento\Framework\View\Element\Template
{
    protected $customerSession;

    protected $accountHelper;

    //private $linkType;

    const LINK_TYPE_LOGIN_FOOTER = 'login_footer';

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context  $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        CustomerSession $customerSession,
        AccountHelper $accountHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_isScopePrivate = true; //$this->setCacheLifetime(0);
        $this->customerSession = $customerSession;
        $this->accountHelper = $accountHelper;
    }

	/**
     * Prepare layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {        
        return parent::_prepareLayout();
    }

    /**
     * Prepare layout
     *
     * @return $this
     */
    public function getCustomer()
    {        
        return $this->customerSession->getCustomer();
    }

    public function isLoggedIn()
    {        
        return $this->customerSession->isLoggedIn();
    }
    public function isAffiliate()
    {        
        if($this->isLoggedIn()){       
            return $this->accountHelper->isAffiliate($this->getCustomer()->getId());
        }
        return false;        
    }

    public function getCustomerId()
    {        
        if ($this->isLoggedIn()){
            return $this->getCustomer()->getId();
        }else{
            return 0;
        }
    }

    public function getParamLinkType(){
       
        return $this->getLinkType();
        //return $this->getData("link_type");
    }


    
}