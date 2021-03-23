<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Chottvn\OrderPayment\Rewrite\Magento\Payment\Model;

use Magento\Framework\App\ObjectManager;
use Magento\Payment\Model\Method\AbstractMethod;
use Chottvn\Finance\Helper\Transaction as FinanceTransactionHelper;

/**
 * Methods List service class.
 *
 * @api
 * @since 100.0.2
 */
class MethodList extends \Magento\Payment\Model\MethodList
{
    protected $scopeConfig;

    /**
     * @var \Magento\Payment\Helper\Data
     * @deprecated 100.1.0 Do not use this property in case of inheritance.
     */
    protected $paymentHelper;

    /**
     * @var \Magento\Payment\Model\Checks\SpecificationFactory
     * @deprecated 100.2.0 Do not use this property in case of inheritance.
     */
    protected $methodSpecificationFactory;

    /**
     * @var \Magento\Payment\Api\PaymentMethodListInterface
     */
    private $paymentMethodList;

    /**
     * @var \Magento\Payment\Model\Method\InstanceFactory
     */
    private $paymentMethodInstanceFactory;

    /**
     * @param \Magento\Payment\Helper\Data $paymentHelper
     * @param Checks\SpecificationFactory $specificationFactory
     */
    public function __construct(
        \Magento\Payment\Helper\Data $paymentHelper,
        \Magento\Payment\Model\Checks\SpecificationFactory $specificationFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        FinanceTransactionHelper $financeTransactionHelper
    ) {
        $this->paymentHelper = $paymentHelper;
        $this->methodSpecificationFactory = $specificationFactory;
        $this->scopeConfig = $scopeConfig;
        $this->financeTransactionHelper = $financeTransactionHelper;
    }

    /**
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return \Magento\Payment\Model\MethodInterface[]
     */
    public function getAvailableMethods(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        $store = $quote ? $quote->getStoreId() : null;
        $availableMethods = [];        
        foreach ($this->getPaymentMethodList()->getActiveList($store) as $method) {
            $methodInstance = $this->getPaymentMethodInstanceFactory()->create($method);            
            if ($methodInstance->isAvailable($quote) && $this->_canUseMethod($methodInstance, $quote)) {                
                // Check custom rules
                if ($this->isMethodPassed($methodInstance->getCode(), $quote)){
                    $methodInstance->setInfoInstance($quote->getPayment());
                    $availableMethods[] = $methodInstance;
                }
                
            }
        }
        return $availableMethods;
    }

    /**
    * Check Method is passed
    * @param $methodCode
    * @param  $quote
    * @return {Boolean}
    */
    public function getMethodRestrictionRules(){
        // Get Rule Config
        $rulesData =  $this->scopeConfig->getValue('checkout/chottvn_orderpayment/payment_method_rules',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        try {
            // Parse Json Data
            $rules = json_decode($rulesData, true);

            if (is_null($rules)) {
            throw new \Exception('Rule Json Data Error');
            }
        //$this->writeLog($rules);
          return $rules;
        } catch (\Exception $e) {            
            return [];
        }
    }
    public function getMethodRestrictionRulesTemp(){
        return [
            "banktransfer" => [
                
            ],
            "cashondelivery" => [
                "agg_type" => "and", // and, or
                "items" => [
                    [
                        "code" => "total_amount",
                        "operator" => "<=",
                        "value" => 5000000
                    ],
                    [
                        "code" => "region",
                        "operator" => "in", // not_in
                        "value" => [658]
                    ]                                    
                ]
            ]
        ];
    }
    
    /**
    * Check Method is passed
    * @param $methodCode
    * @param  $quote
    * @return {Boolean}
    */
    private function isMethodPassed($methodCode, $quote){
        try{
            //$this->writeLog("CustID:  ".$quote->getCustomerId());
            $rules = $this->getMethodRestrictionRules();        
            $rule = $rules[$methodCode];
            // $this->writeLog($rule);
                    
            $ruleResult = false;    
            if (empty($rule) || empty($rule["items"]) ){
                $ruleResult = true;            
                return $ruleResult;
            }
            $ruleItems = $rule["items"];
            $ruleItemsResult = array();
            $aggType = $rule["agg_type"];               
            foreach($ruleItems as $ruleItem) { 
                //$this->writeLog(json_encode($ruleItem));
                $isRuleItemValid = $this->isRuleItemPassed($ruleItem, $quote);
                //$this->writeLog("isPASSED: ".json_encode($isRuleItemValid));
                //if($isRuleItemValid != null){
                    array_push($ruleItemsResult, $isRuleItemValid); 
                //}
            }
           
            // Aggregate Rule Item Results
            $ruleResult = $this->aggRuleItemsResult($ruleItemsResult, $aggType);
            // $this->writeLog($ruleResult);
            return $ruleResult;
        }catch(\Exception $e){
            $this->writeLog($e);
            return false;
        }        
    }

    private function isRuleItemPassed($ruleItem, $quote){
        try{
            $result = null; 
            if(array_key_exists("kind", $ruleItem)){
                $kind = $ruleItem["kind"];
            }else{
                $kind = "single";
            }
            switch ($kind) {
                case "single":                    
                    $result = $this->isRuleItemSinglePassed($ruleItem, $quote);
                    break;
                case "agg":
                    $aggType = $ruleItem["agg_type"];
                    $ruleItemsLevel2 = $ruleItem["items"];
                    $ruleItemsResult = array();
                    if (empty($ruleItemsLevel2)){
                        $result = true;
                    }else{
                        foreach($ruleItemsLevel2 as $ruleItem) {
                            $isRuleItemValid = $this->isRuleItemPassed($ruleItem, $quote);
                            //if($isRuleItemValid  !=  null){
                                array_push($ruleItemsResult, $isRuleItemValid);
                            //}         
                        }
                        $result = $this->aggRuleItemsResult($ruleItemsResult, $aggType);
                    }
                    break;
                default:   
                    $result = false;                 
            }
            //$this->writeLog(">> isRuleItemPassed: ".json_encode($result));
            return $result;
        }
        catch(\Exception $e){
            $this->writeLog($e);
            return null;
        }
    }

    private function isRuleItemSinglePassed($ruleItem, $quote){
        try{
            $operator = $ruleItem["operator"];
            $thresholdValue = $ruleItem["value"];
            $value  = $this->getValueOfCondition($ruleItem["code"], $quote);
            return $this->checkExpression($operator, $value, $thresholdValue);
        }catch(\Exception $e){
            $this->writeLog($e);
            return null;
        }
    }

    private function aggRuleItemsResult($ruleItemsResult, $aggType){
        try{
            $ruleResult = false;    
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
            return $ruleResult;
        }catch(\Exception $e){
            $this->writeLog($e);
            return false;
        }        
    }

    /**
    * Get Value of Condition
    * @param $conditionCode
    * @return 
    */
    private function getValueOfCondition($conditionCode, $quote){
        try{
            $value =  null;
            $regionId = $quote->getShippingAddress()->getRegionId();
            if (empty($quote->getShippingAddress()->getRegionId())) {
                if (isset($_COOKIE['region_id'])) {
                    $regionId = $_COOKIE['region_id'];
                    //$this->writeLog("REGION: ".$regionId);
                }
            }
            $customerId  = $quote->getCustomerId();
            switch ($conditionCode) {
                case 'region':
                    $value = $regionId;
                    break;
                case "total_amount":
                    $value  = $quote->getGrandTotal();
                    break;
                case "customer":
                    $value = $customerId;
                    break;
                case "total_margin":
                    if(empty($customerId)){
                        $value  = 0;
                    }else{
                        $value = $this->financeTransactionHelper->getAccountAmountMarginBalance($customerId);
                    }                    
                    break;
            }
            return $value;
        }catch(\Exception $e){
            return null;
        }
        
    }

    /**
    * Check Expression
    * @param $operator
    * @param  $value
    * @param  $thresholdValue
    * @return {Boolean}
    */
    private function checkExpression($operator, $value, $thresholdValue) {
        try{
            $result = false;
            switch ($operator) {
                case '>':
                    $result =  $value > $thresholdValue;
                    break;
                case '>=':
                    $result =  $value >= $thresholdValue;
                    break;
                case '=':
                    $result =  $value == $thresholdValue;
                    break;
                case '<':
                    $result =  $value < $thresholdValue;
                    break;          
                case '<=':
                    $result =  $value <= $thresholdValue;
                    break;
                case 'in':
                    $result =  in_array($value, $thresholdValue);
                    break;
                case 'not_in':
                    $result =  ! in_array($value, $thresholdValue);
                    break;
                default:
                    $result = false;
            }   
            if($result == null){
                $result = false;
            }
            return $result;
        }catch(\Exception $e){
            $this->writeLog($e);
            return false;            
        }
    }

    /**
     * Check payment method model
     *
     * @param \Magento\Payment\Model\MethodInterface $method
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return bool
     */
    protected function _canUseMethod($method, \Magento\Quote\Api\Data\CartInterface $quote)
    {
        return $this->methodSpecificationFactory->create(
            [
                AbstractMethod::CHECK_USE_CHECKOUT,
                AbstractMethod::CHECK_USE_FOR_COUNTRY,
                AbstractMethod::CHECK_USE_FOR_CURRENCY,
                AbstractMethod::CHECK_ORDER_TOTAL_MIN_MAX,
            ]
        )->isApplicable(
            $method,
            $quote
        );
    }

    /**
     * Get payment method list.
     *
     * @return \Magento\Payment\Api\PaymentMethodListInterface
     */
    private function getPaymentMethodList()
    {
        if ($this->paymentMethodList === null) {
            $this->paymentMethodList = ObjectManager::getInstance()->get(
                \Magento\Payment\Api\PaymentMethodListInterface::class
            );
        }
        return $this->paymentMethodList;
    }

    /**
     * Get payment method instance factory.
     *
     * @return \Magento\Payment\Model\Method\InstanceFactory
     */
    private function getPaymentMethodInstanceFactory()
    {
        if ($this->paymentMethodInstanceFactory === null) {
            $this->paymentMethodInstanceFactory = ObjectManager::getInstance()->get(
                \Magento\Payment\Model\Method\InstanceFactory::class
            );
        }
        return $this->paymentMethodInstanceFactory;
    }

    /**
	* @param $info
	* @param $type  [error, warning, info]
	* @return 
	*/
	private function writeLog($info, $type = "info") {
		$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/payment_methods_filter.log');
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
