<?php
declare(strict_types=1);

namespace Chottvn\Notification\Controller\Adminhtml\MessageType;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\DateTime;

class Save extends \Magento\Backend\App\Action
{

    protected $dataPersistor;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor,
        DateTime $date
    ) {
        $this->dataPersistor = $dataPersistor;
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
        
            $model = $this->_objectManager->create(\Chottvn\Notification\Model\MessageType::class)->load($id);
            if (!$model->getId() && $id) {
                $this->messageManager->addErrorMessage(__('This MessageType no longer exists.'));
                return $resultRedirect->setPath('*/*/');
            }
        
            $model->setData($data);
            
            if (isset($id)) {
                $model->setData('updated_at', $this->date->date());
            } else {
                $model->setData('created_at', $this->date->date());
            }
            try {
                $model->save();
                $this->messageManager->addSuccessMessage(__('You saved the MessageType.'));
                $this->dataPersistor->clear('chottvn_notification_messagetype');
        
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $model->getId()]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the MessageType.'));
            }
        
            $this->dataPersistor->set('chottvn_notification_messagetype', $data);
            return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}

