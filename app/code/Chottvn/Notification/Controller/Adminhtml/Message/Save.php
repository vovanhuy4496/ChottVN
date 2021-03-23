<?php
/**
 * Copyright © chotructuyen.co All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Chottvn\Notification\Controller\Adminhtml\Message;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\DateTime;

class Save extends \Magento\Backend\App\Action
{
    public $_helperAccount;
    
    /**
     * @var MessageFactory
     */
    public $messageFactory;

    protected $dataPersistor;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor,
        \Chottvn\Affiliate\Helper\Account $helperAccount,
        \Chottvn\Notification\Model\MessageFactory $messageFactory,
        DateTime $date
    ) {
        $this->dataPersistor = $dataPersistor;
        $this->_helperAccount = $helperAccount;
        $this->messageFactory = $messageFactory;
        $this->date = $date;
        parent::__construct($context);
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();
        if ($data) {
            $id = $this->getRequest()->getParam('id');
        
            $model = $this->_objectManager->create(\Chottvn\Notification\Model\Message::class)->load($id);
            if (!$model->getId() && $id) {
                $this->messageManager->addErrorMessage(__('This Message no longer exists.'));
                return $resultRedirect->setPath('*/*/');
            }
            $data['customer_group_ids'] = implode(',', $data['customer_group_ids']);
            $data = $this->saveImg($data);
            $model->setData($data);
            
            if (isset($id)) {
                $model->setData('updated_at', $this->date->date());
            } else {
                $model->setData('created_at', $this->date->date());
            }
            try {
                $model->save();
                $message_id = $this->messageFactory->create()->getCollection()->getLastItem()->getId();

                if ($message_id != $id) {
                    $getFilteredCustomerCollection = $this->_helperAccount->getFilteredCustomerCollection();
                    if ($getFilteredCustomerCollection->getSize() > 0 && $message_id) {
                        foreach($getFilteredCustomerCollection->getData() as $item) {
                            $customerEmail = $this->_objectManager->create('Magento\Customer\Api\CustomerRepositoryInterface')->getById($item['entity_id']);

                            $saveDelivery = $this->_objectManager->create('Chottvn\Notification\Model\Delivery');
                            $saveDelivery->setMessageId($message_id);
                            $saveDelivery->setCustomerId($item['entity_id']);
                            $saveDelivery->setCustomerEmail($customerEmail->getCustomAttribute('customer_email')->getValue());
                            $saveDelivery->setData('created_at', $this->date->date());
                            $saveDelivery->save();
                        }
                    }
                }

                $this->messageManager->addSuccessMessage(__('You saved the Message.'));
                $this->dataPersistor->clear('notification_message');
        
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $model->getId()]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->writeLog($e);
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->writeLog($e);
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the Message.'));
            }
        
            $this->dataPersistor->set('notification_message', $data);
            return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }

    public function saveImg(array $rawData)
    {
        $data = $rawData;
        if (isset($data['image'][0]['name'])) {
            $data['image'] = $data['image'][0]['name'];
        } else {
            $data['image'] = null;
        }
        return $data;
    }

    /**
	* @param $info
	* @param $type  [error, warning, info]
	* @return 
	*/
	private function writeLog($info, $type = "info") {
		$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/saveNotificationMessage.log');
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
