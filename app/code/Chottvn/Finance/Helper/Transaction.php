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

declare(strict_types=1);

namespace Chottvn\Finance\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Customer\Model\Logger;
use Chottvn\Finance\Model\TransactionTypeFactory;
use Chottvn\Finance\Model\RequestFactory;
use Chottvn\Finance\Model\TransactionFactory;

class Transaction extends AbstractHelper
{

  const TRAN_TYPE_DEPOSIT_MARGIN = 'deposit_margin';
  const TRAN_TYPE_DEPOSIT_CASH = 'deposit_cash';
  const TRAN_TYPE_WITHDRAWAL_MARGIN = 'withdrawal_margin';
  const TRAN_TYPE_WITHDRAWAL_REWARD = 'withdrawal_reward';
  const TRAN_TYPE_HANDLE_RESPONSIBILITY = 'handle_responsibility';
  const TRAN_TYPE_AFFILIATE_GIFT = 'affiliate_gift';

	/**
   * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
   */
  protected $timezone;

  /**
   * @var Magento\Customer\Model\Logger
   */
  protected $logger;

  /**
   * @varChottvn\Affiliate\Model\TransactionTypeFactory
   */
  protected $transactionTypeFactory;

  /**
   * @varChottvn\Affiliate\Model\RequestFactory
   */
  protected $requestFactory;

  /**
   * @varChottvn\Affiliate\Model\TransactionFactory
   */
  protected $transactionFactory;

  /**
   * @param \Magento\Framework\App\Helper\Context $context
   */
  public function __construct(
    Context $context,
    \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
    Logger $logger,
    TransactionTypeFactory $transactionTypeFactory,
    RequestFactory $requestFactory,
    TransactionFactory $transactionFactory
  ) {
    $this->timezone = $timezone;
    $this->logger = $logger;
    $this->transactionTypeFactory = $transactionTypeFactory;
    $this->requestFactory = $requestFactory;
    $this->transactionFactory = $transactionFactory;
  }


  /**
   * Get TransactionCollections
   * @return Collection
   */
  public function getAccountTransactions($accountId ){
  	$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    $connection = $objectManager->get('\Magento\Framework\App\ResourceConnection');
    $conn = $connection->getConnection();
    $sqlQuery = "
       	SELECT *
				FROM chottvn_finance_transaction
				WHERE account_id = $accountId
				ORDER BY updated_at DESC
        ";

    $binds = [];
    $data  = $conn->fetchAll($sqlQuery, $binds);
    return $data;
  }

  public function getAccountTransactionsByType($accountId, $transactionTypeIds, $options = []){
    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    $connection = $objectManager->get('\Magento\Framework\App\ResourceConnection');
    $conn = $connection->getConnection();
    // Query
    $dateRange  = array_key_exists("date_range", $options) ? $options["date_range"] : null;
    $statusArray  = array_key_exists("status", $options) ? $options["status"] : null;
    $sqlQuery = "
        SELECT *
        FROM chottvn_finance_transaction
        WHERE account_id = $accountId
        ";
    if(!empty($transactionTypeIds)) {
      $tranTypeIdsStr = implode(', ', $transactionTypeIds);
      $sqlQuery .= " AND transaction_type_id IN ($tranTypeIdsStr) ";
    }
    if(!empty($dateRange)){
      $startDate = $dateRange["start_date"];
      $endDate = $dateRange["end_date"];
      $sqlQuery .=" AND start_date BETWEEN '$startDate' AND '$endDate' ";
    }
    if(!empty($statusArray)){
      $statusStr = implode(', ', $statusArray);
      $sqlQuery .= "AND status IN ($statusStr) ";
    }
    $sqlQuery .= " ORDER BY updated_at DESC ";

    $binds = [];
    $data  = $conn->fetchAll($sqlQuery, $binds);
    return $data;
  }

  public function getAccountTransactionsByTypeCode($accountId, $transactionTypeCodes, $options = []){
    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    $connection = $objectManager->get('\Magento\Framework\App\ResourceConnection');
    $conn = $connection->getConnection();
    // Query
    $dateRange  = array_key_exists("date_range", $options) ? $options["date_range"] : null;
    $statusArray  = array_key_exists("status", $options) ? $options["status"] : null;
    $sqlQuery = "
        SELECT *
        FROM vw_chottvn_finance_transaction t
        WHERE account_id = $accountId
        ";
    if(!empty($transactionTypeCodes)) {
      array_walk($transactionTypeCodes, function(&$x) {$x = "'$x'";});
      $tranTypeCodesStr = implode(', ', $transactionTypeCodes);
      $sqlQuery .= " AND transaction_type_code IN ($tranTypeCodesStr) ";
    }
    if(!empty($dateRange)){
      $startDate = $dateRange["start_date"];
      $endDate = $dateRange["end_date"];
      $sqlQuery .=" AND start_date BETWEEN '$startDate' AND '$endDate' ";
    }
    if(!empty($statusArray)){
      $statusStr = implode(', ', $statusArray);
      $sqlQuery .= "AND status IN ($statusStr) ";
    }
    $sqlQuery .= " ORDER BY updated_at DESC ";

    $binds = [];
    $data  = $conn->fetchAll($sqlQuery, $binds);
    return $data;
  }

  public function getAccountTransactionsWithdrawal($accountId, $dateRange ){
    $options = [
      "date_range" => $dateRange
    ];
    //return $this->getAccountTransactionsByType ($accountId, [3,4], $options );
    $transactionTypeCodes = [self::TRAN_TYPE_WITHDRAWAL_MARGIN, self::TRAN_TYPE_WITHDRAWAL_REWARD];
    return $this->getAccountTransactionsByTypeCode ($accountId, $transactionTypeCodes, $options );
  }
  public function getAccountTransactionsWithdrawalMargin($accountId, $dateRange ){
    $options = [
      "date_range" => $dateRange
    ];
    //return $this->getAccountTransactionsByType ($accountId, [3], $options );
    $transactionTypeCodes = [self::TRAN_TYPE_WITHDRAWAL_MARGIN];
    return $this->getAccountTransactionsByTypeCode ($accountId, $transactionTypeCodes, $options );
  }
  public function getAccountTransactionsWithdrawalReward($accountId, $dateRange ){
    $options = [
      "date_range" => $dateRange
    ];
    //return $this->getAccountTransactionsByType ($accountId, [4], $options );
    $transactionTypeCodes = [self::TRAN_TYPE_WITHDRAWAL_REWARD];
    return $this->getAccountTransactionsByTypeCode ($accountId, $transactionTypeCodes, $options );
  }

  public function getAccountTransactionsDeposit($accountId, $dateRange ){
    $options = [
      "date_range" => $dateRange
    ];
    //return $this->getAccountTransactionsByType ($accountId, [1,2], $options );
    $transactionTypeCodes = [self::TRAN_TYPE_DEPOSIT_MARGIN, self::TRAN_TYPE_DEPOSIT_CASH, self::TRAN_TYPE_AFFILIATE_GIFT];
    return $this->getAccountTransactionsByTypeCode ($accountId, $transactionTypeCodes, $options );
  }
  public function getAccountTransactionsDepositWithoutMargin($accountId, $dateRange ){
    $options = [
      "date_range" => $dateRange
    ];    
    //return $this->getAccountTransactionsByType ($accountId, [2], $options );
    $transactionTypeCodes = [self::TRAN_TYPE_DEPOSIT_CASH, self::TRAN_TYPE_AFFILIATE_GIFT];
    return $this->getAccountTransactionsByTypeCode ($accountId, $transactionTypeCodes, $options );
  }
  public function getAccountTransactionsDepositMargin($accountId, $dateRange){
    $options = [
      "date_range" => $dateRange
    ];
    //return $this->getAccountTransactionsByType ($accountId, [1], $options );
    $transactionTypeCodes = [self::TRAN_TYPE_DEPOSIT_MARGIN];
    return $this->getAccountTransactionsByTypeCode ($accountId, $transactionTypeCodes, $options );
  }
  public function getAccountTransactionsDepositCash($accountId, $dateRange ){
    $options = [
      "date_range" => $dateRange
    ];
    //return $this->getAccountTransactionsByType ($accountId, [2], $options );
    $transactionTypeCodes = [self::TRAN_TYPE_DEPOSIT_CASH];
    return $this->getAccountTransactionsByTypeCode ($accountId, $transactionTypeCodes, $options );
  }
  public function getAccountTransactionsHandleResponsibility($accountId, $dateRange ){
    $options = [
      "date_range" => $dateRange
    ];
    //return $this->getAccountTransactionsByType ($accountId, [5], $options );
    $transactionTypeCodes = [self::TRAN_TYPE_HANDLE_RESPONSIBILITY];
    return $this->getAccountTransactionsByTypeCode ($accountId, $transactionTypeCodes, $options );
  }

  public function getAccountTransactionsByMonth($accountId, $dateRange)
  {
    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    $connection = $objectManager->get('\Magento\Framework\App\ResourceConnection');
    $conn = $connection->getConnection();
    $startDate = $dateRange["start_date"];
    $endDate = $dateRange["end_date"];
    $sqlQuery = "
      SELECT DATE_FORMAT(a.start_date, '%Y-%m') AS month, a.*
        FROM chottvn_finance_transaction  a
        WHERE account_id = $accountId
          AND start_date BETWEEN '$startDate' AND '$endDate'
        ORDER BY start_date DESC, updated_at DESC
    ";
    $binds = [];
    $items  = $conn->fetchAll($sqlQuery, $binds);
    $data = [];
    while($startDate <= $endDate)
    {
      $monthKey = date('Y-m', strtotime($endDate) );
      $options = [];
      foreach ($items as $item) {
        if($item["month"] == $monthKey ) {
          $options[] = $item;
        }
      }
      $data[$monthKey] = $options;
      $endDate = date("Y-m-01", strtotime($endDate." -1 month") );
    }

    return $data;
  }

  /**
   * Get Amount
   *
   */
  public function getAccountAmountByTransactionType ($accountId, $transactionTypeIds, $options = [] ){
  	$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    $connection = $objectManager->get('\Magento\Framework\App\ResourceConnection');
    $conn = $connection->getConnection();
    // Query
    $statusArray  = array_key_exists("status", $options) ? $options["status"] : null;
    $sqlQuery = "
       	SELECT SUM(amount * rate) AS amount
				FROM chottvn_finance_transaction
				WHERE account_id = $accountId
        ";
    if(!empty($transactionTypeIds)) {
      $tranTypeIdsStr = implode(', ', $transactionTypeIds);
      $sqlQuery .= " AND transaction_type_id IN ($tranTypeIdsStr) ";
    }
    if(!empty($statusArray)) {
      $statusStr = implode(', ', $statusArray);
      $sqlQuery .= " AND status IN ($statusStr) ";
    }

    $binds = [];
    $data  = $conn->fetchRow($sqlQuery, $binds);
    return $data["amount"];
  }
  public function getAccountAmountByTransactionTypeCode ($accountId, $transactionTypeCodes, $options = [] ){
    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    $connection = $objectManager->get('\Magento\Framework\App\ResourceConnection');
    $conn = $connection->getConnection();
    // Query
    $statusArray  = array_key_exists("status", $options) ? $options["status"] : null;
    $sqlQuery = "
        SELECT SUM(amount * rate) AS amount
        FROM vw_chottvn_finance_transaction
        WHERE account_id = $accountId
        ";
    if(!empty($transactionTypeCodes)) {
      array_walk($transactionTypeCodes, function(&$x) {$x = "'$x'";});
      $tranTypeCodesStr = implode(', ', $transactionTypeCodes);
      $sqlQuery .= " AND transaction_type_code IN ($tranTypeCodesStr) ";
    }
    if(!empty($statusArray)) {
      $statusStr = implode(', ', $statusArray);
      $sqlQuery .= " AND status IN ($statusStr) ";
    }

    $binds = [];
    $data  = $conn->fetchRow($sqlQuery, $binds);
    return $data["amount"];
  }

  public function getAccountAmountTransactionAll ($accountId){
    $options = [
      "status" => [10]
    ];
    return $this->getAccountAmountByTransactionType ($accountId, [], $options );
  }
  public function getAccountAmountTransactionAllAvaiable ($accountId){
    $options = [
      "status" => [1, 10]
    ];
    return $this->getAccountAmountByTransactionType ($accountId, [], $options );
  }


  public function getAccountAmountTransactionDeposit ($accountId){
    $options = [
      "status" => [10] 
    ];
    //return $this->getAccountAmountByTransactionType ($accountId, [1,2], $options );
    $transactionTypeCodes = [self::TRAN_TYPE_DEPOSIT_MARGIN, self::TRAN_TYPE_DEPOSIT_CASH, self::TRAN_TYPE_AFFILIATE_GIFT];
    return $this->getAccountAmountByTransactionTypeCode ($accountId, $transactionTypeCodes, $options );
  }
  public function getAccountAmountTransactionDepositWithoutMargin ($accountId){
    $options = [
      "status" => [10] 
    ];
    //return $this->getAccountAmountByTransactionType ($accountId, [1,2], $options );
    $transactionTypeCodes = [self::TRAN_TYPE_DEPOSIT_CASH, self::TRAN_TYPE_AFFILIATE_GIFT];
    return $this->getAccountAmountByTransactionTypeCode ($accountId, $transactionTypeCodes, $options );
  }

  public function getAccountAmountTransactionDepositMargin ($accountId){
    $options = [
      "status" => [10]
    ];
  	//return $this->getAccountAmountByTransactionType ($accountId, [1],$options );
    $transactionTypeCodes = [self::TRAN_TYPE_DEPOSIT_MARGIN];
    return $this->getAccountAmountByTransactionTypeCode ($accountId, $transactionTypeCodes, $options );
  }

  public function getAccountAmountTransactionDepositCash ($accountId){
    $options = [
      "status" => [10]
    ];
  	//return $this->getAccountAmountByTransactionType ($accountId, [2], $options );
    $transactionTypeCodes = [self::TRAN_TYPE_DEPOSIT_CASH];
    return $this->getAccountAmountByTransactionTypeCode ($accountId, $transactionTypeCodes, $options );
  }

  public function getAccountAmountTransactionWithdrawal ($accountId){
    $options = [
      "status" => [10]
    ];
  	//return $this->getAccountAmountByTransactionType ($accountId, [3,4], $options );
    $transactionTypeCodes = [self::TRAN_TYPE_WITHDRAWAL_MARGIN, self::TRAN_TYPE_WITHDRAWAL_REWARD];
    return $this->getAccountAmountByTransactionTypeCode ($accountId, $transactionTypeCodes, $options );
  }

  public function getAccountAmountTransactionWithdrawalMargin ($accountId){
    $options = [
      "status" => [10]
    ];
  	//return $this->getAccountAmountByTransactionType ($accountId, [3], $options );
    $transactionTypeCodes = [self::TRAN_TYPE_WITHDRAWAL_MARGIN];
    return $this->getAccountAmountByTransactionTypeCode ($accountId, $transactionTypeCodes, $options );
  }

  public function getAccountAmountTransactionWithdrawalReward ($accountId){
    $options = [
      "status" => [10]
    ];
  	//return $this->getAccountAmountByTransactionType ($accountId, [4], $options );
    $transactionTypeCodes = [self::TRAN_TYPE_WITHDRAWAL_REWARD];
    return $this->getAccountAmountByTransactionTypeCode ($accountId, $transactionTypeCodes, $options );
  }

  public function getAccountAmountTransactionHandleResponsibility ($accountId){
    $options = [
      "status" => [10]
    ];
  	//return $this->getAccountAmountByTransactionType ($accountId, [5], $options );
    $transactionTypeCodes = [self::TRAN_TYPE_HANDLE_RESPONSIBILITY];
    return $this->getAccountAmountByTransactionTypeCode ($accountId, $transactionTypeCodes, $options );
  }

  public function getAccountAmountMarginBalance ($accountId){
    $options = [
      "status" => [10]
    ];
  	//return $this->getAccountAmountByTransactionType ($accountId, [1,3], $options );
    $transactionTypeCodes = [self::TRAN_TYPE_DEPOSIT_MARGIN, self::TRAN_TYPE_WITHDRAWAL_MARGIN];
    return $this->getAccountAmountByTransactionTypeCode ($accountId, $transactionTypeCodes, $options );
  }


  public function getTransactionTypeAmountCssClass ($transactionTypeId){
  	$class = "amount-default";
  	switch ($transactionTypeId) {
  		case 1:
  			$class = "amount-primary";
  			break;
  		case 2:
  			$class = "amount-primary";
  			break;
  		case 3:
  			$class = "amount-warning";
  			break;
  		case 4:
  			$class = "amount-warning";
  			break;
  		case 5:
  			$class = "amount-danger";
  			break;
  	}
  	return $class;
  }

  public function getTransactionNote($transaction){
    $note = $transaction["note"];
    if (empty($note)){
      switch (intval($transaction["status"]) ) {
        case 0:
          $note = "Đang xét duyệt";
          break;
        case 1:
          $note = "Đang xử lý";
          break;
        case 10:
          $note = "Đã hoàn thành";
          break;
        case 20:
          $note = "Đã huỷ bỏ";
          break;
      }
    }
    return $note;
  }


  /**
   * @param $info
   * @param $type  [error, warning, info]
   * @return
   */
  private function writeLog($info, $type = "info") {
      $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/chottvn_finance.log');
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
