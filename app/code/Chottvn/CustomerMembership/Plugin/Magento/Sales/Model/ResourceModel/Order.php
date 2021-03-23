<?php
/**
 * Copyright (c) 2019 2020 Ecepvn
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

namespace Chottvn\CustomerMembership\Plugin\Magento\Sales\Model\ResourceModel;

class Order {
	protected $_scopeConfig;

	/**
     * @var \Magento\Customer\Model\CustomerFactory
     */
	private $customerFactory;
	
	public function __construct(
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Customer\Model\CustomerFactory $customerFactory
	) { 
		$this->_scopeConfig = $scopeConfig;
        $this->customerFactory = $customerFactory;
	}  
	/**
	* @param \Magento\Sales\Model\Resource\Model $subject
	* @param  $result
	* @return mixed
	* @throws \Exception
	*/
	public function afterSave(
        \Magento\Sales\Model\ResourceModel\Order $subject,
        $result, $object
    ) {
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();

    $oldStatus = $object->getOrigData('status');
		$newStatus = $object->getData('status');
        $isGuest = $object->getCustomerIsGuest();
		$chottCustomerPhoneNumber = $object->getBillingAddress()->getTelephone();
		// $this->writeLog($oldStatus); 
		// $this->writeLog($newStatus);

		$isEnableAutoLevel =  $this->_scopeConfig->getValue('customer/customer_membership/is_enable_auto_level', \Magento\Store\Model\ScopeInterface::SCOPE_STORE); 			
    if ($isEnableAutoLevel == 1) {
      //if ($oldStatus == "complete" || $newStatus == "complete"){
    	if($this->isNeedToRecalculateCustomerLevel($oldStatus, $newStatus) ){
        		// Find Customer ID
        		// Case 1: Customer >> Get Customer from Order Customer ID
        		// Case 2: Guest >> Get Customer from Phone Number
        		// If Customer is not existed, do nothing
        $customerId = - 1;
				if (!$isGuest) {
						$customerId = $object->getCustomerId();
				} else {
						$customerObj = $objectManager->create('Magento\Customer\Model\ResourceModel\Customer\Collection');
						$collection = $customerObj->addAttributeToSelect('*')
									  ->addAttributeToFilter('phone_number', $chottCustomerPhoneNumber)
									  ->load();
						$customerModel = $collection->getLastItem();

						if (!empty($customerModel)) {
							$customerId = $customerModel->getId();
						}
				}
				if ($customerId != -1) {
						/*$customer = $objectManager->create('Magento\Customer\Api\CustomerRepositoryInterface')
											  ->getById($customerId);
						if (!empty($customer)) {
							$this->updateCustomerMembership($customer, $chottCustomerPhoneNumber);
						}*/
						
						$this->updateCustomerMembershipV2($customerId, $chottCustomerPhoneNumber);
				}
		  }
		}
		return $result;
	}

	public function isNeedToRecalculateCustomerLevel($orderStatusOld, $orderStatusNew){
		$isNeedTo = false;
		if(in_array($orderStatusOld, ["complete","finished","returned_and_finished"]) 
			&& in_array($orderStatusNew, ["complete","finished","returned_and_finished"]) ){
			$isNeedTo = false;
		}else {
			$isNeedTo = true;
		}
		return $isNeedTo;
	}
	
	/**
	* @param {String} $chottCustomerPhoneNumber
	* @return 
	*/
    public function getCustomerLevelByPhoneNumber($chottCustomerPhoneNumber) {   	
		$this->writeLog('chottCustomerPhoneNumber: '.$chottCustomerPhoneNumber);  	
		//
    	$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    	// Get Customer Purchase status
    	$calculatorHelper = $objectManager->create('Chottvn\CustomerMembership\Helper\Calculator');
		$allPeriod = $calculatorHelper->getAllPeriodTotal($chottCustomerPhoneNumber);
		$this->writeLog($allPeriod);

		// Get Rule Config
		$rulesData =  $this->_scopeConfig->getValue('customer/customer_membership/level_rules',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		
		try {
		  $rules = json_decode($rulesData, true);

		  if (is_null($rules)) {
		    throw new \Exception('Rule Json Data Error');
		  }
		} catch (\Exception $e) {
			$this->writeLog("ERROR: ".$e->getMessage());
		    return null;
		}
		// Go through rules (Top Down) and check	
		$levelCode =  null;	
		foreach ($rules as $rule) {
			if ($this->checkRuleV2($rule, $allPeriod)) {
				$levelCode = $rule["code"];
				break;
			};
		}	
		return $levelCode;	
	}


	/**
	* @param {Int} $customerId
	* @param {String} $chottCustomerPhoneNumber
	* @return 
	*/
    public function updateCustomerMembershipV2($customerId, $chottCustomerPhoneNumber) { 
    	// Logging 
    	$this->writeLog('customerId: '.$customerId);		
			//$this->writeLog('chottCustomerPhoneNumber: '.$chottCustomerPhoneNumber);  	
		// Initial
    	$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    	// Get Customer from Id
    	try {
	        $customer = $objectManager->create('Magento\Customer\Api\CustomerRepositoryInterface')
											  ->getById($customerId);
	    } //catch(NoSuchEntityException $e) {
	    	catch(\Exception $e) {
	        $customer = null;
	    }    	
			if (empty($customer)) {
				return;
			}

    	if ($levelCode = $this->getCustomerLevelByPhoneNumber($chottCustomerPhoneNumber)){
            $this->setCustomerLevel($customer, $levelCode);
      }   	
	}


    /**
	* @param {} $customer
	* @param {String} $chottCustomerPhoneNumber
	* @return 
	*/
    public function updateCustomerMembership($customer, $chottCustomerPhoneNumber) {
    	$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    	// Get Customer Purchase status
    	$calculatorHelper = $objectManager->create('Chottvn\CustomerMembership\Helper\Calculator');
		$allPeriod = $calculatorHelper->getAllPeriodTotal($chottCustomerPhoneNumber);
		$this->writeLog($allPeriod);

		// Get Rule Config
		$rulesData =  $this->_scopeConfig->getValue('customer/customer_membership/level_rules',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		
		try {
		  $rules = json_decode($rulesData, true);

		  if (is_null($rules)) {
		    throw new \Exception('Rule Json Data Error');
		  }
		} catch (\Exception $e) {
			$this->writeLog("ERROR: ".$e->getMessage());
		    return;
		}
		// Go through rules (Top Down) and check		
		foreach ($rules as $rule) {
			if ($this->checkRule($customer, $rule, $allPeriod)) {
				break;
			};
		}		
	}

	/**
	* @param {String} $levelCode
	* @return 
	*/
	public function getRuleByLevelCode($levelCode){
		// Get Rule Config
		$rulesData =  $this->_scopeConfig->getValue('customer/customer_membership/level_rules',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);		
		try {
		  $rules = json_decode($rulesData, true);

		  if (is_null($rules)) {
		    throw new \Exception('Rule Json Data Error');
		  }
		} catch (\Exception $e) {
			$this->writeLog("ERROR: ".$e->getMessage());
		    return null;
		}
		$result = null;
		foreach ($rules as $rule) {
			if ($rule["code"] == $levelCode) {
				$result = $rule;
				break;
			};
		}	
		return $result;
	}

	/**
	* @param {String} $levelCode
	* @return 
	*/
	public function getLevelPriorityByLevelCode($levelCode){
		$priority = -1;		
		if ($rule = $this->getRuleByLevelCode($levelCode)){
			if($rule["priority"]){
				$priority = $rule["priority"];	
			}
		}
		return $priority;
	}


	/**
	* Check Rule > Set customer level
	* @param {} $customer
	* @param {String} $rule
	* @param {Dictionary} $purchaseSummary
	* @return {Boolean}
	*/
	private function checkRule($customer, $rule, $purchaseSummary) {
		$ruleItems = $rule["items"];
		$ruleItemsResult = array();
		$aggType = $rule["agg_type"];
		$levelCode = $rule["code"];
		$ruleResult = false;		
		foreach($ruleItems as $ruleItem) {
			$isRuleItemValid = $this->checkRuleItem($ruleItem, $purchaseSummary);
			array_push($ruleItemsResult, $isRuleItemValid);			
		}
		// Aggregate Rule Item Results
		$resultLength = sizeof($ruleItemsResult);
		if ($resultLength > 0) {
			$ruleResult = $ruleItemsResult[0];
			if ($resultLength > 1) {
				for ($i = 1; $i < $resultLength; $i++) {
				  	switch ($aggType) {
						case "and":
							$ruleResult = $ruleResult && $ruleItemsResult[$i];
							break;
						case "or":
							$ruleResult = $ruleResult || $ruleItemsResult[$i];
							break;
					}
				} 
			}
			
		}
		// Update Customer Level if valid
		if ($ruleResult) {
			$this->setCustomerLevel($customer, $levelCode);
		}
		return $ruleResult;
	}

	/**
	* Check Rule 
	* @param {String} $rule
	* @param {Dictionary} $purchaseSummary
	* @return {Boolean}
	*/
	private function checkRuleV2($rule, $purchaseSummary) {
		$ruleItems = $rule["items"];
		$ruleItemsResult = array();
		$aggType = $rule["agg_type"];
		$levelCode = $rule["code"];
		$ruleResult = false;		
		foreach($ruleItems as $ruleItem) {
			$isRuleItemValid = $this->checkRuleItem($ruleItem, $purchaseSummary);
			array_push($ruleItemsResult, $isRuleItemValid);			
		}
		// Aggregate Rule Item Results
		$resultLength = sizeof($ruleItemsResult);
		if ($resultLength > 0) {
			$ruleResult = $ruleItemsResult[0];
			if ($resultLength > 1) {
				for ($i = 1; $i < $resultLength; $i++) {
				  	switch ($aggType) {
						case "and":
							$ruleResult = $ruleResult && $ruleItemsResult[$i];
							break;
						case "or":
							$ruleResult = $ruleResult || $ruleItemsResult[$i];
							break;
					}
				} 
			}
			
		}
		// Update Customer Level if valid
		/*if ($ruleResult) {
			$this->setCustomerLevel($customer, $levelCode);
		}*/
		return $ruleResult;
	}
	
	/**
	* Check Rule item
	* @param $ruleItem
	* @param  $purchaseSummary
	* @return {Boolean}
	*/
	private function checkRuleItem($ruleItem, $purchaseSummary)
	{
		$indexValue = $this->getPurchaseSummaryIndexValue($purchaseSummary, $ruleItem['code']);
		$thresholdValue = (float)$ruleItem['value'];
		switch ($ruleItem['operator']) {
			case '>':
				return $indexValue > $thresholdValue;
				break;
			case '>=':
				return $indexValue >= $thresholdValue;
				break;
			case '=':
				return $indexValue == $thresholdValue;
				break;
			case '<':
				return $indexValue < $thresholdValue;
				break;			
			case '<=':
				return $indexValue <= $thresholdValue;
				break;
			case '!=':
				return $indexValue != $thresholdValue;
				break;
		}
		return false;
	}

	/**
	* @param $purchaseSummary
	* @param $code
	* @return 
	*/
	private function getPurchaseSummaryIndexValue($purchaseSummary, $code){
		$indexValue = 0;
		switch ($code) {
			case 'total_amount':
				$indexValue = $purchaseSummary['total_orders_amount'];
				break;
			case 'total_order_complete':
				$indexValue = $purchaseSummary['of_placed_orders'];
				break;
			case 'avg_order_amount':
				$indexValue = $purchaseSummary['average_order_value'];
				break;
		}
		if (is_null($indexValue)) {
			$indexValue = 0;
		}
		return $indexValue;
	}

	/**
	* @param $customer
	* @param String $level
	* @return 
	*/
    private function setCustomerLevel($customer, $level) {
		$oldLevel = '';
		if ($myCustomAttribute = $customer->getCustomAttribute('customer_level')) {
			$oldLevel = $myCustomAttribute->getValue();
		}
    	if ($oldLevel == $level) {
    		// $this->writeLog("Level: " . $level);
    		return;
		}
		$priorityOldLevel = $this->getLevelPriorityByLevelCode($oldLevel);
		$priorityNewLevel = $this->getLevelPriorityByLevelCode($level);
		// $this->writeLog("Priority: ".$priorityOldLevel."  --  ".$priorityNewLevel);
		if ($priorityOldLevel >= $priorityNewLevel){
			return;
		}

		$customer->setCustomAttribute('customer_level', $level);
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$objectManager->create('Magento\Customer\Api\CustomerRepositoryInterface')->save($customer);
		// $this->writeLog("Level: ".$oldLevel." >> ".$level);
	}

    /**
	* @param $info
	* @param $type  [error, warning, info]
	* @return 
	*/
	private function writeLog($info, $type = "info") {
		$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/customer_membership.log');
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



	/*
	 * Sample
		// Sample Code
		$levelPlatinumTA = 50000000;
		$levelGoldTA = 30000000;
		$levelSilverTA = 10000000;
		$levelSilverTA = 10000000;
		$levelSilverCO = 3;
		$levelBronzeCO = 2;
		$levelBasicCO = 1;	
		if ($totalAmount >= $levelPlatinumTA){
			$this->setCustomerLevel($customer, 'platinum');
		}else if ($totalAmount >= $levelGoldTA){
			$this->setCustomerLevel($customer, 'gold');
		}else if ($totalAmount >= $levelSilverTA || $totalOrderComplete >= $levelSilverCO){
			$this->setCustomerLevel($customer, 'silver');
		}else if ($totalAmount >= $levelSilverTA || $totalOrderComplete >= $levelSilverCO){
			$this->setCustomerLevel($customer, 'bronze');
		}else if ($totalOrderComplete >= $levelBasicCO){
			$this->setCustomerLevel($customer, 'basic');
		}else{
			$this->setCustomerLevel($customer, 'member');
		}
	 */



}

?>