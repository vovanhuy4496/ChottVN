<?php
namespace Chottvn\Notification\Controller\Account;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Notification extends \Magento\Framework\App\Action\Action
{
    protected $resultPageFactory;

    protected $session;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        PageFactory $resultPageFactory
    ) {
        $this->session = $customerSession;
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }
    public function execute()
    {
        if ($this->session->isLoggedIn() && $this->session->getCustomer()->getGroupId() == 4) {
			$resultPage = $this->resultPageFactory->create();
			$resultPage->getConfig()->getTitle()->set(__("Notification"));
			return $resultPage;
		}
        // @var \Magento\Framework\Controller\Result\Redirect $resultRedirect
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('affiliate/account/login/');
        return $resultRedirect;
    }

}