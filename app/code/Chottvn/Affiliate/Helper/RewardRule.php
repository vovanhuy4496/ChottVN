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
use Chottvn\Affiliate\Model\RewardRuleFactory;
use Chottvn\Affiliate\Model\LevelRuleFactory;

/**
 * Class Account
 * @package Chottvn\Affiliate\Helper
 */
class RewardRule extends AbstractHelper
{
  /**
   * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
   */
  protected $timezone;

  /**
   * @var Magento\Customer\Model\Logger
   */
  protected $logger;

  /**
   * @varChottvn\Affiliate\Model\RewardRuleFactory
   */
  protected $rewardRuleFactory;

  /**
   * @varChottvn\Affiliate\Model\LevelRuleFactory
   */
  protected $levelRuleFactory;

  public function __construct(
    Context $context,
    \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
    Logger $logger,
    RewardRuleFactory $rewardRuleFactory,
    LevelRuleFactory $levelRuleFactory
  ) {
    parent::__construct($context);
    $this->timezone = $timezone;
    $this->logger = $logger;
    $this->rewardRuleFactory = $rewardRuleFactory;
    $this->levelRuleFactory = $levelRuleFactory;
  }

  /**
   * Get RewardRuleCollections
   * @var {Datetime} checkDatetime
   * @return Collection
   */
  public function getRewardRuleCollection($checkDatetime = null)
  {
    try{
      if(empty($checkDatetime)){
        $checkDatetime = new \DateTime();
      }
      $checkDatetimeSQL = $this->timezone->date($checkDatetime)->format('Y-m-d H:i:s');
      $collection = $this->rewardRuleFactory->create()->getCollection()->filterNotDeleted()->filterActive()
        ->addFieldToFilter('start_date', ['lteq' => $checkDatetimeSQL])
        ->addFieldToFilter('end_date',
          [
            ['gteq' => $checkDatetimeSQL],
            ['null' => true]
          ]
         );

      return $collection;
    }catch(\Exception $e){
      $this->writeLog($e);
      return null;
    }

  }
  /* Sort by brand name
  public function getRewardRuleCollection()
  {
    $now = new \DateTime();
    $collection = $this->rewardRuleFactory->create()->getCollection();
    $collection->getSelect()->joinLeft(
            ['brand' => 'ves_brand'],
            'main_table.product_brand_id = brand.brand_id',
            ["brand.name AS product_brand_name"]
        );
    $collection->filterNotDeleted()->filterActive()
      ->addFieldToFilter('start_date', ['lteq' => $now->format('Y-m-d H:i:s')])
      ->addFieldToFilter('end_date',
        [
          ['gteq' => $now->format('Y-m-d H:i:s')],
          ['null' => true]
        ]
       );
    $collection->getSelect()->order('brand.name','ASC');

    return $collection;
  }
  */

  /**
   * Get RewardRules Active
   * @return {Collection}
   */
  public function getRewardRules()
  {
    $rewardRules = $this->getRewardRuleCollection();

    return $rewardRules->load();
  }


  /**
   * Get RewardRules Available for AffilateLevel
   * @return {Collection}
   */
  public function getRewardRulesAvailableForAffiliateLevel($affiliateLevel)
  {
    $rewardRules = $this->getRewardRuleCollection();
    $rewardRules->addFieldToFilter('affiliate_level', $affiliateLevel);

    return $rewardRules->load();
  }

  /**
   * Get RewardRules Available for AffilateLevel
   * @return {Collection}
   */
  public function getRewardRulesAvailableForOrder($affiliateLevel, $orderedAt)
  {
    $rewardRules = $this->getRewardRuleCollection($orderedAt);
    $rewardRules->addFieldToFilter('affiliate_level', $affiliateLevel);

    return $rewardRules->load();
  }

  /**
   * Get RewardRules
   * @return {Collection}
   */
  public function getRewardRulesGroupByAffiliateLevel()
  {
    $rewardRules = $this->getRewardRuleCollection();
    $rewardRulesByAL = [];
    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    $affiliateLevelSource = $objectManager->create('Chottvn\Affiliate\Model\Customer\Attribute\Source\AffiliateLevel');
    $productBrandFactory = $objectManager->create('Ves\Brand\Model\ResourceModel\Brand\CollectionFactory');

    $affiliateLevelOptions = $affiliateLevelSource->getAllOptions();
    $productBrands = $productBrandFactory->create()
      ->addFieldToFilter('status', '1');
    $productBrands->getSelect()->order('name','ASC');

    foreach ($affiliateLevelOptions as $alo) {
      $affiliateLevel = $alo["value"];
      $data = [];
      $data["product_brands"] = $this->filterRewardRulesOfAffiliateLevel($affiliateLevel, $rewardRules, $productBrands);

      // Phuoc add 20200916 - Get level rule descrition
      $levelRule = $this->levelRuleFactory->create()->getCollection()
        ->addFieldToFilter('affiliate_level', $affiliateLevel)
        ->getLastItem();
      $data['level_description'] = $levelRule->getData('description');

      $rewardRulesByAL[$affiliateLevel] = $data;
    }

    return $rewardRulesByAL;
  }

  private function filterRewardRulesOfAffiliateLevel($affiliateLevel, $rewardRules, $productBrands)
  {
    $data = [];

    foreach ($productBrands as $pb) {
      $productBrandId = $pb->getId();
      $rules = [];
      foreach ($rewardRules as $rewardRule) {
        if (
          $rewardRule->getProductBrandId() == $productBrandId
          && $rewardRule->getAffiliateLevel() == $affiliateLevel
        ) {
          $rules[] = $rewardRule->toArray();
        }
      }
      $data[$productBrandId] = $rules;
    }
    return $data;
  }

  public function getProductKindCodeFromId($productKindId){
    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    $productKindSource = $objectManager->create('Chottvn\Affiliate\Model\Customer\Attribute\Source\ProductKind');
    return $productKindSource->getCodeFromId($productKindId);
  }

  public function getStatisticDateRangeForLatestMonth($months, $cutDate = null){
    $months--;
    $startDate = date('Y-m-01', strtotime(date('Y-m-d H:i:s') . " - $months months"));
    if (! empty($cutDate)){
      $cutDateBeginningOfMonth = date('Y-m-01', strtotime($cutDate) );
      if ($startDate < $cutDateBeginningOfMonth){
        $startDate = $cutDateBeginningOfMonth;
      }
    }    
    $endDate = date('Y-m-d', strtotime(date('Y-m-d H:i:s')));
      //$endDate = date('Y-m-d', strtotime(date('Y-m-d H:i:s')." + 1 days" ));
    return [
        "start_date" => $startDate,
        "end_date" => $endDate
      ];
  }

  public function getStaticMonthRangeStr($dateRange){
    $startMonth = date('m/Y', strtotime($dateRange["start_date"]) );
    $endMonth = date('m/Y', strtotime($dateRange["end_date"]) );
    if($startMonth != $endMonth){
      $str = __("month")." ".$startMonth." - ".__("month")." ".$endMonth;
    }else{
      $str = $startMonth;
    }

    return $str;
  }

  public function getStatisticAffiliateRevenue($customerId, $dateRange = [])
  {
    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    $connection = $objectManager->get('\Magento\Framework\App\ResourceConnection');
    $conn = $connection->getConnection();



    /*$sqlQuery = "SELECT SUM(affiliate_revenue_amount) AS affiliate_revenue_amount, SUM(affiliate_reward_amount) AS affiliate_reward_amount
              FROM vw_chottvn_affiliate_revenue
              WHERE affiliate_account_id = $customerId
              AND updated_at between '$startDate' AND '$endDate'
              ";*/
    $sqlQuery = "
        SELECT SUM(affiliate_revenue_amount) AS affiliate_revenue_amount
             , SUM(affiliate_reward_amount) AS affiliate_reward_amount
        FROM vw_chottvn_affiliate_statistic_monthly
        WHERE affiliate_account_id = $customerId
          AND order_status IN ('complete', 'finished', 'returned_and_finished', 'replaced_and_finished', 'replaced_and_returned_and_finished')
          AND ABS(TIMESTAMPDIFF(SECOND, updated_at, NOW())) / (24 * 60 * 60) > return_period
        ";
    if(!empty($dateRange)){
      $startDate = $dateRange["start_date"];
      $endDate = $dateRange["end_date"];
      $sqlQuery .= " AND finished_at BETWEEN '$startDate' AND '$endDate 23:59:59' ";
    }

    $binds = [];
    $data  = $conn->fetchRow($sqlQuery, $binds);
    return $data;
  }

  public function getStatisticAffiliateRevenueByBrand($customerId, $dateRange)
  {
    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    $connection = $objectManager->get('\Magento\Framework\App\ResourceConnection');
    $conn = $connection->getConnection();
    $startDate = $dateRange["start_date"];
    $endDate = $dateRange["end_date"];

    /*$sqlQuery = "SELECT product_brand_id, SUM(affiliate_revenue_amount) AS affiliate_revenue_amount, SUM(affiliate_reward_amount) AS affiliate_reward_amount
              FROM vw_chottvn_affiliate_revenue
              WHERE affiliate_account_id = $customerId
              AND updated_at between '$startDate' AND '$endDate'
              GROUP BY product_brand_id
              ";*/
    /*$sqlQuery = "SELECT product_brand_id,
                SUM(affiliate_revenue_amount) AS affiliate_revenue_amount,
                SUM(affiliate_reward_amount) AS affiliate_reward_amount
              FROM vw_chottvn_affiliate_statistic_monthly
              WHERE affiliate_account_id = $customerId
                AND updated_at between '$startDate' AND '$endDate'
                AND order_status IN ('complete', 'finished', 'returned_and_finished')
              GROUP BY product_brand_id
              ";*/
    $sqlQuery = "
        SELECT stats.product_brand_id
             , COALESCE(brand.name, '') AS product_brand_name
             , SUM(stats.affiliate_revenue_amount) AS affiliate_revenue_amount
             , SUM(stats.affiliate_reward_amount) AS affiliate_reward_amount
        FROM vw_chottvn_affiliate_statistic_monthly stats
        LEFT JOIN ves_brand brand ON (brand.brand_id = stats.product_brand_id)
        WHERE stats.affiliate_account_id = $customerId
          AND stats.finished_at BETWEEN '$startDate' AND '$endDate 23:59:59'
          AND stats.order_status IN ('complete', 'finished', 'returned_and_finished', 'replaced_and_finished', 'replaced_and_returned_and_finished')
          AND ABS(TIMESTAMPDIFF(SECOND, stats.updated_at, NOW())) / (24 * 60 * 60) > stats.return_period
        GROUP BY stats.product_brand_id, COALESCE(brand.name, '')
        ORDER BY COALESCE(brand.name, '')
    ";
    $binds = [];
    $data  = $conn->fetchAll($sqlQuery, $binds);
    return $data;
  }


  public function getStatisticAffiliateOrderCount($customerId, $dateRange){
    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    $connection = $objectManager->get('\Magento\Framework\App\ResourceConnection');
    $conn = $connection->getConnection();
    $startDate = $dateRange["start_date"];
    $endDate = $dateRange["end_date"];

    /*$sqlQuery = "SELECT COUNT(DISTINCT order_id) AS count
              FROM vw_chottvn_affiliate_revenue
              WHERE affiliate_account_id = $customerId
              AND order_status IN ('complete', 'finished')
              AND updated_at between '$startDate' AND '$endDate'
              ";*/
    /*$sqlQuery = "SELECT COUNT(DISTINCT order_id) AS count
              FROM vw_chottvn_affiliate_statistic_monthly
              WHERE affiliate_account_id = $customerId
                AND updated_at between '$startDate' AND '$endDate'
                AND order_status IN ('complete','finished')
              ";*/
    $sqlQuery = "
        SELECT COUNT(DISTINCT order_id) AS count
        FROM vw_chottvn_affiliate_statistic_monthly
        WHERE affiliate_account_id = $customerId
          AND finished_at BETWEEN '$startDate' AND '$endDate 23:59:59'
          AND order_status IN ('complete', 'finished')
          AND ABS(TIMESTAMPDIFF(SECOND, updated_at, NOW())) / (24 * 60 * 60) > return_period
    ";
    $binds = [];
    $data  = $conn->fetchRow($sqlQuery, $binds);
    return $data["count"];
  }
  public function getStatisticAffiliateOrderTypeActiveCount($customerId, $dateRange){
    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    $connection = $objectManager->get('\Magento\Framework\App\ResourceConnection');
    $conn = $connection->getConnection();
    $startDate = $dateRange["start_date"];
    $endDate = $dateRange["end_date"];

    /*$sqlQuery = "SELECT COUNT(DISTINCT order_id) AS count
              FROM vw_chottvn_affiliate_revenue
              WHERE affiliate_account_id = $customerId
              AND order_status IN ('complete', 'finished')
              AND updated_at between '$startDate' AND '$endDate'
              AND affiliate_account_id = customer_id
              ";*/
    /*$sqlQuery = "SELECT COUNT(DISTINCT order_id) AS count
              FROM vw_chottvn_affiliate_statistic_monthly
              WHERE affiliate_account_id = $customerId
                AND updated_at between '$startDate' AND '$endDate'
                AND order_status IN ('complete','finished')
                AND affiliate_account_id = customer_id
              ";*/
    $sqlQuery = "
        SELECT COUNT(DISTINCT order_id) AS count
        FROM vw_chottvn_affiliate_statistic_monthly
        WHERE affiliate_account_id = $customerId
          AND finished_at BETWEEN '$startDate' AND '$endDate 23:59:59'
          AND order_status IN ('complete', 'finished')
          AND ABS(TIMESTAMPDIFF(SECOND, updated_at, NOW())) / (24 * 60 * 60) > return_period
          AND affiliate_account_id = customer_id
    ";
    $binds = [];
    $data  = $conn->fetchRow($sqlQuery, $binds);
    return $data["count"];
  }

  public function getStatisticAffiliateOrderTypeActiveOrderIds($customerId, $dateRange){
    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    $connection = $objectManager->get('\Magento\Framework\App\ResourceConnection');
    $conn = $connection->getConnection();
    $startDate = $dateRange["start_date"];
    $endDate = $dateRange["end_date"];

    /*$sqlQuery = "SELECT DISTINCT order_id
              FROM vw_chottvn_affiliate_revenue
              WHERE affiliate_account_id = $customerId
              AND updated_at between '$startDate' AND '$endDate'
              AND affiliate_account_id = customer_id
              ";*/
    $sqlQuery = "
        SELECT DISTINCT order_id
        FROM vw_chottvn_affiliate_statistic_monthly
        WHERE affiliate_account_id = $customerId
          AND finished_at BETWEEN '$startDate' AND '$endDate 23:59:59'
          AND order_status IN ('complete', 'finished')
          AND ABS(TIMESTAMPDIFF(SECOND, updated_at, NOW())) / (24 * 60 * 60) > return_period
          AND affiliate_account_id = customer_id
    ";
    $binds = [];
    $data  = $conn->fetchAll($sqlQuery, $binds);

    $result = array();
    foreach ($data as $d) {
      $result[] = $d['order_id'];
    }
    return $result;
  }

  public function getLatestOrderDate($customerId){
    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    $connection = $objectManager->get('\Magento\Framework\App\ResourceConnection');
    $conn = $connection->getConnection();

    $sqlQuery = "SELECT created_at AS date
              FROM sales_order
              WHERE affiliate_account_id = $customerId
              ORDER BY entity_id DESC
              ";
    $binds = [];
    $data  = $conn->fetchRow($sqlQuery, $binds);
    return $data ? $data["date"] : null;
  }
  public function getLatestOrderDateStr($customerId){
    $date = $this->getLatestOrderDate($customerId);
    return $date ? $this->timezone->date($date)->format('d/m/yy') : null;
  }

  public function getMonthOptionsFromDateRange($dateRange){
    $startDate = $dateRange["start_date"];
    $endDate = $dateRange["end_date"];
    // Reset date
    $startDate = date('Y-m-01', strtotime($startDate));
    $endDate = date('Y-m-01', strtotime($endDate));
    $options = [];
    /* // Sort ASC
    while($startDate <= $endDate)
    {
      $option = [
        "month" => date('Y-m', strtotime($startDate)),
        "month_label" => date('m/Y', strtotime($startDate))
      ];
      $options[] = $option;
      $startDate = date("Y-m-d", strtotime($startDate." +1 month") );
    }*/
    while($startDate <= $endDate)
    {
      $option = [
        "month" => date('Y-m', strtotime($endDate)),
        "month_label" => date('m/Y', strtotime($endDate))
      ];
      $options[] = $option;
      $endDate = date("Y-m-01", strtotime($endDate." -1 month") );
    }
    return $options;
  }

  public function getStatisticAffiliateRevenueByMonth($customerId, $dateRange)
  {
    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    $connection = $objectManager->get('\Magento\Framework\App\ResourceConnection');
    $conn = $connection->getConnection();
    $startDate = $dateRange["start_date"];
    $endDate = $dateRange["end_date"];

    $sqlQuery = "
    SELECT data_root.month, data_root.month_label
         , COALESCE(pending.order_count, 0) AS order_count_pending
         , COALESCE(pending.revenue, 0) AS revenue_pending
         , COALESCE(pending.reward, 0) AS reward_pending
         , COALESCE(finished_count.order_count, 0) AS order_count_finished
         , COALESCE(finished_revenue.revenue, 0) AS revenue_finished
         , COALESCE(finished_revenue.reward, 0) AS reward_finished
         , COALESCE(returned_count.order_count, 0) AS order_count_returned
         , COALESCE(returned_revenue.revenue, 0) AS revenue_returned
         , COALESCE(returned_revenue.reward, 0) AS reward_returned
    FROM (
        SELECT finished_month AS `month`, finished_month_label AS month_label
        FROM vw_chottvn_affiliate_statistic_monthly
        WHERE affiliate_account_id = $customerId
         AND DATE(finished_at) BETWEEN '$startDate' AND '$endDate 23:59:59'
        GROUP BY finished_month, finished_month_label
    ) data_root
    LEFT JOIN (
        SELECT finished_month AS `month`, finished_month_label AS month_label
             , COUNT(DISTINCT order_id) AS order_count
             , SUM(affiliate_revenue_amount) AS revenue
             , SUM(affiliate_reward_amount) AS reward
        FROM vw_chottvn_affiliate_statistic_monthly
        WHERE affiliate_account_id = $customerId
          AND finished_at BETWEEN '$startDate' AND '$endDate 23:59:59'
          AND ( order_status IN ('pending', 'processing', 'packaging', 'delivery') )
        GROUP BY finished_month, finished_month_label
    ) pending ON (data_root.month = pending.month)
    LEFT JOIN (
        SELECT finished_month AS `month`, finished_month_label AS month_label
             , COUNT(DISTINCT order_id) AS order_count
        FROM vw_chottvn_affiliate_statistic_monthly
        WHERE affiliate_account_id = $customerId
          AND finished_at BETWEEN '$startDate' AND '$endDate 23:59:59'
          AND order_status IN ('complete','finished')
        GROUP BY finished_month, finished_month_label
    ) finished_count ON (data_root.month = finished_count.month)
    LEFT JOIN (
        SELECT finished_month AS `month`, finished_month_label AS month_label
             , SUM(affiliate_revenue_amount) AS revenue
             , SUM(affiliate_reward_amount) AS reward
        FROM vw_chottvn_affiliate_statistic_monthly
        WHERE affiliate_account_id = $customerId
        AND finished_at BETWEEN '$startDate' AND '$endDate 23:59:59'
        AND order_status IN ('complete', 'finished', 'returned_and_finished', 'replaced_and_finished', 'replaced_and_returned_and_finished')
        GROUP BY finished_month, finished_month_label
    ) finished_revenue ON (data_root.month = finished_revenue.month)
    LEFT JOIN (
        SELECT finished_month AS `month`, finished_month_label AS month_label
             , COUNT(DISTINCT order_id) AS order_count
        FROM vw_chottvn_affiliate_statistic_monthly
        WHERE affiliate_account_id = $customerId
          AND finished_at BETWEEN '$startDate' AND '$endDate 23:59:59'
          AND ( order_status IN ('canceled','returned','returned_and_finished','replaced','replaced_and_finished','replaced_and_returned','replaced_and_returned_and_finished') )
        GROUP BY finished_month, finished_month_label
    ) returned_count ON (data_root.month = returned_count.month)
    LEFT JOIN (
        SELECT finished_month AS `month`, finished_month_label AS month_label
             , SUM(
    					     CASE
    						       WHEN order_status = 'canceled' THEN affiliate_revenue_amount
    						       WHEN order_status = 'returned' THEN affiliate_revenue_amount
    						       WHEN order_status = 'returned_and_finished' THEN affiliate_revenue_refunded_amount
    						       WHEN order_status = 'replaced' THEN affiliate_revenue_amount
    						       WHEN order_status = 'replaced_and_finished' THEN affiliate_revenue_refunded_amount
    						       WHEN order_status = 'replaced_and_returned' THEN affiliate_revenue_amount
    						       WHEN order_status = 'replaced_and_returned_and_finished' THEN affiliate_revenue_refunded_amount
                       ELSE affiliate_revenue_amount
    					     END
    				   ) AS revenue
             , SUM(
    					     CASE
    						       WHEN order_status = 'canceled' THEN affiliate_reward_amount
    						       WHEN order_status = 'returned' THEN affiliate_reward_amount
    						       WHEN order_status = 'returned_and_finished' THEN affiliate_reward_refunded_amount
    						       WHEN order_status = 'replaced' THEN affiliate_reward_amount
    						       WHEN order_status = 'replaced_and_finished' THEN affiliate_reward_refunded_amount
    						       WHEN order_status = 'replaced_and_returned' THEN affiliate_reward_amount
    						       WHEN order_status = 'replaced_and_returned_and_finished' THEN affiliate_reward_refunded_amount
                       ELSE affiliate_reward_amount
    					     END
    				   ) AS reward
        FROM vw_chottvn_affiliate_statistic_monthly
        WHERE affiliate_account_id = $customerId
          AND finished_at BETWEEN '$startDate' AND '$endDate 23:59:59'
          AND ( order_status IN ('canceled','returned','returned_and_finished','replaced','replaced_and_finished','replaced_and_returned','replaced_and_returned_and_finished') )
        GROUP BY finished_month, finished_month_label
    ) returned_revenue ON (data_root.month = returned_revenue.month)
    ORDER BY month ASC
    ;
    ";
    $binds = [];
    $items  = $conn->fetchAll($sqlQuery, $binds);
    $data = [];
    foreach ($items as $item) {
      $monthKey = $item["month"];
      $data[$monthKey] = $item;
     }
    return $data;
  }




  public function applyRewardRule($rewardRule, $orderItems, $affiliateLevel){
    $rewardLevel = $rewardRule->getRewardLevel();
    $productKind = $rewardRule->getProductKind();
    $productBrandId = $rewardRule->getProductBrandId();
    $conditions = json_decode($rewardRule->getConditions(), true);
    $conditionItems = $conditions["items"];
    $conditionAggType = $conditions["agg_type"];

    switch ($rewardLevel) {
      case 'per_item':
        foreach ($orderItems as $orderItem) {
          $oiproductPrice = $orderItem->getPrice();
          $oiProductBrandId = $orderItem->getProductBrandId();
          $oiProductKind = $orderItem->getProductKind();
          $this->writeLog("OrderItem: ".$orderItem->getId());
          //$this->writeLog("-Brand: ".$oiProductBrandId."-".$productBrandId);
          //$this->writeLog("-Kind: ".$oiProductKind."-".$productKind);
          if($oiproductPrice > 0 &&  $oiProductBrandId == $productBrandId
            && $oiProductKind == $productKind
          ){
            $qtyOrdered = $orderItem->getQtyOrdered();
            $qtyRefunded = $orderItem->getQtyRefuned();
            $qty = $qtyOrdered -  $qtyRefunded;
            $price = $orderItem->getPrice();
            // Get Amount by ConditionItems
            $amountValues = [];
            foreach ($conditionItems as $ci) {
              $ciKind = $ci["kind"];
              $ciValue = $ci["value"];
              $rewardAmountItem = 0;
              //$amountBase = 0;
              switch ($ciKind) {
                case 'amount':
                  $rewardAmountItem = $ciValue;
                  break;
                case 'percent':
                  $rewardAmountItem = ($price * $ciValue / 100);
                  break;
              }
              $amountValues[]= $rewardAmountItem;
            }
            // Aggregate amount
            $aggRewardAmountItem = $this->getAggValueFromArray($conditionAggType, $amountValues);
            $rewardAmount = $aggRewardAmountItem * $qty;

            // Update Affiliate Info to Order Item
            $rewardRuleIds = array();
            $rewardRuleIds[] = intval($rewardRule->getId());
            /*
            // >>> Use model
            //$orderItem->setAffiliateLevel($affiliateLevel);
            $orderItem->setAffiliateAmountItem($aggRewardAmountItem);
            $orderItem->setBaseAffiliateAmountItem($aggRewardAmountItem); //Will Update logic
            $orderItem->setAffiliateAmount($rewardAmount);
            $orderItem->setBaseAffiliateAmount($rewardAmount); //Will Update logic later

            $orderItem->setChottvnAffiliateRewardRuleIds(json_encode($rewardRuleIds)) ;
            $this->writeLog(">>>".$orderItem->getId()." - ".$affiliateLevel.' '.$rewardAmount." - ".json_encode($rewardRuleIds));
            $orderItem->setUpdatedAt($orderItem->getUpdatedAt());
            $orderItem->save();
            */

            // >>> Use SQL
            $data = [
              "affiliate_level" => $affiliateLevel,
              "affiliate_amount_item" => $aggRewardAmountItem,
              "base_affiliate_amount_item" => $aggRewardAmountItem,
              "affiliate_amount" => $rewardAmount,
              "base_affiliate_amount" => $rewardAmount,
              "chottvn_affiliate_reward_rule_ids" => json_encode($rewardRuleIds)
            ];
            $this->updateOrderItem($orderItem->getId(), $data, $orderItem->getUpdatedAt());
          }


        }

        break;
      case 'all_items':
        $orderItemGroup = [];
        $totalAmount =  0;
        $totalQty  = 0;
        foreach ($orderItems as $orderItem) {
          $oiproductPrice = $orderItem->getPrice();
          $oiProductBrandId = $orderItem->getProductBrandId();
          $oiProductKind = $orderItem->getProductKind();
          if($oiproductPrice > 0 && $oiProductBrandId == $productBrandId
            && $oiProductKind == $productKind
          ){
            $orderItemGroup[] = $orderItem;
            // Amount
            $qtyOrdered = $orderItem->getQtyOrdered();
            $qtyRefunded = $orderItem->getQtyRefuned();
            $qty = $qtyOrdered -  $qtyRefunded;
            $price = $orderItem->getPrice();
            $amount = $qty * $price;
            $totalAmount += $amount;
            $totalQty += $qty;
          }
        }
        if(sizeof($orderItemGroup) > 0){
          // Get Amount by ConditionItems
          $amountValues = [];
          foreach ($conditionItems as $ci) {
            $ciKind = $ci["kind"];
            $ciValue = $ci["value"];
            $rewardAmountTotal = 0;
            //$amountBase = 0;
            switch ($ciKind) {
              case 'amount':
                $rewardAmountTotal = $ciValue;
                break;
              case 'percent':
                $rewardAmountTotal = $totalAmount * ($ciValue / 100);
                break;
            }
            $amountValues[]= $rewardAmountTotal;
          }
          // Aggregate amount
          $aggAmount = $this->getAggValueFromArray($conditionAggType, $amountValues);
          $rewardAmountPerItem = $aggAmount / $totalQty;
          // Update Reward amount for order items
          foreach ($orderItemGroup as $orderItem) {
            // Amount
            $qtyOrdered = $orderItem->getQtyOrdered();
            $qtyRefunded = $orderItem->getQtyRefuned();
            $qty = $qtyOrdered -  $qtyRefunded;
            if($qty != 0){
              $rewardAmount = $rewardAmountPerItem * $qty;
            }else{
              $rewardAmount = 0;
            }


            // Update Affiliate Info to Order Item
            $rewardRuleIds = array();
            $rewardRuleIds[] = intval($rewardRule->getId());
            /*
            // >>> Use Model --> not keep updated_at
            //$orderItem->setAffiliateLevel($affiliateLevel);
            $orderItem->setAffiliateAmountPerItem($rewardAmountPerItem);
            $orderItem->setBaseAffiliateAmountPerItem($rewardAmountPerItem); //Will Update logic later
            $orderItem->setAffiliateAmount($rewardAmount);
            $orderItem->setBaseAffiliateAmount($rewardAmount); //Will Update logic later
            $orderItem->setChottvnAffiliateRewardRuleIds(json_encode($rewardRuleIds)) ;
            $this->writeLog("OrdItem: ".$orderItem->getId()." - ".$affiliateLevel.' '.$rewardAmount." - ".json_encode($rewardRuleIds));
            $orderItem->setUpdatedAt($orderItem->getUpdatedAt());
            $orderItem->save();
            */

            // >>> Use SQL
            $data = [
              "affiliate_level" => $affiliateLevel,
              "affiliate_amount_item" => $rewardAmountPerItem,
              "base_affiliate_amount_item" => $rewardAmountPerItem,
              "affiliate_amount" => $rewardAmount,
              "base_affiliate_amount" => $rewardAmount,
              "chottvn_affiliate_reward_rule_ids" => json_encode($rewardRuleIds)
            ];
            $this->updateOrderItem($orderItem->getId(), $data, $orderItem->getUpdatedAt());
          }
        }
        break;
    }
  }

  public function updateOrderItem($orderItemId, $data, $updatedAt){
    try{
      $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
      $connection = $objectManager->get('\Magento\Framework\App\ResourceConnection');
      $conn = $connection->getConnection();
      if (!empty($updatedAt)){
        $data['updated_at'] = $updatedAt;
      }
      $where = ['item_id = ?' => (int)$orderItemId];
      $tableName = $connection->getTableName("sales_order_item");
      $updatedRows=$conn->update($tableName, $data, $where);

    }catch(\Exception $e){
      $this->writeLog($e);
    }
  }

  public function getAggValueFromArray($aggType, $values){
    $this->writeLog("Aggs: ".$aggType." --- ".json_encode($values));
    $value = 0;
    switch ($aggType) {
      case 'max':
        $value = max($values);
        break;
      case 'min':
        $value = min($values);
        break;
    }
    return $value;
  }

  /**
   * @param $info
   * @param $type  [error, warning, info]
   * @return
   */
  private function writeLog($info, $type = "info") {
      $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/affiliate_reward.log');
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
