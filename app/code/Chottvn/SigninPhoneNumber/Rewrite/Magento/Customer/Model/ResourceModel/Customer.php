<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Chottvn\SigninPhoneNumber\Rewrite\Magento\Customer\Model\ResourceModel;
use \Magento\Framework\Exception\AlreadyExistsException;
/**
 * Customer entity resource model
 *
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class Customer extends \Magento\Customer\Model\ResourceModel\Customer
{
    
    protected function _beforeSave(\Magento\Framework\DataObject $customer)
    {
        // $this->writeLog("customer model before save");
        /** @var \Magento\Customer\Model\Customer $customer */
        if ($customer->getStoreId() === null) {
            $customer->setStoreId($this->storeManager->getStore()->getId());
        }
        $customer->getGroupId();
        $this->updateCustomerLevel($customer);

        //parent::_beforeSave($customer);     
        
        // Update Customer move to hottvn\SigninPhoneNumber\ModelAccountManagement::updateCustomerLevel();  
        /*if (empty($customer->getCustomerLevel()) ){
            $chottCustomerPhoneNumber = $customer->getPhoneNumber();
           
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    
            $cttSaleOrderPlugin = $objectManager->create('Chottvn\CustomerMembership\Plugin\Magento\Sales\Model\ResourceModel\Order');
            //$cttSaleOrderPlugin->updateCustomerMembership($customer, $chottCustomerPhoneNumber);      
            $levelCode = $cttSaleOrderPlugin->getCustomerLevelByPhoneNumber($chottCustomerPhoneNumber);   
            if ( !empty($levelCode) ){
                $customer->setCustomerLevel($levelCode);
            }             
        }*/
        if (!$customer->getEmail() && !$customer->getPhoneNumber()) {            
            exit();
            throw new ValidatorException(__('The customer email is missing. Enter and try again.'));
        }

        if (!empty($customer->getEmail()) ){
            $connection = $this->getConnection();
            $bind = ['email' => $customer->getEmail()];

            $select = $connection->select()->from(
                $this->getEntityTable(),
                [$this->getEntityIdField()]
            )->where(
                'email = :email'
            );
            if ($customer->getSharingConfig()->isWebsiteScope()) {
                $bind['website_id'] = (int)$customer->getWebsiteId();
                $select->where('website_id = :website_id');
            }
            if ($customer->getId()) {
                $bind['entity_id'] = (int)$customer->getId();
                $select->where('entity_id != :entity_id');
            }

            $result = $connection->fetchOne($select, $bind);
            if ($result) {
                throw new AlreadyExistsException(
                    __('A customer with the same email address already exists in an associated website.')
                );
            }

            // set confirmation key logic
            if (!$customer->getId() && $customer->isConfirmationRequired()) {
                $customer->setConfirmation($customer->getRandomConfirmationKey());
            }
            // remove customer confirmation key from database, if empty
            if (!$customer->getConfirmation()) {
                $customer->setConfirmation(null);
            }

            if (!$customer->getData('ignore_validation_flag')) {
                $this->_validate($customer);
            }
        }
        

        // $this->writeLog("customer model before save end");
        return $this;
    }

    /**
     * Set Customer Level by 
     *
     * @param CustomerInterface $customer
     * @return void
     */
    protected function updateCustomerLevel($customer){   
        try{                       
            if($chottCustomerPhoneNumber = $customer->getPhoneNumber()){
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

                $cttSaleOrderPlugin = $objectManager->create('Chottvn\CustomerMembership\Plugin\Magento\Sales\Model\ResourceModel\Order');
                // $this->writeLog('chottCustomerPhoneNumber: '.$chottCustomerPhoneNumber);
                $levelCode = $cttSaleOrderPlugin->getCustomerLevelByPhoneNumber($chottCustomerPhoneNumber);                   
                // $this->writeLog('levelCode: '.$levelCode);
                if (empty($levelCode)) {
                    $levelCode = "member";                
                }
                $customer->setCustomerLevel($levelCode);
            }
            
        }catch(\Exception $e){
            $this->writeLog($e);
            throw $e;
        }
    }

    /**
	* @param $info
	* @param $type  [error, warning, info]
	* @return 
	*/
	private function writeLog($info, $type = "info") {
		$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/level_membership_register.log');
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
