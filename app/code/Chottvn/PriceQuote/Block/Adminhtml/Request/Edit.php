<?php
/**
 * Copyright © (c) chotructuyen.vn All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Chottvn\PriceQuote\Block\Adminhtml\Request;

class Edit extends \Magento\Framework\View\Element\Template
{
    protected $request;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\Request\Http $request
        )
	{
        $this->request = $request;
		parent::__construct($context);
	}
    public function getRequestItems()
    { 
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $items = $objectManager->create('Chottvn\PriceQuote\Model\ResourceModel\Items\CollectionFactory');
        $request_id = $this->request->getParam('request_id');
        $data = $items->create()->addFieldToSelect(
            '*'
        )->addFieldToFilter('request_id', $request_id);
        return $data;
    }
    public function getRequest()
    { 
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $items = $objectManager->create('Chottvn\PriceQuote\Model\ResourceModel\Request\CollectionFactory');
        $request_id = $this->request->getParam('request_id');
        $data = $items->create()->addFieldToSelect(
            '*'
        )->addFieldToFilter('request_id', $request_id);
        return $data->getLastItem();
    }
    public function getCustomShippingAmount($checkFlagShipping,$getShippingAmount)
    {
        if ($checkFlagShipping == 'freeshipping') {
            return __('Free Shipping');
        }
        if ($this->isOverWeight() == "over") {
            return __('Price Contact');
        }
        // chua chon address ben checkout
        if (empty($this->getRequest()->getData('region_id'))) {
            return __('Not included');
        }


        if ($getShippingAmount > 0) {
            return $getShippingAmount;
        }
        if ($checkFlagShipping == 'accept' && $getShippingAmount == 0) {
            return __('Not included');
        }
        return $getShippingAmount;
    }
    public function isOverWeight()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $handlingOverWeightFee = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('carriers/tablerate/handling_over_weight_fee');
        $totalWeight = $this->checkWeight();

        if ($totalWeight > $handlingOverWeightFee) {
            return "over";
        }
        return "accept";
    }
    public function checkWeight()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $ruleResource = $objectManager->get('Magento\CatalogRule\Model\ResourceModel\Rule');
        $totalWeight = 0;
        $items = $this->getRequestItems();
        $rule = false;

        foreach ($items as $item) {
            if($item->getData('applied_rule_ids')){
                $arrayAppliedRuleIds = explode(',', $item->getData('applied_rule_ids'));
                $rule = $this->checkProductHaveFreeShip($arrayAppliedRuleIds);
            }
            if ($item->getData('qty') > 0 && empty($item->getData('applied_rule_ids')) && !$rule) {
                $totalWeight = $totalWeight + ($item->getData('weight') * $item->getData('qty'));
            }
        }
        return $totalWeight;
    }
    public function checkProductHaveFreeShip($salesruleIds)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $productHaveFreeShip = 0;
        foreach($salesruleIds as $salesruleId) {
            if ($salesruleId) {
                $salesRule = $objectManager->get('\Magento\SalesRule\Model\Rule');
                $rule = $salesRule->load($salesruleId);
                if ($rule && $rule->getIsActive()) {
                    $productHaveFreeShip = $rule->getSimpleFreeShipping();
                }
            }
        }

        if ($productHaveFreeShip == 1) {
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
		$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/Edit.log');
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


