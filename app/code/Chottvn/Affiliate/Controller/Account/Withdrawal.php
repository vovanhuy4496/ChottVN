<?php

namespace Chottvn\Affiliate\Controller\Account;

class Withdrawal extends \Magento\Framework\App\Action\Action
{
	protected $_pageFactory;
	/**
	 * @var \Magento\Framework\App\Config\ScopeConfigInterface
	 */
  	protected $scopeConfig;

	/**
	 * Recipient email config path
	 */
	const PATH_WITHDRAWAL_REWARD_AMOUNT_MIN = 'configuration/general/withdrawal_reward_amount_min';

	/**
	 * @var Session
	 */
	protected $session;
	
	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Customer\Model\Session $customerSession,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Magento\Framework\View\Result\PageFactory $pageFactory)
	{
		$this->_pageFactory = $pageFactory;
		$this->session = $customerSession;
		$this->scopeConfig = $scopeConfig;
		return parent::__construct($context);
	}

	public function execute()
	{
		$withdrawalRewardAmountMin = $this->scopeConfig->getValue(self::PATH_WITHDRAWAL_REWARD_AMOUNT_MIN, \Magento\Store\Model\ScopeInterface::SCOPE_STORE) ? (int)$this->scopeConfig->getValue(self::PATH_WITHDRAWAL_REWARD_AMOUNT_MIN, \Magento\Store\Model\ScopeInterface::SCOPE_STORE): 0;
		$accountamountavailable = $this->getRequest()->getParam('accountamountavailable')? $this->getRequest()->getParam('accountamountavailable') : 0;
		
		if ($this->session->isLoggedIn() && $this->session->getCustomer()->getGroupId() == 4 && $accountamountavailable >= $withdrawalRewardAmountMin) {
			return $this->_pageFactory->create();
		}	
		 /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
		 $resultRedirect = $this->resultRedirectFactory->create();
		 $resultRedirect->setPath('affiliate/account/login/');
		 return $resultRedirect;
	}
}