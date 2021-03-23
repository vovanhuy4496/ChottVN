<?php
namespace Chottvn\Notification\Block\Account;

class LoadMoreMessage extends \Magento\Framework\View\Element\Template
{
	// Request
	protected $_request;
	protected $_response;
	public $_helperAccount;
    public $_customer;
    public $_helperMessage;

	/**
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Magento\Customer\Model\Session $customer,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\App\Response\Http $response,
        \Chottvn\Affiliate\Helper\Account $helperAccount,
        \Chottvn\Notification\Helper\Data $helperMessage,
        array $data = []
    ) {
        $this->_customer = $customer;
    	$this->_request = $request;
    	$this->_response = $response;
        $this->_helperAccount = $helperAccount;
        $this->_helperMessage = $helperMessage;
        parent::__construct($context, $data);
    }
    public function getMessageCollection()
    {
        $lastId = $this->getRequest()->getParam('lastId');
        return $this->_helperMessage->getLoadMoreMessageCollection($lastId);
    }

    public function getReadAtMessage($id)
    {
        return $this->_helperMessage->getReadAtMessage($id);
    }

    public function getMessageType($id)
    {
        return $this->_helperMessage->getMessageType($id);
    }

    /**
	* @param $info
	* @param $type  [error, warning, info]
	* @return 
	*/
	private function writeLog($info, $type = "info") {
		$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/NotificationMessageAccount.log');
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