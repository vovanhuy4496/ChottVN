<?php
namespace Chottvn\Notification\Controller\Account;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Registry;

class Message extends \Magento\Framework\App\Action\Action
{
    protected $resultPageFactory;

    protected $session;

    public $_helperMessage;

    /**
     * @var Registry
     */
    protected $registry;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        PageFactory $resultPageFactory,
        Registry $registry,
        \Chottvn\Notification\Helper\Data $helperMessage
    ) {
        $this->_resultFactory = $context->getResultFactory();
        $this->session = $customerSession;
        $this->resultPageFactory = $resultPageFactory;
        $this->registry = $registry;
        $this->_helperMessage = $helperMessage;
        parent::__construct($context);
    }
    public function execute()
    {
        if (!$this->session->isLoggedIn())
        {
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('affiliate/account/login');
            return $resultRedirect;
        }
        else {
            if ($this->getRequest()->isAjax()) {
                $id = $this->getRequest()->getParam('id');
                $getDetailMessage = $this->_helperMessage->getDetailMessage($id);
                $setReadAt = $this->_helperMessage->setReadAt($id);
                $response = $this->_resultFactory
                    ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
                    ->setData([
                        'status'  => "ok",
                        'data' => $getDetailMessage
                    ]);

                return $response;
            }
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('customer/account/notification');
            return $resultRedirect;
        }
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