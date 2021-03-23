<?php
/**
 * Copyright © chotructuyen.co All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Chottvn\Rma\Controller\Adminhtml\Rule;

class Delete extends \Chottvn\Rma\Controller\Adminhtml\Rule
{

    /**
     * Delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        // check if we know what should be deleted
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            try {
                // init model and delete
                $model = $this->_objectManager->create(\Chottvn\Rma\Model\Rule::class);
                $currentUser = $this->_objectManager->get('Magento\Backend\Model\Auth\Session');
                $helperRmaLog = $this->_objectManager->get('\Chottvn\Rma\Helper\Log');
                $customerId = 0;
                if($currentUser->isLoggedIn()){
                    $customerId = $currentUser->getUser()->getId();
                }
                $model->load($id);
                if($id){
                    $helperRmaLog->saveLogWithResource([
                        "account_id" => $customerId,
                        "resource_type" => 'chottvn_rma_rule',
                        "resource_id" => $model->getId(),
                        "event" => 'delete_rule',
                        "value" => [
                            'id' => $model->getId(),
                            'name' => $model->getName(),
                            'priority' => $model->getPriority(),
                            'conditions' => $model->getConditions(),
                            'discard_subsequent_rules' => $model->getDiscardSubsequentRules(),
                            'status' => $model->getStatus(),
                            'product_kind' => $model->getProductKind(),
                            'start_date' => $model->getStartDate(),
                            'end_date' => $model->getEndDate(),
                        ]
                    ]);
                }
                $model->delete();
                
                // display success message
                $this->messageManager->addSuccessMessage(__('You deleted the RMARULE.'));
                // go to grid
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addErrorMessage($e->getMessage());
                // go back to edit form
                return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
            }
        }
        // display error message
        $this->messageManager->addErrorMessage(__('We can\'t find a RMARULE to delete.'));
        // go to grid
        return $resultRedirect->setPath('*/*/');
    }
}

