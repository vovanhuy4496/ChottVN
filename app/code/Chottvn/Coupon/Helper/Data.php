<?php
/**
 * Copyright © (c) chotructuyen.co All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Chottvn\Coupon\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $timezone;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
    ) {
      $this->timezone = $timezone;
      parent::__construct($context);
    }


  /**
   * Get Coupons
   *
   * @return <Array>
   */
  public function getCoupons($phoneNumber){
      try{
          $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
          $connection = $objectManager->get('\Magento\Framework\App\ResourceConnection');
          $conn = $connection->getConnection();

          $sqlQuery = "
              SELECT src.coupon_id AS coupon_id,  src.code AS coupon_code,
                src.usage_limit, src.times_used,
                src.created_at, src.expiration_date,
                csrc.html_item, csrc.phone_number, csrc.email, csrc.status,
                src.rule_id AS rule_id, sr.name AS rule_name, sr.description AS rule_description,
                sr.from_date AS rule_from_date, sr.to_date AS rule_to_date,
                sr.simple_action AS rule_action, sr.discount_amount AS rule_discount_amount,
                csr.promo_condition_description,
                csr.promo_condition_image
              FROM salesrule_coupon src
              JOIN chottvn_salesrule_coupon csrc ON src.coupon_id = csrc.salesrule_coupon_id
              JOIN salesrule sr ON src.rule_id = sr.rule_id
              LEFT JOIN chottvn_salesrule csr On sr.rule_id = csr.salesrule_id
              WHERE csrc.phone_number = $phoneNumber
                AND csrc.status >= 1
          ";
          $binds = [];
          $data  = $conn->fetchAll($sqlQuery, $binds);
          return $data;
      }catch(\Exception $e){
          $this->writeLog($e);
          return [];
      }
  }

  /**
   * Get Coupon info utils
   *
   */
  public function getCouponType($coupon){
    try{
      $type = "";
      switch ($coupon["rule_action"]) {
        case 'cart_fixed':
          $type = __("Voucher");
          break;
        case 'cart_percent':
          $type = __("Coupon");
          break;
        case 'by_fixed':
          $type = __("Voucher");
          break;
        case 'by_percent':
          $type = __("Coupon");
          break;
        case 'ampromo_cart':
          //$type = __("Voucher");
          break;
        case 'ampromo_items':
          //$type = __("Coupon");
          break;
        default:
          break;
      }
      return $type;
    }catch(\Exception $e){
      return "";
    }
  }

  public function getCouponExpirationDate($coupon){
    try{
      return empty($coupon["expiration_date"]) ? $coupon["rule_to_date"] : $coupon["expiration_date"];
    }catch(\Exception $e){
      $this->writeLog($e);
      return null;
    }
  }

  public function getCouponExpirationDateStr($coupon){
    try{
      $dateStr = $this->getCouponExpirationDate($coupon);
      if (empty($dateStr)){
        return "";
      }else{
        $date = strtotime($dateStr);
        return $this->timezone->date($date)->format('d/m/yy');
      }
    }catch(\Exception $e){
      $this->writeLog($e);
      return "";
    }
  }

  public function getCouponStatusCode($coupon){
    try{
      $status = "";

      if($this->isCouponRevoked($coupon)){
        $status = "revoked";
      }else if($this->isCouponUsed($coupon)){
        $status = "used";
      }else{
        if($this->isCouponExpired($coupon)){
          $status = "expired";
        }else{
          $status = "new";
        }
      }
      return $status;
    }catch(\Exception $e){
      return "";
    }
  }

  public function getCouponStatusLabel($coupon){
    $statusCode = $this->getCouponStatusCode($coupon);
    return __("coupon_status_".$statusCode);
  }

  public function isCouponRevoked($coupon){
    try{
      if($coupon["status"] == 4){
        return true;
      }
      return false;

    }catch(\Exception $e){
      $this->writeLog($e);
      return false;
    }
  }

  public function isCouponExpired($coupon){
    try{
      if($coupon["status"] == 3){
        return true;
      }
      $usageLimit = $coupon["usage_limit"];
      $timesUsed = $coupon["times_used"];
      if ($timesUsed > $usageLimit){
        return true;
      }else{
        $dateStr = $this->getCouponExpirationDate($coupon);
        if (empty($dateStr)){
          return false;
        }else{
          $date = strtotime($dateStr);
          $dateCurrent = date("Y-m-d");
          return $date < $dateCurrent;
        }
      }

    }catch(\Exception $e){
      $this->writeLog($e);
      return true;
    }
  }

  public function isCouponUsed($coupon){
    try{
      if($coupon["status"] == 2){
        return true;
      }
      $usageLimit = $coupon["usage_limit"];
      $timesUsed = $coupon["times_used"];
      if(0 < $timesUsed && $timesUsed <= $usageLimit){
        return true;
      }else{
        return false;
      }

    }catch(\Exception $e){
      $this->writeLog($e);
      return true;
    }
  }

  public function getCouponAmountString($coupon){
    try{
      $amount = $coupon["rule_discount_amount"];
      $label = "";
      $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
      $priceHelper = $objectManager->get('Chottvn\PriceDecimal\Helper\Data');
      switch ($coupon["rule_action"]) {
        case 'cart_fixed':
          $label = $priceHelper->formatPrice(floatval($amount) );
          break;
        case 'cart_percent':
          $label = round(floatval($amount), 0)."%";
          break;
        case 'by_fixed':
          $label = $priceHelper->formatPrice(floatval($amount) );
          break;
        case 'by_percent':
          $label = round(floatval($amount), 0)."%";
          break;
        case 'ampromo_cart':
          $label = round(floatval($amount), 0);
          break;
        case 'ampromo_items':
          $label = round(floatval($amount), 0);
          break;
        default:
          break;
      }
      return $label;
    }catch(\Exception $e){
      return "";
    }
  }


  /**
  * @param $info
  * @param $type  [error, warning, info]
  * @return
  */
  private function writeLog($info, $type = "info") {
      $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/chottvn_coupon.log');
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
