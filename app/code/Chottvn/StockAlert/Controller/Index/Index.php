<?php

namespace Chottvn\StockAlert\Controller\Index;

use Magento\Framework\Controller\ResultFactory;
use Zend\Log\Filter\Timestamp;
use Chottvn\StockAlert\Helper\Data as HelperData;

class Index extends \Magento\Framework\App\Action\Action
{

    protected $_logLoggerInterface;
    protected $_storeManager;
    public $helperData;
    protected $_coreSession;
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Psr\Log\LoggerInterface $loggerInterface,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Session\SessionManagerInterface $coreSession,
        HelperData $helperData,
        array $data = []
    ) {
        $this->_storeManager = $storeManager;
        $this->helperData = $helperData;
        $this->messageManager = $context->getMessageManager();
        parent::__construct($context);
        $this->_coreSession = $coreSession;
    }
    /**
     * Index action
     *
     * @return void
     */
    public function execute()
    {
        $post = $this->getRequest()->getPostValue();
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $url = '';
        try
        {
            if (!empty($post)) {
                if($post['customer_id']== 0){
                    $post['customer_id'] = null;
                }
                $url = $post['url_product'];
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $model = $objectManager->create('Chottvn\StockAlert\Model\StockAvailableObserver');
                $model->addData($post);
                $model->save();
                // send mail
                $this->helperData->sendMail($post);
                $this->messageManager->addSuccessMessage(__('Thanks for contacting us with your comments and questions. We\'ll respond to you very soon.'));
                // Redirect to your form page (or anywhere you want...)
                $resultRedirect->setUrl($url);
                $this->_coreSession->start();
                $this->_coreSession->setMessage('Success');
                return $resultRedirect;
            }
        } catch(\Exception $e){
            $this->_coreSession->start();
            $this->_coreSession->setMessage('Error');
            $this->messageManager->addError($e->getMessage());
            $this->_logLoggerInterface->debug($e->getMessage());
        }
        $resultRedirect->setUrl($url);
        return $resultRedirect;
    }
    /**
	* @param $info
	* @param $type  [error, warning, info]
	* @return
	*/
	private function writeLog($info, $type = "info"){
		$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/send_email.log');
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
