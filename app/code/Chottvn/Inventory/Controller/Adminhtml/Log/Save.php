<?php
/**
 * Copyright © © 2020 chotructuyen.co All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Chottvn\Inventory\Controller\Adminhtml\Log;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\DateTime;

class Save extends \Magento\Backend\App\Action
{
    public $date;

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
            $id = $this->getRequest()->getParam('log_id');
        
            $model = $this->_objectManager->create(\Chottvn\Inventory\Model\Log::class)->load($id);
            if (!$model->getId() && $id) {
                $this->messageManager->addErrorMessage(__('This Log no longer exists.'));
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
                $this->messageManager->addSuccessMessage(__('You saved the Log.'));
                $this->dataPersistor->clear('chottvn_inventory_log');
        
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['log_id' => $model->getId()]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the Log.'));
            }
        
            $this->dataPersistor->set('chottvn_inventory_log', $data);
            return $resultRedirect->setPath('*/*/edit', ['log_id' => $this->getRequest()->getParam('log_id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}

