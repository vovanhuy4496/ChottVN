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

use Chottvn\Affiliate\Model\Log;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Logger;

/**
 * Class Account
 * @package Chottvn\Affiliate\Helper
 */
class Account extends AbstractHelper
{
  protected $_customer;
  protected $_customerFactory;

  /**
   * @var CustomerRepositoryInterface
   */
  protected $customerRepository;

  /**
   * @var LogFactory
   */
  protected $_logFactory;

  /**
   * Logger of customer's log data.
   *
   * @var Logger
   */
  protected $logger;

  public function __construct(
    Context $context,
    CustomerRepositoryInterface $customerRepository,
    \Chottvn\Affiliate\Model\LogFactory $logFactory,
    \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
    Logger $logger,
    \Magento\Customer\Model\CustomerFactory $customerFactory,
    \Magento\Customer\Model\Customer $customers
  ) {
    parent::__construct($context);
    $this->customerRepository = $customerRepository;
    $this->_logFactory = $logFactory;
    $this->timezone = $timezone;
    $this->logger = $logger;
    $this->_customerFactory = $customerFactory;
    $this->_customer = $customers;
  }

  // get all customer
  public function getFilteredCustomerCollection() {
      return $this->_customerFactory->create()->getCollection()
              ->addAttributeToSelect("*")
              ->addAttributeToFilter("affiliate_status", array("eq" => "activated"))
              ->load();
  }

  /**
   * Get AffiliateLevel
   * @var {int} customerId
   * @return \Magento\Customer\Model\Customer
   */
  public function getAffiliateAccount($customerId){
    try{
      $customer = $this->customerRepository->getById($customerId);
      return $customer;
    }catch(\Exception $e){
      return null;
    }
  }

  /**
   * Get PhoneNumbers
   * @return {String}
   */
  public function getPhoneNumber($customerId)
  {
    $customer = $this->getAffiliateAccount($customerId);
    if(empty($customer)){
      return '';
    }else{
      $phoneNumberAttr = $customer->getCustomAttribute('phone_number');
      
      if (!empty($phoneNumberAttr)) {        
        return $phoneNumberAttr->getValue();
      }
      return '';
    }
  }

  /**
   * Get AffiliateLevel
   * @var \Magento\Customer\Model\Customer
   */
  public function saveAffiliateAccount($customer){
    try{
      $this->customerRepository->save($customer);
    }catch(\Exception $e){
      
    }
  }

  /**
   * Get AffiliateLevel
   * @return {String}
   */
  public function isAffiliate($customerId)
  {
    try{
      $customer = $this->customerRepository->getById($customerId);
      $affiliateStatus = $customer->getCustomAttribute('affiliate_status');
      
      if (!empty($affiliateStatus) && $affiliateStatus->getValue() == "activated") {        
        return true;
      }
      return false;
    }catch(\Exception $e){
      return false;
    }

  }

  /**
   * Get AffiliateCode
   * @return {String}
   */
  public function getAffiliateCode($customerId)
  {
    try{
      $customer = $this->customerRepository->getById($customerId);
      $affiliateCode = $customer->getCustomAttribute('affiliate_code');

      if ($affiliateCode) {
        return $affiliateCode->getValue();
      }
    }catch(\Exception $e){
      return "N/A";
    }
  }

  /**
   * Get AffiliateLevel
   * @return {String}
   */
  public function getAffiliateLevel($customerId)
  {
    try{
      $customer = $this->customerRepository->getById($customerId);
      $affiliateLevel = $customer->getCustomAttribute('affiliate_level');
      if ($affiliateLevel) {
        return $affiliateLevel->getValue();
      }
    }catch(\Exception $e){
      return "ctv";
    }
  }

  public function getAffiliateMarginLimit($customerId){
    $marginLimit = 0;
    $level =  $this->getAffiliateLevel($customerId);
    switch ($level) {
      case 'ctv':
        $marginLimit = 0;
        break;
      case 'ctv_1':
        $marginLimit = 0;
        break;
      case 'ctv_2':
        $marginLimit = 2000000;
        break;
      case 'ctv_3':
        $marginLimit = 5000000;
        break;
      case 'ctv_4':
        $marginLimit = 10000000;
        break;
      case 'ctv_5':
        $marginLimit = 20000000;
        break;
    }

    return $marginLimit;
  }

  /**
   * Get AffiliateLevelChangedLatestDate
   * @return {String}
   */
  public function getAffiliateLevelChangedLatestDate($customerId)
  {
    $log = $this->_logFactory->create();
    $collection = $log->getCollection()
      ->addFieldToFilter('account_id', $customerId)
      ->addFieldToFilter('event', Log::EVENT_AFFILIATE_LEVEL_CHANGED)
      ->setOrder('created_at', 'ASC');
    $itemLog = $collection->getLastItem();

    if ($itemLog->getId()) {
      return $itemLog->getData('created_at');
    }

    return null;
  }

  /**
   * Get AffiliateLevelChangedLatestDateStr
   * @return {String}
   */
  public function getAffiliateLevelChangedLatestDateStr($customerId)
  {
    $date = $this->getAffiliateLevelChangedLatestDate($customerId);

    if ($date) {
      return $this->timezone->date($date)->format('d/m/yy');
    }

    return null;
  }

  /**
   * Get MarginLimit
   * @return {float}
   */
  public function getMarginLimit($customerId)
  {
    return 0;
  }

  /**
   * Get MarginLimitChangedLatestDate
   * @return {float}
   */
  public function getMarginLimitChangedLatestDate($customerId)
  {
    $log = $this->_logFactory->create();
    $collection = $log->getCollection()
      ->addFieldToFilter('account_id', $customerId)
      ->addFieldToFilter('event', Log::EVENT_MARGIN_LIMIT_CHANGED)
      ->setOrder('created_at', 'ASC');
    $itemLog = $collection->getLastItem();

    if ($itemLog->getId()) {
      return $itemLog->getData('created_at');
    }

    return "N/A";
  }

  /**
   * Get MarginLimitChangedLatestDate
   * @return {float}
   */
  public function getMarginLimitChangedLatestDateStr($customerId)
  {
    $log = $this->_logFactory->create();
    $collection = $log->getCollection()
      ->addFieldToFilter('account_id', $customerId)
      ->addFieldToFilter('event', Log::EVENT_MARGIN_LIMIT_CHANGED)
      ->setOrder('created_at', 'ASC');
    $itemLog = $collection->getLastItem();

    if ($itemLog->getId()) {
      return $this->timezone->date($itemLog->getData('created_at'))->format('d/m/yy');
    }

    return null;
  }

  /**
   * Get RegisteredDate
   *
   * @return {Date}
   */
  public function getRegisteredDate($customerId)
  {
    $log = $this->_logFactory->create();
    $collection = $log->getCollection()
      ->addFieldToFilter('account_id', $customerId)
      ->addFieldToFilter('event', Log::EVENT_REGISTERED)
      ->setOrder('created_at', 'ASC');
    $itemLog = $collection->getLastItem();

    if ($itemLog->getId()) {
      return $itemLog->getData('value');
    }

    return "N/A";
  }

  /**
   * Get RegisteredDateString
   *
   * @return {String}
   */
  public function getRegisteredDateStr($customerId)
  {
    $log = $this->_logFactory->create();
    $collection = $log->getCollection()
      ->addFieldToFilter('account_id', $customerId)
      ->addFieldToFilter('event', Log::EVENT_REGISTERED)
      ->setOrder('created_at', 'ASC');
    $itemLog = $collection->getLastItem();

    if ($itemLog->getId()) {
      return $this->timezone->date($itemLog->getData('value'))->format('d/m/yy');
    }

    return "N/A";
  }

  /**
   * Get RegisteredDate
   *
   * @return {Date}
   */
  public function getActivatedDate($customerId)
  {
    $log = $this->_logFactory->create();
    $collection = $log->getCollection()
      ->addFieldToFilter('account_id', $customerId)
      ->addFieldToFilter('event', Log::EVENT_ACTIVATED)
      ->setOrder('created_at', 'ASC');
    $itemLog = $collection->getLastItem();

    if ($itemLog->getId()) {
      return  $this->timezone->date($itemLog->getData('value'));
    }

    return null;
  }

  /**
   * Get RegisteredDateStr
   *
   * @return {Date}
   */
  public function getActivatedDateStr($customerId)
  {
    $log = $this->_logFactory->create();
    $collection = $log->getCollection()
      ->addFieldToFilter('account_id', $customerId)
      ->addFieldToFilter('event', Log::EVENT_ACTIVATED)
      ->setOrder('created_at', 'ASC');
    $itemLog = $collection->getLastItem();

    if ($itemLog->getId()) {
      return $this->timezone->date($itemLog->getData('value'))->format('d/m/yy');
    }

    return "N/A";
  }

  /**
   * Get RegisteredDate
   *
   * @return {Date}
   */
  public function getLogginLatestDate($customerId)
  {
    $logCustomer = $this->logger->get($customerId);

    if ($logCustomer->getLastLoginAt()) {
      return $this->timezone->date($logCustomer->getLastLoginAt())->format('H:i d/m/Y');
    }

    return __('Never');
  }

  /**
   * @param $info
   * @param $type  [error, warning, info]
   * @return
   */
  private function writeLog($info, $type = "info") {
      $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/affiliate_account.log');
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
