<?php
/**
 * Copyright © (c) Cho Truc Tuyen 2020 All rights reserved.
 * See COPYING.txt for license details.
 *
 *
 * @category    Chottvn
 * @package     Chottvn_Affiliate
 * 
 */

namespace Chottvn\Affiliate\Plugin\Magento\Sales\Model\ResourceModel;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Chottvn\Affiliate\Helper\Account as AffiliateAccountHelper;
use Chottvn\Affiliate\Helper\RewardRule as AffiliateRewardRuleHelper;
use Chottvn\Affiliate\Helper\LevelRule as AffiliateLevelRuleHelper;
use Chottvn\Sales\Helper\Order as SalesOrderHelper;

class Order {
	protected $_scopeConfig;

	/**
   * @var \Magento\Customer\Api\CustomerRepositoryInterface
   */
	private $customerRepository;
	
	/**
   * @var \Chottvn\Affiliate\Helper\Account
   */
	private $affiliateAccountHelper;

	/**
    * @var \Chottvn\Sales\Helper\Order
    */
    private $salesOrderHelper;
	

	/**
   * @var \Chottvn\Affiliate\Helper\RewardRule
   */
	private $affiliateRewardRuleHelper;
	

	public function __construct(
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
    CustomerRepositoryInterface $customerRepository,
    AffiliateAccountHelper $affiliateAccountHelper,
    AffiliateRewardRuleHelper $affiliateRewardRuleHelper,
    AffiliateLevelRuleHelper $affiliateLevelRuleHelper,
    SalesOrderHelper $salesOrderHelper

	) { 
		$this->_scopeConfig = $scopeConfig;
    $this->customerRepository = $customerRepository;
    $this->affiliateAccountHelper = $affiliateAccountHelper;
    $this->affiliateRewardRuleHelper = $affiliateRewardRuleHelper;
    $this->affiliateLevelRuleHelper = $affiliateLevelRuleHelper;
    $this->salesOrderHelper = $salesOrderHelper;
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
		try{
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();

	    $oldStatus = $object->getOrigData('status');
			$newStatus = $object->getData('status');
	  
      //$this->writeLog('Chottvn_Affiliate: '.$oldStatus." - ".$newStatus);  

			$affiliateAccountId = $object->getData("affiliate_account_id");			
			$this->writeLog("###### Order: ".$object->getEntityId());  
			$this->writeLog("Status: ".$oldStatus." - ".$newStatus);			
						
			if (!empty($affiliateAccountId) 
				// && ($newStatus == "pending")
				//&& ($newStatus = "processing"  || $newStatus = "canceled")
			){
				$orderItems = $object->getAllItems();
				// Get order affiliate_level				
				
				// >> Get affiliate_level from order items				
				if(sizeof($orderItems) > 0) {
					$firstItem = $orderItems[0];
					$affiliateLevel = $firstItem->getAffiliateLevel();
				}else{
					$affiliateLevel = "";
				}	
					/*
					// >> Get affiliate_level from order
					$affiliateLevel = $object->getData('affiliate_level');					
					if ($affiliateLevel == null){
						$affiliateLevel = "";
					}
					*/

				if ($newStatus == "pending"){
					$this->writeLog("ApplyReward - Acc: ".$affiliateAccountId." -- ".$affiliateLevel);  
					/*
					// Check and update Order Items affiliate level if change >> update on 'applyRewardRule'					
					$affiliateLevelAccount = $this->affiliateAccountHelper->getAffiliateLevel($affiliateAccountId);	
					$affiliateLevel  = $affiliateLevelAccount;
						if($affiliateLevelAccount != $affiliateLevel){						
							$data = [
	              "affiliate_level"  => $affiliateLevel
	            ];
	            foreach ($orderItems as $orderItem) {
	            	$this->updateOrderItem($orderItem->getId(), $data, $orderItem->getUpdatedAt());
	            }            
						}
					*/
					// Apply Reward Rules to Order 
					$orderedAt = $object->getCreatedAt();
					$rewardRules = $this->affiliateRewardRuleHelper->getRewardRulesAvailableForOrder($affiliateLevel, $orderedAt);
						//$rewardRules = $this->affiliateRewardRuleHelper->getRewardRulesAvailableForAffiliateLevel($affiliateLevel);
					foreach ($rewardRules as $rewardRule) {	
						$this->affiliateRewardRuleHelper->applyRewardRule($rewardRule, $orderItems, $affiliateLevel);
					}
				}
				if (in_array($newStatus, ["finished", "returned_and_finished"])){
					$this->writeLog("ApplyLevel- Acc: ".$affiliateAccountId);
					// Recalculate AffReward					
          if(sizeof($orderItems) > 0) {
              $firstItem = $orderItems[0];
              $affiliateLevel = $firstItem->getAffiliateLevel();
          }else{
              $affiliateLevel = "";
          } 
          $orderFinishedAt = $this->salesOrderHelper->getOrderFinishedAt($object->getId());
          
          $rewardRules = $this->affiliateRewardRuleHelper->getRewardRulesAvailableForOrder($affiliateLevel, $orderFinishedAt);
          if (!empty($rewardRules)){
              foreach ($rewardRules as $rewardRule) {                     
                  $this->affiliateRewardRuleHelper->applyRewardRule($rewardRule, $orderItems, $affiliateLevel);
              }
          }
					// Check Level
					$this->affiliateLevelRuleHelper->checkAndApplyLevelRule($affiliateAccountId);
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
      $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/affiliate_sales_order.log');
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

?>