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

namespace Chottvn\Affiliate\Plugin\Chottvn\Finance\Model\ResourceModel;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Chottvn\Affiliate\Helper\Account as AffiliateAccountHelper;
use Chottvn\Affiliate\Helper\RewardRule as AffiliateRewardRuleHelper;
use Chottvn\Affiliate\Helper\LevelRule as AffiliateLevelRuleHelper;
use Chottvn\Affiliate\Helper\Log as AffiliateLogHelper;
use Chottvn\Finance\Helper\Transaction as FinanceTransactionHelper;

class Transaction {
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
   * @var \Chottvn\Affiliate\Helper\RewardRule
   */
	private $affiliateRewardRuleHelper;

	/**
   * @var \Chottvn\Affiliate\Helper\LevelRule
   */
	private $affiliateLevelRuleHelper;
	
	/**
   * @var Chottvn\Finance\Helper\Transaction
   */
	private $financeTransactionHelper;

	
	

	public function __construct(
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
    CustomerRepositoryInterface $customerRepository,
    AffiliateAccountHelper $affiliateAccountHelper,
    AffiliateRewardRuleHelper $affiliateRewardRuleHelper,
    AffiliateLevelRuleHelper $affiliateLevelRuleHelper,
    AffiliateLogHelper $affiliateLogHelper,
    FinanceTransactionHelper $financeTransactionHelper
	) { 
		$this->_scopeConfig = $scopeConfig;
    $this->customerRepository = $customerRepository;
    $this->affiliateAccountHelper = $affiliateAccountHelper;
    $this->affiliateRewardRuleHelper = $affiliateRewardRuleHelper;
    $this->affiliateLevelRuleHelper = $affiliateLevelRuleHelper;
    $this->affiliateLogHelper = $affiliateLogHelper;
    $this->financeTransactionHelper = $financeTransactionHelper;
	}  
	/**
	* @param \Magento\Sales\Model\Resource\Model $subject
	* @param  $result
	* @return mixed
	* @throw \Exception
	*/
	public function afterSave(
        \Chottvn\Finance\Model\ResourceModel\Transaction $subject,
        $result, $object
    ) {
		try{
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();

	    $transactionTypeId = intval($object->getData('transaction_type_id'));
	    $transactionTypeMarginIds = array(1, 3);
	    $customerId = $object->getData('account_id');
		
	    if(in_array($transactionTypeId, $transactionTypeMarginIds)){
	    	// Save Margin Change log
				$this->affiliateLogHelper->saveLogWithResource([
					"account_id" => $customerId,
					"resource_type" => 'chottvn_finance_transaction',
					"resource_id" => $object->getId(),
					"event" => 'margin_limit_changed',
					"value" => [
						"account_id" => (int)$object->getData('account_id'),
						"request_id" => $object->getData('request_id'),
						"transaction_type_id" => $object->getData('transaction_type_id'),
						"amount" => $object->getData('amount'),
						"transaction_date" => $object->getData('transaction_date'),
						"note" => $object->getData('note'),
						"status" => $object->getData('status')
					]
				]);
				// Check & apply Level rule
		    $this->affiliateLevelRuleHelper->checkAndApplyLevelRule($customerId);
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
      $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/affiliate_finance_transaction.log');
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