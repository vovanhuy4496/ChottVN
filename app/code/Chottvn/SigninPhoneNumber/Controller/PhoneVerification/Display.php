<?php

namespace Chottvn\SigninPhoneNumber\Controller\PhoneVerification;

class Display extends \Magento\Framework\App\Action\Action
{
	protected $_pageFactory;

	protected $_phoneVerificationFactory;

	protected $_resource;

	protected $_request;

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $pageFactory,
		\Chottvn\SigninPhoneNumber\Model\PhoneVerificationFactory $phoneVerificationFactory,
		\Chottvn\SigninPhoneNumber\Model\ResourceModel\PhoneVerification $resource,
		\Magento\Framework\App\Request\Http $request
	) {
		$this->_pageFactory = $pageFactory;
		$this->_phoneVerificationFactory = $phoneVerificationFactory;
		$this->_resource = $resource;
		$this->_request = $request;
		return parent::__construct($context);
	}

	public function execute()
	{
        $resultPage = $this->_pageFactory->create();   
        return $resultPage;  
	}
}
