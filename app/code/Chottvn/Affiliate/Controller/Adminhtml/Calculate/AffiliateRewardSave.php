<?php

/**
 * Copyright © © 2020 chotructuyen.co All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Chottvn\Affiliate\Controller\Adminhtml\Calculate;
use Chottvn\Affiliate\Helper\Account as AffiliateAccountHelper;
use Chottvn\Affiliate\Helper\RewardRule as AffiliateRewardRuleHelper;
use Chottvn\Sales\Helper\Order as SalesOrderHelper;

class AffiliateRewardSave extends \Magento\Backend\App\Action
{
    protected $orderRepository;

    /**
    * @var \Chottvn\Affiliate\Helper\Account
    */
    private $affiliateAccountHelper;


    /**
    * @var \Chottvn\Affiliate\Helper\RewardRule
    */
    private $affiliateRewardRuleHelper;

    /**
    * @var \Chottvn\Sales\Helper\Order
    */
    private $SalesOrderHelper;

    /**
     * Notifier Pool
     *
     * @var NotifierPool
     */
    protected $messageManager;


    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context  $context
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        AffiliateAccountHelper $affiliateAccountHelper,
        AffiliateRewardRuleHelper $affiliateRewardRuleHelper,
        SalesOrderHelper $salesOrderHelper,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->orderRepository = $orderRepository;
        $this->affiliateAccountHelper = $affiliateAccountHelper;
        $this->affiliateRewardRuleHelper = $affiliateRewardRuleHelper;
        $this->salesOrderHelper = $salesOrderHelper;
        $this->messageManager = $messageManager;
        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $formKeyIsValid = $this->_formKeyValidator->validate($this->getRequest());
        $isPost = $this->getRequest()->isPost();
        $orderIdsStr = $this->getRequest()->getParam('order_ids');
        if (!$formKeyIsValid || !$isPost || empty($orderIdsStr)) {
            $this->messageManager->addError(__('Bad Request'));
            return $resultRedirect->setPath(
                '*/*/affiliatereward',
                ['_current' => true]
            );
        }
        try {            
            $orderIds = explode(',', $orderIdsStr);  

            foreach($orderIds as $orderId) {
                try{
                    $order = $this->orderRepository->get(intval($orderId) );
                    if(!empty($order)){
                        $orderItems = $order->getAllItems();
                        if(sizeof($orderItems) > 0) {
                            $firstItem = $orderItems[0];
                            $affiliateLevel = $firstItem->getAffiliateLevel();
                        }else{
                            $affiliateLevel = "";
                        } 
                        $orderStatus = $order->getStatus();                   
                        $this->writeLog("Order: ".$orderId. " - Level: ".$affiliateLevel." - Status: ".$orderStatus);                                                  
                        if(in_array($orderStatus, ["finished", "returned_and_finished"]) ){
                            $rrDateFilter = $this->salesOrderHelper->getOrderFinishedAt($orderId);   
                        }else{
                            $rrDateFilter = $order->getCreatedAt();
                        }
                        
                        $rewardRules = $this->affiliateRewardRuleHelper->getRewardRulesAvailableForOrder($affiliateLevel, $rrDateFilter);
                        if (!empty($rewardRules)){
                            foreach ($rewardRules as $rewardRule) {                     
                                $this->affiliateRewardRuleHelper->applyRewardRule($rewardRule, $orderItems, $affiliateLevel);
                            }
                        }
                    }
                } catch(\Exception $e){
                    
                }                     
            }          
            $this->messageManager->addSuccess(__("Applied Orders' Affiliate Reward"));
            return $resultRedirect->setPath('sales/order/index');

        } catch(\Exception $e) {
            $this->writeLog($e->getMessage());
            $this->messageManager->addError($e->getMessage());
            return $resultRedirect->setPath(
                '*/*/affiliatereward',
                ['_current' => true]
            );
        }
    }

    public function checkExistOrder($id) {
        $order = $this->orderRepository->get($id);
        if ($order->getData()) {
            return true;
        }
        return false;
    }

    /**
	* @param $info
	* @param $type  [error, warning, info]
	* @return 
	*/
	private function writeLog($info, $type = "info") {
		$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/calculate_affreward.log');
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
