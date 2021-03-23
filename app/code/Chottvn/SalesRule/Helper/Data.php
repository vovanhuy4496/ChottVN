<?php
/**
 * Copyright © (c) chotructuyen.co All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Chottvn\SalesRule\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{

  protected $resourceConnection;
  protected $sendOtpRepo;

  /**
   * @param \Magento\Framework\App\Helper\Context $context
   */
  public function __construct(
    \Magento\Framework\App\ResourceConnection $resourceConnection,
    \Chottvn\Sms\Model\SendOtpRepository $sendOtpRepo,
    \Magento\Framework\App\Helper\Context $context
  ) {
    $this->resourceConnection = $resourceConnection;
    $this->sendOtpRepo = $sendOtpRepo;
    parent::__construct($context);
  }

  /**
  * lay thong tin customer duoc tao trong khoang thoi gian dien ra su kien
  * - customer da active
  * - customer duoc verify phone
  * - customer created_at >= rule_start_data && create_at <= rule_end_data
  */
  public function getCustomerByPhoneAndRule($phone_number, $rule){
    $result = array();

    // need transfer date to date - 7 hours
    $from_date = $rule->getData('from_date').' 00:00:00';
    $from_date = date('Y-m-d H:i:s', strtotime($from_date . " - 7 hours"));
    
    $to_date = $rule->getData('to_date').' 23:59:59';
    $to_date = date('Y-m-d H:i:s', strtotime($to_date . " - 7 hours"));

    // connection
    $connection = $this->resourceConnection->getConnection();
    // query
    $query = "SELECT ce.*, `csv`.`created_at` as verify_phone_created_at FROM customer_entity AS ce
            LEFT JOIN customer_entity_varchar AS cev
            ON cev.entity_id = ce.entity_id
            LEFT JOIN eav_attribute AS ea
            ON ea.attribute_id = cev.attribute_id
            LEFT JOIN chottvn_signinphonenumber_verification AS csv
            ON csv.customer_id = ce.entity_id
            WHERE ea.attribute_code = 'phone_number' 
            AND cev.value = '".$phone_number."' 
            AND csv.verify_status = 1
            AND ce.is_active = 1
            AND csv.created_at BETWEEN '".$from_date."' AND '".$to_date."'";
    $this->writeLog('Query Get Customer: '.$query);
    // get customer
    $result = $connection->fetchAll($query);

    if(isset($result[0])){
      return $result[0];
    }else{
      return array();
    }
  }

  /**
  * sum original_total của tất cả đơn hàng trong sales_order
  * chưa có thông tin chottvn_applied_rule_ids, customer_discount_code, affilate_transaction_id, chottvn_applied_discount_code_rule_id
  * trong thoi gian customer_created_at va salesrule_end_date
  * >= 3.000.000
  */
  public function isAppliedRuleForCustomerPhoneNumber($phone_number, $from_date, $to_date, $applied_amount, $rule_id){
    $result = array();
    $totalAmount = array();
    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

    // connection
    // $connection = $this->resourceConnection->getConnection();
    // query
    // $query = "SELECT SUM(original_total) as total FROM `sales_order`
    //           WHERE chott_customer_phone_number = '".$phone_number."'
    //           AND state = 'complete'
    //           AND chottvn_applied_rule_ids IS NULL
    //           AND customer_discount_code IS NULL
    //           AND chottvn_applied_discount_code_rule_id IS NULL
    //           AND created_at BETWEEN '".$from_date."' AND '".$to_date."'";

    // result
    // $result = $connection->fetchOne($query);

    $customerMembershipHelperCalculator = $objectManager->create('Chottvn\CustomerMembership\Helper\Calculator');
    $conditions = [
                    ["status" => "IN ('complete','finished')"],
                    ["custom" => "o.created_at BETWEEN '".$from_date."' AND '".$to_date."'"],
                    ["custom" => "(FIND_IN_SET('".$rule_id."', o.applied_rule_ids) IS NULL) OR (FIND_IN_SET('".$rule_id."', o.applied_rule_ids) = 0)"]
                  ];

    $totalAmount = $customerMembershipHelperCalculator->getTotalsV2($phone_number, $conditions, 'all');
    // $query = "SELECT `o`.`entity_id` FROM `sales_order` AS `o` 
    //           WHERE (o.chott_customer_phone_number = '".$phone_number."') 
    //           AND (o.status IN ('complete','finished')) 
    //           AND (o.created_at >= '".$from_date."') AND (o.created_at <= '".$to_date."') 
    //           AND ((o.coupon_code IS NULL OR (o.coupon_code NOT LIKE '%CTT%' AND o.coupon_code NOT LIKE '%31%')))";
    // $this->writeLog('Query get Orders to Count Total: '.$query);

    // $result = $connection->fetchAll($query);
    // $result = array_column($result, 'entity_id');

    // if($result){
    //   $query_total = "SELECT COUNT(distinct(oi.order_id)) AS `count`, SUM((oi.qty_ordered - oi.qty_refunded) * oi.base_price) AS `amount` FROM `sales_order_item` AS `oi` WHERE (oi.order_id IN (".implode(',', $result)."))";

    //   $this->writeLog('Query Count Total Orders: '.$query_total);

    //   $result_total = $connection->fetchRow($query_total);
    //   $totalAmount = [            
    //                     'total_orders_amount' => $result_total['amount'],
    //                     'of_placed_orders' => $result_total['count'],
    //                     'average_order_value' => $result_total['count'] ? ($result_total['amount']) / $result_total['count'] : 0,
    //                   ];
    // }

    $this->writeLog('Total Orders Amount: '.$totalAmount['total_orders_amount']);
    $this->writeLog('Applied Amount: '.$applied_amount);
    // thoa dk
    if((int)$totalAmount['total_orders_amount'] >= (int)$applied_amount){
      return true;
    }

    return false;
  }

  public function isRecievedRuleForCustomer($phone_number, $vat_number, $from_date, $to_date){
    $result_phone_number = false;
    $result_vat_number = false;

    // kiem tra dk phone number
    if($phone_number){
      // connection
      $connection = $this->resourceConnection->getConnection();
      // query
      $query = "SELECT entity_id FROM `sales_order`
                WHERE chott_customer_phone_number = '".$phone_number."'
                AND state != 'canceled'
                AND (chottvn_applied_rule_ids IS NOT NULL
                OR customer_discount_code IS NOT NULL
                OR chottvn_applied_discount_code_rule_id IS NOT NULL)
                AND created_at BETWEEN '".$from_date."' AND '".$to_date."'";
      
      $this->writeLog('Query Is Recieved Rule For Customer PHONE NUMER: '.$query);
      // result
      $result_pn = $connection->fetchAll($query);

      // thoa dk
      if($result_pn){
        $result_phone_number = true;
      }
    }
    
    // kiem tra dk vat number
    if($vat_number){
      // connection
      $connection = $this->resourceConnection->getConnection();
      // query
      // by code amasty 
      // parent_entity_type = 1 => table order
      // parent_entity_type = 2 => table qoute
      $query = "SELECT so.* FROM `sales_order` AS so
                LEFT JOIN amasty_order_attribute_entity AS aoae
                ON aoae.parent_id = so.entity_id
                LEFT JOIN amasty_order_attribute_entity_varchar AS aoaev
                ON aoaev.entity_id = aoae.entity_id
                LEFT JOIN eav_attribute AS ea
                ON ea.attribute_id = aoaev.attribute_id
                WHERE aoaev.value = '".$vat_number."'
                AND ea.attribute_code = 'vat_number'
                AND state != 'canceled'
                AND aoae.parent_entity_type = 1
                AND (chottvn_applied_rule_ids IS NOT NULL
                OR customer_discount_code IS NOT NULL
                OR chottvn_applied_discount_code_rule_id IS NOT NULL)
                AND created_at BETWEEN '".$from_date."' AND '".$to_date."'";
      $this->writeLog('Query Is Recieved Rule For Customer VAT NUMBER: '.$query);
      // result
      $result_vn = $connection->fetchAll($query);

      // thoa dk
      if($result_vn){
        $result_vat_number = true;
      }
    }

    if($result_phone_number == false && $result_vat_number == false){
      return false;
    }else{
      return true;
    }
    
  }

  /**
  * save voucher vao salesrule_coupon
  * tạo voucher theo rule xxx và lưu vào chottvn_salesrule_coupon (phone_number, html_item, email, status show)
  */
  public function applyCouponProcess($voucher_code, $phone_number, $rule, $enable_sms){
    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    $timeInterface = $objectManager->create('\Magento\Framework\Stdlib\DateTime\TimezoneInterface');
    $ruleId = $rule->getId();

    // get voucher message
    $discountAmount = $rule->getDiscountAmount();
    $ruleEndDate = $timeInterface->date($rule->getData('to_date').' 00:00:00')->format('d/m/Y');
    $voucherMessage = ((int)$discountAmount/1000).'K, ma voucher '.$voucher_code;
    // connection
    $connection = $this->resourceConnection->getConnection();

    // save voucher
    $voucher = $objectManager->create('Magento\SalesRule\Model\Coupon');        
    $voucher->setId(null)->setRuleId($ruleId)->setUsageLimit(1)->setUsagePerCustomer(1)->setType(1)->setCode($voucher_code)->setCreatedAt(date('Y-m-d H:i:s'))->save();

    // save chottvn_salesrule_coupon
    $query = "INSERT INTO chottvn_salesrule_coupon (salesrule_coupon_id, phone_number, status) VALUES ('".$voucher->getId()."', '".$phone_number."', 1)";

    $this->writeLog('Query Apply Coupon Process: '.$query);
    $this->writeLog('SMS Enable: '.$enable_sms);
    if($connection->query($query)){
      // send SMS
      if($enable_sms){
        $data_sms = json_encode(array(
                                  'phone' => $phone_number,
                                  'voucher' => $voucherMessage,
                                  'rule_end_date' => $ruleEndDate
                                ));
        $result_sms = $this->sendOtpRepo->sendVoucher($data_sms);
      }
      return true;
    }
  }

  /**
  * save info vao sales_order vi dung model no bi vong lap afterSave
  */
  public function updateInfoSalesOrder($orderId, $currentPromoCartRuleId, $voucher, $discountVoucherCartRuleId){
    // connection
    $connection = $this->resourceConnection->getConnection();

    // save sales_order
    $query = "UPDATE sales_order SET chottvn_applied_rule_ids='".$currentPromoCartRuleId."', customer_discount_code='".$voucher."', chottvn_applied_discount_code_rule_id='".$discountVoucherCartRuleId."' WHERE entity_id='".$orderId."'";
    
    $this->writeLog('Update Info Sales Order: '.$query);
    $connection->query($query);

    if($connection->query($query)){
      // send SMS
      return true;
    }
  }

  /**
  * Generate voucher
  */
  function generateVoucher($customer, $key = '31', $length = 4) {
    // connection
    $connection = $this->resourceConnection->getConnection();
    $customer_id = $customer['entity_id'];
    $result = array();

    // generate
    $voucher = 'CTT'.sprintf("%03d", $customer_id).$key.$this->generateRandomString($length);

    // check voucher exist
    $query = "SELECT * FROM salesrule_coupon WHERE code='".$voucher."'";
    $result = $connection->fetchRow($query);

    if($result){
      $this->generateVoucher($customer, $key, $length);
    }else{
      return $voucher;
    }

  }

  /**
  * Generate random string
  */
  function generateRandomString($length = 4) {
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
  }

  /**
  * @param $info
  * @param $type  [error, warning, info]
  * @return 
  */
  private function writeLog($info, $type = "info") {
      $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/chottvn_salesrule.log');
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

