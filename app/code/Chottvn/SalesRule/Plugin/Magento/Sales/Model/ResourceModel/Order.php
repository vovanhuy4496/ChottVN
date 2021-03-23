<?php
/**
 * Copyright © (c) chotructuyen.co All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Chottvn\SalesRule\Plugin\Magento\Sales\Model\ResourceModel;

use Chottvn\SalesRule\Helper\Data as SalesRuleHelper;

class Order {
  protected $scopeConfig;
  protected $request;

  const CURRENT_PROMO_CART_RULE_ID = 'chottvn_promo_configuration/voucher/cttpromo_current_promo_cart_rule_id';
  const DISCOUNT_VOUCHER_RULE_ID = 'chottvn_promo_configuration/voucher/cttpromo_discount_voucher_rule_id';
  const APPLIED_AMOUNT = 'chottvn_promo_configuration/voucher/cttpromo_applied_amount';
  const AFFILIATE_REWARD = 'chottvn_promo_configuration/voucher/cttpromo_affiliate_reward';
  const AFFILIATE_TRANSACTION_TYPE_ID = 'chottvn_promo_configuration/voucher/cttpromo_affiliate_transaction_type_id';
  const ENABLED_MODULE = 'chottvn_promo_configuration/voucher/enabled';
  const SEND_SMS_ENABLE = 'chottvn_promo_configuration/voucher/send_sms_enabled';

  const TRANSACTION_STATUS = 10; // complete

  public function __construct(
    \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
    \Magento\Framework\Webapi\Rest\Request $request,
    SalesRuleHelper $salesRuleHelper
  ) {
      $this->scopeConfig = $scopeConfig;
      $this->salesRuleHelper = $salesRuleHelper;
      $this->request = $request;
  }

  /**
   * @param \Magento\Sales\Model\Resource\Model $subject
   * @param  $result
   * @return mixed
   * @throws \Exception
   */
  public function afterSave(\Magento\Sales\Model\ResourceModel\Order $subject, $result, $object) {
    try{
      $oldStatus = $object->getOrigData('status');
      $newStatus = $object->getData('status');
      $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
      $this->writeLog('Start: --------------------------------------------------------------'.date('Y-m-d H:i:s'));
      // kiem tra don hang thanh cong thi thuc hien
      $state = $object->getData('state');
      $this->writeLog('Order State: '.$state);
      if($state == 'complete'){
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $enabledModule = $this->scopeConfig->getValue(self::ENABLED_MODULE, $storeScope);
        $this->writeLog('Enable Module: '.$enabledModule);
        if($enabledModule){
          // get rule id from admin config
          $currentPromoCartRuleId = $this->scopeConfig->getValue(self::CURRENT_PROMO_CART_RULE_ID, $storeScope);

          // get current promo cart rule by id
          $timeInterface = $objectManager->create('\Magento\Framework\Stdlib\DateTime\TimezoneInterface');
          $currentPromoCartRule = $objectManager->create('Magento\SalesRule\Model\Rule')->load($currentPromoCartRuleId); 
          $isActive = $currentPromoCartRule->getIsActive();
          $stopRulesProcessing = $currentPromoCartRule->getStopRulesProcessing();

          if($isActive == 1 && $stopRulesProcessing == 0){
            $customerPhoneNumber = $object->getData('chott_customer_phone_number');
            $customerVatNumber = $object->getData('vat_number');

            // get company_vat_number
            $companyVatNumber = '';
            $saleHelper = $objectManager->get('Chottvn\Sales\Helper\Data');
            $orderAttributeList = $saleHelper->getOrderAttributesData($object->getId());
            if($orderAttributeList){
              $companyVatNumber = isset($orderAttributeList['vat_number']) ? $orderAttributeList['vat_number']:'';
            }

            // get start/end date rule
            // need transfer date to date - 7 hours
            $currentPromoCartRuleStartDate = $currentPromoCartRule->getData('from_date').' 00:00:00';
            $currentPromoCartRuleStartDate = date('Y-m-d H:i:s', strtotime($currentPromoCartRuleStartDate . " - 7 hours"));

            $currentPromoCartRuleEndDate = $currentPromoCartRule->getData('to_date').' 23:59:59';
            $currentPromoCartRuleEndDate = date('Y-m-d H:i:s', strtotime($currentPromoCartRuleEndDate . " - 7 hours"));

            // get customer thoa dieu kien
            // step 1: 
              // 1.1 kiem tra nhan qua hay chua phone_number, rule_start_date, rule_end_date
              // 1.2 kiem tra nhan qua hay chua theo ma so thue, rule_start_date, rule_end_date
            // step 2: if ( 1.1 == false && 1.2 == false )
              // 2.1 kiem tra customer da active
              // 2.2 customer duoc verify phone
              // 2.3 customer created_at >= rule_start_data && create_at <= rule_end_data
            // step 3: if (2 == true)
                // 3.1 lay thong tin verify_phone_created_at, sale_rule_end_date, so tien > 3tr (tien tinh lai theo order)
                // 3.2 kiem tra customer co duoc nhan voucher hay khong
            // step 4: if (3 == true)
                // 4.1 Tao voucher code
                // 4.2 save voucher vao salesrule_coupon
                // 4.3 save chottvn_salesrule_coupon (phone_number, html_item, email, status show)
                // 4.4 send SMS
            // step 5: update info chottvn_applied_rule_ids, customer_discount_code, chottvn_applied_discount_code_rule_id

            // step 1: 
              // 1.1 kiem tra nhan qua hay chua phone_number, rule_start_date, rule_end_date
              // 1.2 kiem tra nhan qua hay chua theo ma so thue, rule_start_date, rule_end_date
            $isRecievedRuleForCustomer = $this->salesRuleHelper->isRecievedRuleForCustomer($customerPhoneNumber, $companyVatNumber, $currentPromoCartRuleStartDate, $currentPromoCartRuleEndDate);
            $this->writeLog('Is Recieved Rule For Customer: '.$isRecievedRuleForCustomer);

            // step 2: if ( 1.1 == false && 1.2 == false )
            if($isRecievedRuleForCustomer == false){
              // 2.1 kiem tra customer da active
              // 2.2 customer duoc verify phone
              // 2.3 customer created_at >= rule_start_data && create_at <= rule_end_data
              $customer = $this->salesRuleHelper->getCustomerByPhoneAndRule($customerPhoneNumber, $currentPromoCartRule);
              if($customer){
                $customerCreatedAt = $customer['verify_phone_created_at'];
                $appliedAmount = $this->scopeConfig->getValue(self::APPLIED_AMOUNT, $storeScope);
                $this->writeLog('Customer Created At: '.$customerCreatedAt);
                // check sales_order cua customer va duoc tao tu ngay dang ky toi ngay het han rule
                // va co tong tien >= 3tr
                // step 3: if (2 == true)
                // 3.1 lay thong tin verify_phone_created_at, sale_rule_end_date, so tien > 3tr (tien tinh lai theo order)
                // 3.2 kiem tra customer co duoc nhan voucher hay khong
                // 3.3 loai nhung order da nhap coupon trong khi tinh tong doanh thu

                // get discount voucher rule
                $discountVoucherCartRuleId = $this->scopeConfig->getValue(self::DISCOUNT_VOUCHER_RULE_ID, $storeScope);
                $discountVoucherCartRule = $objectManager->create('Magento\SalesRule\Model\Rule')->load($discountVoucherCartRuleId); 

                $isAppliedRuleForCustomer = $this->salesRuleHelper->isAppliedRuleForCustomerPhoneNumber($customerPhoneNumber, $customerCreatedAt, $currentPromoCartRuleEndDate, $appliedAmount, $discountVoucherCartRuleId);
                $this->writeLog('Is Applied Rule For Customer: '.$customerCreatedAt);
                
                if($isAppliedRuleForCustomer){
                  // step 4: if (3 == true)
                  // 4.1 Tao voucher code
                  // 4.2 save voucher vao salesrule_coupon
                  // 4.3 save chottvn_salesrule_coupon (phone_number, html_item, email, status show)
                  // 4.4 send SMS
                  // du dk nhan coupon
                  // 1. tạo voucher theo rule xxx và lưu vào chottvn_sales_coupon (phone_number, html_item, email, status show)
                  // voucher: CTT 091 31 AQSA
                  $voucher = $this->salesRuleHelper->generateVoucher($customer, '31', 4);
                  $this->writeLog('voucher: '.$voucher);

                  // get enable send sms1
                  $enabledSendSMS = $this->scopeConfig->getValue(self::SEND_SMS_ENABLE, $storeScope);
                  $applyCouponProcess = $this->salesRuleHelper->applyCouponProcess($voucher, $customerPhoneNumber, $discountVoucherCartRule, $enabledSendSMS);

                  // 2. sau đó update last sales_order thông tin chottvn_applied_rule_ids, customer_discount_code, chottvn_applied_discount_code_rule_id
                  // step 5: update info chottvn_applied_rule_ids, customer_discount_code, chottvn_applied_discount_code_rule_id
                  if($applyCouponProcess){
                    $this->salesRuleHelper->updateInfoSalesOrder($object->getId(), $currentPromoCartRuleId, $voucher, $discountVoucherCartRuleId);
                  }
                  
                }
              }
            }          
          }
        }
      }

      $this->checkAndApplyAffiliateVoucherCampaign($object);
        
    }catch(\Exception $e){
        $this->writeLog($e);
    }

    return $result;
  }

  /**
   * Apply Affiliate voucher campaign
   * @param {\Magento\Sales\Model\ResourceModel\Order} order 
   * @return
   */
  public function checkAndApplyAffiliateVoucherCampaign($orderObj){
    if($this->shouldApplyAffiliateVoucherCampaign($orderObj)){
      $this->writeLog(">>>> Order: ".$orderObj->getId()." - Aff APPLY");     
      $this->insertGiftRecord($orderObj);
    }else{
      $this->writeLog(">>>> Order: ".$orderObj->getId()." - Aff NOT APPLY");
    }
  }

  public function insertGiftRecord($orderObj){    
    try{
      $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
      // Get rule   
      $currentPromoCartRuleId = $this->scopeConfig->getValue(self::CURRENT_PROMO_CART_RULE_ID, $storeScope);        
      // Get Transaction Type config
      $transactionTypeId = $this->scopeConfig->getValue(self::AFFILIATE_TRANSACTION_TYPE_ID, $storeScope);
      $transactionType = $this->getTransactionType($transactionTypeId);
      if (empty($transactionType))
        return;
      // Prepare data
      $customerId = $orderObj->getData("customer_id");
      if (empty($customerId)){
        $phoneNumber = $orderObj->getData('chott_customer_phone_number');
        $customerId = $this->getCustomerIdFromPhoneNumber($phoneNumber);
      }
      $affiliateAccountId = $orderObj->getData("affiliate_account_id");
      // Insert Gift
      $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
      $transactionModel = $objectManager->create(\Chottvn\Finance\Model\Transaction::class);   
      $giftAmount = $this->scopeConfig->getValue(self::AFFILIATE_REWARD, $storeScope);
      $transactionStartDate = date("Y-m-d");
      $transactionEndDate = date("Y-m-d");
      $transactionDate = date("Y-m-d");
      $transactionData = [
        "account_id" => $affiliateAccountId,
        "transaction_type_id" => $transactionTypeId,
        "amount" => $giftAmount,
        "rate" => $transactionType["rate"],
        "start_date" => $transactionStartDate,
        "end_date" => $transactionEndDate,
        "transaction_date" => $transactionDate,
        "note" => "",
        "document_code" => "#".$orderObj->getIncrementId(),
        "status" => self::TRANSACTION_STATUS
      ];
      $transactionModel->setData($transactionData);
      $transactionModel->save();
      // Insert Log
      $logModel = $objectManager->create(\Chottvn\SalesRule\Model\Log::class);
      $logData = [
        "affiliate_account_id" => $affiliateAccountId,
        "customer_id" => $customerId,
        "salesrule_id" => $currentPromoCartRuleId,
        "order_id" => $orderObj->getId(),
        "event" => "affiliate_gift",
        "finance_transaction_id" => $transactionModel->getId()        
      ];
      $logModel->setData($logData);
      $logModel->save();
      // Update Order tracking info
      $this->updateOrderInfo(
          $orderObj->getId(),
          [
            "affiliate_transaction_ids" => $transactionModel->getId() 
          ],
          $orderObj->getUpdatedAt()
      );
    }catch(\Exception $e){
      $this->writeLog($e);
    }      
  }

  public function updateOrderInfo($orderId, $data, $updatedAt){
    try{
      $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
      $connection = $objectManager->get('\Magento\Framework\App\ResourceConnection');
      $conn = $connection->getConnection();
      
      // Update Sale Order      
      if (!empty($updatedAt)){
        $data['updated_at'] = $updatedAt;
      }
      $where = ['entity_id = ?' => (int)$orderId];
      $tableName = $connection->getTableName("sales_order");
      $updatedRows=$conn->update($tableName, $data, $where);
    }catch(\Exception $e){
      $this->writeLog($e);
    }
  }

  public function getTransactionType($transactionTypeId){
    try{
      $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
      $connection = $objectManager->get('\Magento\Framework\App\ResourceConnection');
      $conn = $connection->getConnection();
      $sqlQuery = "
        SELECT *
        FROM chottvn_finance_transactiontype
        WHERE transactiontype_id = $transactionTypeId
      ";
      $binds = [];
      $data  = $conn->fetchRow($sqlQuery, $binds);
      return $data;
    }catch(\Exception $e){
      $this->writeLog($e);
      return null;
    }  
  }
  
  public function shouldApplyAffiliateVoucherCampaign($orderObj){
    $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
    // Check module 
    $enabledModule = $this->scopeConfig->getValue(self::ENABLED_MODULE, $storeScope);
    if (! $enabledModule) 
      return false;
    // Check order status
    $statusOld = $orderObj->getOrigData('status');
    $statusNew = $orderObj->getData('status');
    //if(!in_array($statusNew, ["finished", "returned_and_finished"]))
    if(!in_array($statusNew, ["complete"]))
      return false;
    // Check if Customer order 
    $customerId = $orderObj->getData('customer_id');
    $affiliateAccountId = $orderObj->getData('affiliate_account_id');
      //if (empty($customerId) || empty($affiliateAccountId) || ($customerId == $affiliateAccountId) )
    if (empty($affiliateAccountId)
          || ($customerId == $affiliateAccountId) )
      return false;
    // Check Customer is new
    $phoneNumber = $orderObj->getData('chott_customer_phone_number');
    $ruleDateRange = $this->getRuleDateRange();
    if(empty($ruleDateRange))
      return false;
    if(! $this->isCustomerPassedRule($phoneNumber, $ruleDateRange))
      return false;
    // Get customerId if guest order
    if (empty($customerId)){
      $customerId = $this->getCustomerIdFromPhoneNumber($phoneNumber);
    }
    // Check Order date
    $orderDate = $orderObj->getData('created_at');
    if (! $this->isOrderDatePassed($orderDate, $ruleDateRange))
      return false;
    // Check Order amount
    $appliedAmount = $this->scopeConfig->getValue(self::APPLIED_AMOUNT, $storeScope);
    if (! $this->isOrderAmountPassed($orderObj, $appliedAmount))
      return false;
    // Check Gift Received 
    $currentPromoCartRuleId = $this->scopeConfig->getValue(self::CURRENT_PROMO_CART_RULE_ID, $storeScope);  
    //$affiliateReceivedCount = $this->getAffiliateGiftReceivedCountByOrder($affiliateAccountId, $orderObj->getId());
    $affiliateReceivedCount = $this->getAffiliateGiftReceivedCountByCustomerRule($affiliateAccountId, $customerId, $currentPromoCartRuleId);


    if ($affiliateReceivedCount ==  null || $affiliateReceivedCount >= 1){
      return false;
    }
    return true;
  }

  public function getAffiliateGiftReceivedCountByCustomerRule($affiliateAccountId, $customerId, $saleRuleId){
   try{
      // connection
      $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
      $connection = $objectManager->get('\Magento\Framework\App\ResourceConnection');
      $conn = $connection->getConnection();     
      // query
      $query = "
        SELECT count(1) AS count
        FROM chottvn_log_promo
        WHERE customer_id = $customerId
          AND salesrule_id = $saleRuleId                    
      ";
      // get data
      $result = $conn->fetchRow($query);
      return empty($result) ? null: $result["count"];
    }catch(\Exception $e){
      $this->writeLog($e);
      return null;
    }   
  }

  public function getAffiliateGiftReceivedCountByOrder($affiliateAccountId, $customerId, $saleRuleId){
   try{
      // connection
      $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
      $connection = $objectManager->get('\Magento\Framework\App\ResourceConnection');
      $conn = $connection->getConnection();     
      // query
      $query = "
        SELECT count(1) AS count
        FROM chottvn_log_promo
        WHERE affiliate_account_id = $affiliateAccountId
          AND order_id = $orderId                    
      ";
      // get data
      $result = $conn->fetchRow($query);
      return empty($result) ? null: $result["count"];
    }catch(\Exception $e){
      $this->writeLog($e);
      return null;
    }   
  }

  public function isOrderAmountPassed($orderObj, $appliedAmount){
    $orderSubTotalAmount = $this->getOrderSubtotalReal($orderObj->getId());
    if(empty($orderSubTotalAmount))
      return false;
    return $orderSubTotalAmount > $appliedAmount;
  }

  public function isOrderDatePassed($orderDate, $dateRange){
    try{
      $fromDate = $dateRange["from_date"];
      $toDate = $dateRange["to_date"];
      return ($orderDate >= $fromDate) && ($orderDate <= $toDate);
    }catch(\Exception $e){
      $this->writeLog($e);
    }
  }

  public function isCustomerPassedRule($phoneNumber, $dateRange){
    try{
      // connection
      $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
      $connection = $objectManager->get('\Magento\Framework\App\ResourceConnection');
      $conn = $connection->getConnection();
      $fromDate = $dateRange["from_date"];
      $toDate = $dateRange["to_date"];
      // query
      $query = "
        SELECT *
        FROM chottvn_signinphonenumber_verification
        WHERE phone_number = '$phoneNumber'
          AND verify_status = 1          
          AND created_at BETWEEN '$fromDate' AND '$toDate' 
      ";
      // get customer
      $result = $conn->fetchRow($query);
      return !empty($result);
    }catch(\Exception $e){
      $this->writeLog($e);
      return false;
    }    
  }

  public function getCustomerIdFromPhoneNumber($phoneNumber){
    try{
      // connection
      $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
      $connection = $objectManager->get('\Magento\Framework\App\ResourceConnection');
      $conn = $connection->getConnection();      
      // query
      $query = "
        SELECT *
        FROM chottvn_signinphonenumber_verification
        WHERE phone_number = '$phoneNumber'
          AND verify_status = 1 
        ORDER BY id DESC          
      ";
      // get customer
      $result = $conn->fetchRow($query);
      return !empty($result) ? $result["customer_id"] : 0 ;
    }catch(\Exception $e){
      $this->writeLog($e);
      return 0;
    }    
  }

  public function getRuleDateRange(){
    $rule = $this->getCurrentPromoCartRule();
    if(empty($rule)){
      return null;
    }else{
      return [
        "from_date" => $rule->getData('from_date')." 00:00:00",
        "to_date" => $rule->getData('to_date')." 23:59:59"
      ];
    }
  }

  public function getCurrentPromoCartRule(){
    try{
      $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
      $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
      // get rule id from admin config
      $currentPromoCartRuleId = $this->scopeConfig->getValue(self::CURRENT_PROMO_CART_RULE_ID, $storeScope);
      // get current promo cart rule by id
      $currentPromoCartRule = $objectManager->create('Magento\SalesRule\Model\Rule')->load($currentPromoCartRuleId); 
      return $currentPromoCartRule;
    }catch(\Exception $e){
      $this->writeLog($e);      
      return null;
    }
  }

  public function getOrderSubtotalReal($orderId){
    try{
      $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
      $connection = $objectManager->get('\Magento\Framework\App\ResourceConnection');
      $conn = $connection->getConnection();
      $sqlQuery = "
        SELECT SUM( (soi.qty_ordered -  soi.qty_refunded) * soi.price) AS order_sub_total
        FROM sales_order_item soi
        WHERE soi.order_id = $orderId
      ";
      $binds = [];
      $data  = $conn->fetchRow($sqlQuery, $binds);
      return empty($data) ? 0 : $data["order_sub_total"];
    }catch(\Exception $e){
      $this->writeLog($e);
      return 0;
    }
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