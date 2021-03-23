<?php

/**
 * Copyright © (c) Cho Truc Tuyen 2020 All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Chottvn\PaymentAccount\Controller\Adminhtml\CustomerBankAccount;

use Magento\Framework\Exception\LocalizedException;
use Chottvn\Affiliate\Helper\Log as HelperAffiliateLog;
use Chottvn\Affiliate\Model\Log as AffiliateLog;

class Save extends \Magento\Backend\App\Action
{

    protected $dataPersistor;

    /**
     * @var HelperAffiliateLog
     */
    protected $helperAffiliateLog;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor,
        HelperAffiliateLog $helperAffiliateLog
    ) {
        $this->dataPersistor = $dataPersistor;
        $this->_helperAffiliateLog = $helperAffiliateLog;
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
            $id = $this->getRequest()->getParam('customerba_id');

            $model = $this->_objectManager->create(\Chottvn\PaymentAccount\Model\CustomerBankAccount::class)->load($id);
            if (!$model->getId() && $id) {
                $this->messageManager->addErrorMessage(__('This CustomerBankAccount no longer exists.'));
                return $resultRedirect->setPath('*/*/');
            }

            $model->setData($data);

            try {
                $model->save();
                $this->saveLog($model);
                $this->messageManager->addSuccessMessage(__('You saved the CustomerBankAccount.'));
                $this->dataPersistor->clear('chottvn_paymentaccount_customerbankaccount');

                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['customerba_id' => $model->getId()]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the CustomerBankAccount.'));
            }

            $this->dataPersistor->set('chottvn_paymentaccount_customerbankaccount', $data);
            return $resultRedirect->setPath('*/*/edit', ['customerba_id' => $this->getRequest()->getParam('customerba_id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }

    private function saveLog($model)
    {
        // Save log bank
        $this->_helperAffiliateLog->saveLogWithResource([
            "account_id" => $model->getData('customer_id'),
            "resource_type" => 'chottvn_paymentaccount_customerba',
            "resource_id" => $model->getId(),
            "event" => AffiliateLog::EVENT_BANK_ACCOUNT_CHANGED,
            "value" => [
                "paymentaccount_bank_id" => (int)$model->getData('paymentaccount_bank_id'),
                "account_owner" => $model->getData('account_owner'),
                "account_number" => $model->getData('account_number'),
                "bank_branch" => $model->getData('bank_branch'),
                "note" => $model->getData('note'),
                "status" => $model->getData('status')
            ]
        ]);
    }
}
