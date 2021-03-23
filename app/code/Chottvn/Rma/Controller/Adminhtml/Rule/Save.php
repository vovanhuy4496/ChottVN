<?php
/**
 * Copyright © chotructuyen.co All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Chottvn\Rma\Controller\Adminhtml\Rule;

use Magento\Framework\Exception\LocalizedException;

class Save extends \Magento\Backend\App\Action
{

    protected $dataPersistor;
    protected $_helperRmaLog;
    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Chottvn\Rma\Helper\Log $helperRmaLog,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
    ) {
        $this->_helperRmaLog = $helperRmaLog;
        $this->dataPersistor = $dataPersistor;
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
            $model = $this->_objectManager->create(\Chottvn\Rma\Model\Rule::class)->load($id);
            if (!$model->getId() && $id) {
                $this->messageManager->addErrorMessage(__('This RMARule no longer exists.'));
                return $resultRedirect->setPath('*/*/');
            }
            $checkAction = $model->getId() ? $model->getId():'';
            $model->setData($data);
            $currentUser = $this->_objectManager->get('Magento\Backend\Model\Auth\Session');
            $customerId = 0;
            if($currentUser->isLoggedIn()){
                $customerId = $currentUser->getUser()->getId();
            }
            try {
                $model->save();
                if($checkAction){
                    $this->_helperRmaLog->saveLogWithResource([
                        "account_id" => $customerId,
                        "resource_type" => 'chottvn_rma_rule',
                        "resource_id" => $checkAction,
                        "event" => 'update_rule',
                        "value" => $data
                    ]);
                }else{
                    if($model->getId()){
                        $this->_helperRmaLog->saveLogWithResource([
                            "account_id" => $customerId,
                            "resource_type" => 'chottvn_rma_rule',
                            "resource_id" => $model->getId(),
                            "event" => 'create_rule',
                            "value" => $data
                        ]);
                    }
                }
                $this->messageManager->addSuccessMessage(__('You saved the RMARule.'));
                $this->dataPersistor->clear('chottvn_rma_rule');
        
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $model->getId()]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {

                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the RMARule.'));
            }
        
            $this->dataPersistor->set('chottvn_rma_rule', $data);
            return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }
      /**
     * @param $info
     * @param $type  [error, warning, info]
     * @return 
     */
    private function writeLog($info, $type = "info")
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/save_rma.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        switch ($type) {
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

