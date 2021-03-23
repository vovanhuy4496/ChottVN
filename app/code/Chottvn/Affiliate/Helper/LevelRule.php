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

namespace Chottvn\Affiliate\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Customer\Model\Logger;
use Chottvn\Affiliate\Api\RewardRuleRepositoryInterface;
use Chottvn\Affiliate\Api\LevelRuleRepositoryInterface;
use Chottvn\Affiliate\Helper\Account as AffiliateAccountHelper;
use Chottvn\Affiliate\Helper\RewardRule as AffiliateRewardRuleHelper;
use Chottvn\Affiliate\Helper\Log as AffiliateLogHelper;
use Chottvn\Finance\Helper\Transaction as FinanceTransactionHelper;

/**
 * Class Account
 * @package Chottvn\Affiliate\Helper
 */
class LevelRule extends AbstractHelper
{
  const CONDITION_AMOUNT_UNIT = 1000000;


  /**
   * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
   */
  protected $timezone;

  /**
   * @var Magento\Customer\Model\Logger
   */
  protected $logger;

  /**
   * @var Chottvn\Affiliate\Api\RewardRuleRepositoryInterface
   */
  protected $rewardRuleRepository;

  /**
   * @var Chottvn\Affiliate\Api\LevelRuleRepositoryInterface
   */
  protected $levelRuleRepository;

  public function __construct(
    Context $context,
    \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
    Logger $logger,
    RewardRuleRepositoryInterface $rewardRuleRepository,
    LevelRuleRepositoryInterface $levelRuleRepository,
    AffiliateAccountHelper $affiliateAccountHelper,    
    AffiliateRewardRuleHelper $affiliateRewardRuleHelper,
    AffiliateLogHelper $affiliateLogHelper,
    FinanceTransactionHelper $financeTransactionHelper
  ) {
    parent::__construct($context);
    $this->timezone = $timezone;
    $this->logger = $logger;
    $this->rewardRuleRepository = $rewardRuleRepository;
    $this->levelRuleRepository = $levelRuleRepository;
    $this->affiliateAccountHelper = $affiliateAccountHelper;
    $this->affiliateRewardRuleHelper = $affiliateRewardRuleHelper;
    $this->affiliateLogHelper = $affiliateLogHelper;
    $this->financeTransactionHelper = $financeTransactionHelper;
  }

  public function checkAndApplyLevelRule($customerId){
    try{
      // Get Customer Object        
      $customer = $this->affiliateAccountHelper->getAffiliateAccount($customerId);
      $affiliateLevelAttr = $customer->getCustomAttribute('affiliate_level');
      $affiliateLevel  = "";
        if (!$affiliateLevel) {
            $affiliateLevel = $affiliateLevelAttr->getValue();
      }         
      $this->writeLog("#### Cus#: ".$customerId."  -  level: ".$affiliateLevel);
      // Get level rule and check
      $levelRules = $this->getLevelRulesForAffiliateLevelUpdate($affiliateLevel);
      $newLevel = $affiliateLevel;
      foreach ($levelRules as $rule) {
          //$this->writeLog(json_encode($rule));
        if ($this->checkRule($customer, $rule) ) {
          $newLevel = $rule["affiliate_level"];
          $this->writeLog("New level: ".$newLevel);
          // Set new level
          $customer->setCustomAttribute('affiliate_level', $newLevel);
          $this->affiliateAccountHelper->saveAffiliateAccount($customer);
          break;
        };
      }
      
      if($affiliateLevel !== $newLevel){  
        $this->affiliateLogHelper->saveLogWithResource([
          "account_id" => $customerId,
          "resource_type" => null,
          "resource_id" => null,
          "event" => 'affiliate_level_changed',
          "value" => ['affiliate_level' => $newLevel]
        ]);
      }
    }catch(\Exception $e){
      $this->writeLog($e);
    }
    
  }


  public function getLevelRulesForAffiliateLevelUpdate($affiliatelevel){
    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    $connection = $objectManager->get('\Magento\Framework\App\ResourceConnection');
    $conn = $connection->getConnection();

    $sqlQuery = "
      SELECT * 
      FROM chottvn_affiliate_level_rule
      WHERE start_date <= now() 
        AND (end_date >= now() OR end_date IS null)
        AND affiliate_level > '$affiliatelevel'
      ORDER BY affiliate_level DESC
    ";
    $binds = [];
    $data  = $conn->fetchAll($sqlQuery, $binds);

    return $data;
  }


  /**
  * Check Rule item
  * @param $customer
  * @param  $rule config
  * @param  $statistic data
  * @return {Boolean}
  */
  public function checkRule($customer, $rule){
    try{  
      $ruleResult = false;    
      // Rule
      $conditions = json_decode($rule["conditions"], true);
      $ruleItems = $conditions["items"];
      $ruleAggType = $conditions["agg_type"];
      // Check rule Items
      $ruleItemsResult = array();
      foreach($ruleItems as $ruleItem) {
        $isRuleItemValid = $this->checkRuleItem($customer->getId(), $ruleItem);
        array_push($ruleItemsResult, $isRuleItemValid);     
      }

      // Aggregate Rule Item Results
      $resultLength = sizeof($ruleItemsResult);
      if ($resultLength > 0) {
        $ruleResult = $ruleItemsResult[0];
        if ($resultLength > 1) {
          for ($i = 1; $i < $resultLength; $i++) {
              switch ($ruleAggType) {
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
      //$this->writeLog($e);
      throw $e;
    }
    
  }

  /**
  * Check Rule item
  * @param $ruleItem
  * @param  $purchaseSummary
  * @return {Boolean}
  */
  private function checkRuleItem($customerId, $ruleItem)
  {   
    $indexValue = $this->getStaticIndexValue($customerId, $ruleItem);
    $thresholdValue = (float)$ruleItem['value']  * self::CONDITION_AMOUNT_UNIT;
    //$this->writeLog("AMOUNT: ".$indexValue."  - ".$thresholdValue);
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

  private function getStaticIndexValue($customerId, $ruleItem){
    $code = $ruleItem['code'];
    $kind = $ruleItem["kind"];
    $indexValue  =  0;
    switch ($code) {
      case 'margin':
        $indexValue = $this->getAccountMargin($customerId);
        break;
      case 'revenue':
        $periodConfig = $ruleItem["period"];
        $indexValue = $this->getAccountRevenue($customerId, $periodConfig);
        break;      
    }   
    //$this->writeLog("IndexValue: ".$code." - ".$indexValue);
    return $indexValue;
  }

  private function getAccountMargin($customerId){
    return $this->financeTransactionHelper->getAccountAmountMarginBalance($customerId);
  }

  private function getAccountRevenue($customerId, $periodConfig){
    try{
      $revenue = 0;
      $periodType  = $periodConfig["type"];
      $periodValue = $periodConfig["value"];
      switch ($periodType) {
        case 'months':
          $dateRange = $this->affiliateRewardRuleHelper->getStatisticDateRangeForLatestMonth($periodValue);
          //$this->writeLog(json_encode($dateRange));
          $statistic = $this->affiliateRewardRuleHelper->getStatisticAffiliateRevenue($customerId, $dateRange);
          //$this->writeLog(json_encode($statistic));
          $revenue  = floatval($statistic["affiliate_revenue_amount"]);
          break;
      }
      return $revenue;
    }catch(\Exception $e){
      //$this->writeLog($e);
      throw $e;
    }   
  }



  /**
   * @param $info
   * @param $type  [error, warning, info]
   * @return
   */
  private function writeLog($info, $type = "info") {
      $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/affiliate_level.log');
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