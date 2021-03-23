<?php
/**
 * Copyright © (c) Cho Truc Tuyen 2020 All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Chottvn\Affiliate\Controller\Account;

class AffiliateContract extends \Magento\Framework\App\Action\Action
{
	protected $_pageFactory;
	 /**
     * @var Session
     */
  protected $session;
  
	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Customer\Model\Session $customerSession,
		\Magento\Framework\View\Result\PageFactory $pageFactory)
	{
		$this->_pageFactory = $pageFactory;
		$this->session = $customerSession;
		return parent::__construct($context);
	}

	public function execute()
	{		
		if ($this->session->isLoggedIn() && $this->session->getCustomer()->getGroupId() == 4) {
			$resultPage = $this->_pageFactory->create();
			$resultPage->getConfig()->getTitle()->set(__("Affiliate Contract"));
			return $resultPage;
		}
	 // @var \Magento\Framework\Controller\Result\Redirect $resultRedirect
	 $resultRedirect = $this->resultRedirectFactory->create();
	 $resultRedirect->setPath('affiliate/account/login/');
	 return $resultRedirect;

	}
}