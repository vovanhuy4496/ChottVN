<?php
/**
 * Copyright © (c) Cho Truc Tuyen 2020 All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Chottvn\PaymentAccount\Controller\Adminhtml\Bank;

use Magento\Framework\Exception\LocalizedException;
use Chottvn\PaymentAccount\Helper\Image as ImageHelper;
use Chottvn\PaymentAccount\Model\Bank\ImageUploader;

class Save extends \Chottvn\PaymentAccount\Controller\Adminhtml\Bank
{

    protected $dataPersistor;

    /**
     * Image Helper
     *
     * @var ImageHelper
     */
    protected $imageHelper;

    /**
     * @var ImageUploader
     */
    public $imageUploader;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor,
        ImageHelper $imageHelper,
        ImageUploader $imageUploader
    ) {
        $this->dataPersistor = $dataPersistor;
        $this->_coreRegistry = $coreRegistry;
        $this->imageHelper = $imageHelper;
        $this->imageUploader = $imageUploader;
        parent::__construct($context, $coreRegistry);
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
            $id = $this->getRequest()->getParam('bank_id');
        
            $model = $this->_objectManager->create(\Chottvn\PaymentAccount\Model\Bank::class)->load($id);
            if (!$model->getId() && $id) {
                $this->messageManager->addErrorMessage(__('This Bank no longer exists.'));
                return $resultRedirect->setPath('*/*/');
            }
            // Save image 
            $data = $this->processImageBank($data);
                //$this->imageHelper->uploadImage($data, 'image', ImageHelper::TEMPLATE_MEDIA_TYPE_BANK, $model->getImage());
            $model->setData($data);
        
            try {
                $model->save();
                $this->messageManager->addSuccessMessage(__('You saved the Bank.'));
                $this->dataPersistor->clear('chottvn_paymentaccount_bank');
        
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['bank_id' => $model->getId()]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the Bank.'));
            }
        
            $this->dataPersistor->set('chottvn_paymentaccount_bank', $data);
            return $resultRedirect->setPath('*/*/edit', ['bank_id' => $this->getRequest()->getParam('bank_id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }


    public function processImageBank(array $rawData)
    {
        $data = $rawData;
        if (isset($data['image'][0]['name'])) {
            $data['image'] = $data['image'][0]['name'];
            $this->imageUploader->moveFileFromTmp($data['image']);
        } else {
            $data['image'] = null;
        }
        return $data;
    }   
}

