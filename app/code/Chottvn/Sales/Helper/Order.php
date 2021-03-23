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

namespace Chottvn\Sales\Helper;

use Chottvn\Affiliate\Model\Log;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * Class Order
 * @package Chottvn\Sales\Helper
 */
class Order extends AbstractHelper
{
	  /**
   * @var OrderRepositoryInterface
   */
  protected $orderRepository;


	 public function __construct(
    Context $context,
    OrderRepositoryInterface $orderRepository,    
    \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
  ) {
    parent::__construct($context);
    $this->orderRepository = $orderRepository;
    $this->timezone = $timezone;
  }


  public function getOrdersComplete()
  {
    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    $connection = $objectManager->get('\Magento\Framework\App\ResourceConnection');
    $conn = $connection->getConnection();
    
    $sqlQuery = "
        SELECT *
        FROM vw_sales_order_complete
      ";

    $binds = [];
    $data  = $conn->fetchAll($sqlQuery, $binds);
    return $data;
  }

  public function getOrderFinishedAt($orderId){
    try{
      $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
      $connection = $objectManager->get('\Magento\Framework\App\ResourceConnection');
      $conn = $connection->getConnection();
      
      $sqlQuery = "
        SELECT *
        FROM vw_sales_order_info
        WHERE order_id = $orderId
      ";

      $binds = [];
      $data  = $conn->fetchRow($sqlQuery, $binds);
      return $data["order_finished_at"];
    }catch(\Exception $e){
      $this->writeLog($e);
      return null;
    }
  }

  public function getOrdersNeedRecalculateAffReward($affiliateAccountId, $dateCheckpoint)
  {
    try{
      $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
      $connection = $objectManager->get('\Magento\Framework\App\ResourceConnection');
      $conn = $connection->getConnection();
      
      $sqlQuery = "
        SELECT entity_id AS order_id
        FROM sales_order
        WHERE affiliate_account_id = $affiliateAccountId  
          AND created_at > '$dateCheckpoint' 
      ";

      $binds = [];
      $data  = $conn->fetchAll($sqlQuery, $binds);
      return $data;
    }catch(\Exception $e){
      $this->writeLog($e);
      return [];
    }    
  }

  public function getOrderObject($orderId){
  	try{
      $order = $this->orderRepository->get($orderId);
      return $order;
    }catch(\Exception $e){
      return null;
    }
  }
  public function updateOrderStatus($orderId, $status, $updatedAt){
  	try{
      $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
      $connection = $objectManager->get('\Magento\Framework\App\ResourceConnection');
      $conn = $connection->getConnection();
      
      // Update Sale Order
      $data = [
        "status" => $status
      ]; 
      if (!empty($updatedAt)){
        $data['updated_at'] = $updatedAt;
      }
      $where = ['entity_id = ?' => (int)$orderId];
      $tableName = $connection->getTableName("sales_order");
      $updatedRows=$conn->update($tableName, $data, $where);
      /*
      $query = "
        UPDATE sales_order
        SET status = '$status'
        WHERE entity_id = $orderId
      ";
      $conn->query($query);
      */

      // Update Sale Order Grid
      /*
      $data2 = [
        "status" => $status
      ];
      $where = ['entity_id = ?' => (int)$orderId];
      $tableName = $connection->getTableName("sales_order_grid");
      $updatedRows=$conn->update($tableName, $data2, $where);      
      */
      
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
      $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/sales_helper.log');
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