<?php
namespace Chottvn\Notification\Controller\Account;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Registry;

class LoadMoreMessage extends \Magento\Framework\App\Action\Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context, 
        PageFactory $resultPageFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
    ) {        
        $this->resultPageFactory = $resultPageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_resultLayoutFactory = $resultLayoutFactory;

        parent::__construct($context);
    }

    public function execute()
    {
        $result =  $this->_resultLayoutFactory->create();
        $response =  $this->resultJsonFactory->create();
        $resultPage = $this->resultPageFactory->create();
        if ($this->getRequest()->isAjax()) {
            $block = $resultPage->getLayout()
                ->createBlock('Chottvn\Notification\Block\Account\LoadMoreMessage')
                ->setTemplate('Chottvn_Notification::loadMoreMessage.phtml')
                ->toHtml();

            $response->setData($block);
            return $response;
        }
    }

    /**
	* @param $info
	* @param $type  [error, warning, info]
	* @return 
	*/
	private function writeLog($info, $type = "info") {
		$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/loadMoreMessage.log');
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