<?php
/**
 * Copyright (c) 2019 2020 ChottVN
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace Chottvn\OrderPayment\Controller\Adminhtml\BankAccount;

use Magento\Framework\Exception\LocalizedException;
use Chottvn\OrderPayment\Helper\Image;
use Chottvn\OrderPayment\Model\BankAccount;
use Magento\Framework\Exception\StateException;
/**
 * Class Save
 *
 * @package Chottvn\OrderPayment\Controller\Adminhtml\BankAccount
 */
class Save extends \Magento\Backend\App\Action
{
    protected $dataPersistor;

    protected $imageHelper;

    protected $collection;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor,
        Image $imageHelper,
        BankAccount $collection
    ) {
        $this->dataPersistor = $dataPersistor;
        $this->imageHelper = $imageHelper;
        $this->collection = $collection;
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
            $id = $this->getRequest()->getParam('bankaccount_id');
            $model = $this->_objectManager->create(\Chottvn\OrderPayment\Model\BankAccount::class)->load($id);
           
            if (!$model->getId() && $id) {
                $this->messageManager->addErrorMessage(__('This Bankaccount no longer exists.'));
                return $resultRedirect->setPath('*/*/');
            }
           
            if(!$id){
                //check field condition return bool
                $account_number = $this->checkFieldInput($data['account_number'],'account_number');
                $bank_name = $this->checkFieldInput($data['bank_name'],'bank_name');
            }else{
                $account_number = false;
                $bank_name = false;
            }
            // save image 
            $data = $this->handleimageBank($data);
            $model->setData($data);
            try {
                if($account_number == false && $bank_name == false){
                    $model->save();
                    $this->messageManager->addSuccessMessage(__('You saved the Bankaccount.'));
                    $this->dataPersistor->clear('chottvn_orderpayment_bankaccount');
                }else{
                    throw new StateException(__('Account number or bank name has been duplicated.'));
                }
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['bankaccount_id' => $model->getId()]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the Bankaccount.'));
            }
        
            $this->dataPersistor->set('chottvn_orderpayment_bankaccount', $data);
            return $resultRedirect->setPath('*/*/edit', ['bankaccount_id' => $this->getRequest()->getParam('bankaccount_id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }
    public function handleimageBank(array $rawData)
    {
        $data = $rawData;
        if (isset($data['bank_image'][0]['name'])) {
            $data['bank_image'] = $data['bank_image'][0]['name'];
        } else {
            $data['bank_image'] = null;
        }
        return $data;
    }
    protected function checkFieldInput($data = [],$name)
    {
        $collection = $this->collection->getSelectedbankCollection();
        $array_condition = $collection->getColumnValues($name);
        foreach($array_condition as $item){
            if($data == $item){
                return true;
            }   
        }
       return false;
    }
   
}

