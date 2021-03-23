<?php
/**
 * Copyright © (c) Cho Truc Tuyen 2020 All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Chottvn\Affiliate\Block\RewardRule;

use Magento\Customer\Model\Session as CustomerSession;
use Chottvn\Affiliate\Helper\RewardRule as RewardRuleHelper;

class RuleList extends \Magento\Framework\View\Element\Template
{
    protected $customerSession;

    protected $rewardRuleHelper;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context  $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        CustomerSession $customerSession,
        RewardRuleHelper $rewardRuleHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->customerSession = $customerSession;
        $this->rewardRuleHelper = $rewardRuleHelper;
    }

	/**
     * Prepare layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {        
        return parent::_prepareLayout();
    }

    /**
     * Prepare layout
     *
     * @return $this
     */
    public function getCustomer()
    {        
        return $this->customerSession->getCustomer();
    }

    public function isCustomerLoggedIn()
    {        
        return !empty($this->getCustomer());
    }

    public function getCustomerId()
    {        
        if ($this->isCustomerLoggedIn()){
            return $this->getCustomer()->getId();
        }else{
            return 0;
        }
    }

    public function getRewardRules(){
        return $this->rewardRuleHelper->getRewardRules();
    }

    public function getDictRewardRulesByAffiliateLevel(){
        return $this->rewardRuleHelper->getRewardRulesGroupByAffiliateLevel();
    }

    public function getProductBrandImageUrl($productBrandId){       
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $productBrandFactory = $objectManager->create('Ves\Brand\Model\ResourceModel\Brand\CollectionFactory');
        try{
            $productBrand = $productBrandFactory->create()
                ->addFieldToFilter('brand_id', array('eq' => $productBrandId))
                ->getFirstItem();
            return $productBrand->getThumbnailUrl();
        }catch(\Exception $e){
            return "";
        }
       
    }

    public function getProductKindCodeFromId($productKindId){
        return $this->rewardRuleHelper->getProductKindCodeFromId($productKindId);
    } 
}