<?php
/**
 * Copyright © (c) Cho Truc Tuyen 2020 All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Chottvn\Sales\Plugin\Magento\Quote\Model\Quote;

use Chottvn\Sales\Helper\Data as HelperData;

class Item {
    protected $_scopeConfig;
    protected $_request;
    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, \Magento\Framework\Webapi\Rest\Request $request, HelperData $helperData) {
        $this->_scopeConfig = $scopeConfig;
        $this->helperData = $helperData;
        $this->_request = $request;
    }


    /**
     * @param \Magento\Quote\Model\Quote\Item $subject $subject
     * @param  $result
     * @return mixed
     * @throws \Exception
     */
    public function beforeSave(\Magento\Quote\Model\Quote\Item $subject) {
        try{
             
        }catch(\Exception $e){
            $this->writeLog($e);
        }
        return $this;
    }


    /**
     * @param \Magento\Quote\Model\Quote\Item $subject $subject
     * @param  $result
     * @return mixed
     * @throws \Exception
     */
    public function afterSave(\Magento\Quote\Model\Quote\Item $subject, $result, $object) {
        try{     
            $cartPromoOptions =  ['ampromo_cart', 'ampromo_spent'];     
            if ($subject->getPrice() == 0
                && empty($subject->getCartPromoOption()) 
                && !empty($subject->getAppliedRuleIds())) {           
                $ruleIds = explode(",",$subject->getAppliedRuleIds());
                foreach ($ruleIds as $ruleId) {
                    $ruleInfo = $this->helperData->getPromoRuleInfo($ruleId);
                    if(!empty($ruleInfo)){                      
                        $cartPromoOption = $ruleInfo["simple_action"];
                        if (in_array($cartPromoOption, $cartPromoOptions)){
                            $subject->setCartPromoOption($cartPromoOption);    
                        }                        
                    }
                }                               
            }            
        }catch(\Exception $e){
            $this->writeLog($e);
        }
              
        return $result;
    }


    /**
    * @param $info
    * @param $type  [error, warning, info]
    * @return 
    */
    private function writeLog($info, $type = "info") {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/chottvn_sales_quote.log');
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

?>