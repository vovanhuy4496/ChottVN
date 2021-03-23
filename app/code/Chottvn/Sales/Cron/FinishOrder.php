<?php
declare(strict_types=1);

namespace Chottvn\Sales\Cron;

use \Chottvn\Sales\Helper\Order as SalesOrderHelper;
use Chottvn\Affiliate\Helper\Account as AffiliateAccountHelper;
use Chottvn\Affiliate\Helper\RewardRule as AffiliateRewardRuleHelper;
use Chottvn\Affiliate\Helper\LevelRule as AffiliateLevelRuleHelper;

class FinishOrder
{

    private $SalesOrderHelper;

    protected $logger;

    /**
     * @var \Chottvn\Affiliate\Helper\Account
     */
    private $affiliateAccountHelper;
    

    /**
     * @var \Chottvn\Affiliate\Helper\RewardRule
     */
    private $affiliateRewardRuleHelper;

    /**
     * Constructor
     *
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(\Psr\Log\LoggerInterface $logger,
        SalesOrderHelper $salesOrderHelper,
        AffiliateAccountHelper $affiliateAccountHelper,
        AffiliateRewardRuleHelper $affiliateRewardRuleHelper,
        AffiliateLevelRuleHelper $affiliateLevelRuleHelper
    )
    {
        $this->logger = $logger;
        $this->salesOrderHelper = $salesOrderHelper;
        $this->affiliateAccountHelper = $affiliateAccountHelper;
        $this->affiliateRewardRuleHelper = $affiliateRewardRuleHelper;
        $this->affiliateLevelRuleHelper = $affiliateLevelRuleHelper;
    }

    /**
     * Execute the cron
     *
     * @return void
     */
    public function execute()
    {
        //$this->logger->addInfo("Cronjob UpdateOrderStatus is executed.");
        // Get list Order 'complete'
        try{
            $ordersComplete = $this->salesOrderHelper->getOrdersComplete();
            $this->writeLog("#### Finish Orders: ");            
            foreach ($ordersComplete as $order) {                
                $orderId = $order["order_id"];
                $orderObject = $this->salesOrderHelper->getOrderObject($orderId);
                if(!empty($orderObject)){
                    $orderStatus = $orderObject->getStatus();
                    $this->writeLog("Ord #".$orderId." - ".$orderStatus);
                    if(in_array($orderStatus, ["complete"])){
                        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                        // Update Order status by model
                          /*
                          $orderObject->setStatus("finished");
                          $orderObject->save();
                          */
                  
                        // Update Order status & Rule manually
                        //-- Update Order Status
                        $orderStatusNew = "finished";
                        $this->salesOrderHelper->updateOrderStatus($orderId,"finished", $orderObject->getUpdatedAt());
                        // -- Save Order Status Log
                        $logValue = [
                            "action" => "Chottvn_Sales_CronFinishOrder"
                        ];
                        $timefc = $objectManager->create('\Magento\Framework\Stdlib\DateTime\DateTime');
                        $currentDate = $timefc->gmtDate();
                        $log = $objectManager->create('\Chottvn\Sales\Model\Log');
                            $log->setOrderId($orderId);
                            $log->setOrderStatus($orderStatusNew);
                            $log->setValue(json_encode($logValue));
                            $log->setValueOld($orderStatus);
                            $log->setCreatedAt($currentDate);
                            $log->save();
                        // Update Affiliate Level of Account
                        $affiliateAccountId =  $orderObject->getData('affiliate_account_id');
                        $this->writeLog("AffAccID: ".$affiliateAccountId);
                        if(!empty($affiliateAccountId)){   
                          $affiliateLevelAccount = $this->affiliateAccountHelper->getAffiliateLevel($affiliateAccountId);      
                          // Update Order affiliate level if account level change -- deprecated
                          /*$affiliateLevelOrder = $orderObject->getData("affiliate_level");                            
                          if($affiliateLevelOrder != $affiliateLevelAccount){                            
                          }*/
                          // Recalculate Reward
                          $affiliateLevel = $affiliateLevelAccount;
                          $orderedAt = $orderObject->getCreatedAt();
                          $orderFinishedAt = $order["order_finished_at"];
                          $rewardRules = $this->affiliateRewardRuleHelper->getRewardRulesAvailableForOrder($affiliateLevel, $orderFinishedAt);
                          $orderItems = $orderObject->getAllItems();
                          foreach ($rewardRules as $rewardRule) { 
                            $this->affiliateRewardRuleHelper->applyRewardRule($rewardRule, $orderItems, $affiliateLevel);
                          }
                            
                          // Check and Apply Level Rule of Account
                          $this->affiliateLevelRuleHelper->checkAndApplyLevelRule($affiliateAccountId);
                          // Then check level if change >> Update level for orders - deprecated
                          /*
                          $affiliateLevelAccountNew = $this->affiliateAccountHelper->getAffiliateLevel($affiliateAccountId);   
                          if ($affiliateLevelAccount != $affiliateLevelAccountNew){                            
                            $ordersNeedRecalculate = $this->getOrdersNeedRecalculateAffReward($affiliateAccountId, $orderFinishedAt);
                            $this->writeLog("Recal - finsihed: ".$orderFinishedAt." - ids: ".json_encode($ordersNeedRecalculate));
                            $affiliateLevel = $affiliateLevelAccount;
                            foreach ($ordersNeedRecalculate as $orderNeedRecalculate) {
                              $orderedAt = $orderNeedRecalculate->getCreatedAt();
                              $rewardRules = $this->affiliateRewardRuleHelper->getRewardRulesAvailableForOrder($affiliateLevel, $orderedAt);
                              $orderItems = $orderNeedRecalculate->getAllItems();
                              foreach ($rewardRules as $rewardRule) { 
                                $this->affiliateRewardRuleHelper->applyRewardRule($rewardRule, $orderItems, $affiliateLevel);
                              }
                            }
                          }
                          */
                        }
                        
                    }
                }
            }
        }catch(\Exception $e){
            $this->writeLog($e);
        }
        
    }

    /**
       * @param $info
       * @param $type  [error, warning, info]
       * @return
       */
    private function writeLog($info, $type = "info") {
      $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/cron_sales_order.log');
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