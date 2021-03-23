<?php
/**
 * Copyright (c) 2019 ChottVN
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace Chottvn\Frontend\Block;

/**
 * Class Rules
 *
 * @package Chottvn\Frontend\Block
 */
class Rules extends \Magento\Framework\View\Element\Template
{
    protected $_ruleResource;
    protected $_ruleCollectionFactory;
    protected $_objectManager;
    protected $_customerSession;
    protected $request;
  
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,        
        \Magento\CatalogRule\Model\ResourceModel\Rule $ruleResource,
        \Magento\CatalogRule\Model\ResourceModel\Rule\CollectionFactory $ruleCollectionFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\ObjectManagerInterface $objectManager
    )
    {    
        $this->_ruleResource = $ruleResource;
        $this->_ruleCollectionFactory = $ruleCollectionFactory;
        $this->_objectManager = $objectManager;
        $this->_customerSession = $customerSession;
        $this->request = $request;
        parent::__construct($context);
    }

    /**
     * Product detail (Module: catalog, Controller: product, Action: view, Route: catalog)
     * Checkout Page (Module: checkout, Controller: index, Action: index, Route: checkout)
     * Cart (Module: checkout, Controller: cart, Action: index, Route: checkout)
     * Checkout Success Page (Module: checkout, Controller: onepage, Action: success, Route: checkout)
     */
    public function getCurrentRoutePage(){
        $result = array();
        $moduleName = $this->request->getModuleName();
        $controller = $this->request->getControllerName();
        $action     = $this->request->getActionName();
        $route      = $this->request->getRouteName();

        $result['module'] = $moduleName;
        $result['controller'] = $controller;
        $result['action'] = $action;
        $result['route'] = $route;

        return $result;
    }

    public function isAddRuleToArray($rule_id){
        $salesRuleModel = $this->_objectManager->create('Chottvn\SalesRule\Model\SalesRuleRepository');
        $result = true;

        $currentPage = implode('-', $this->getCurrentRoutePage());

        // echo $currentPage;
        switch ($currentPage) {
            case 'catalog-product-view-catalog':
                # page product detail
                if($salesRuleModel->isHideOnProductDetailPage($rule_id) == false){
                    $result = true;
                }else{
                    $result = false;
                }
                break;
            
            case 'checkout-index-index-checkout':
            case 'checkout-cart-index-checkout':
            case 'checkout-onepage-success-checkout':
                # page checkout page
                # page cart
                # checkout success
                if($salesRuleModel->isHideOnCheckoutPage($rule_id) == false){
                    $result = true;
                }else{
                    $result = false;
                }
                break;
        }

        return $result;
    }

    public function getCatalogRuleByProduct($product){
        // get info catalog price rule
        $priceRules = null;
        $rd = null;
        $productId = $product->getId();
        $price = $product->getPriceInfo()->getPrice('regular_price')->getAmount()->getValue();
        $storeId = $product->getStoreId();
        $dateTs = $this->_localeDate->scopeTimeStamp($storeId);
        $websiteId = $this->_storeManager->getStore($storeId)->getWebsiteId();

        // include customer group id when user logged in
        $customerGroupId = 0; 
        if($this->_customerSession->isLoggedIn()){
            $customerGroupId = $this->_customerSession->getCustomer()->getGroupId();
        }

        // get simple product id or config product ids
        $productType = $product->getTypeId();
        $productIds = array();
        switch ($productType) {
            case 'simple':
                $productIds[] = $product->getId();
                break;
            
            case 'configurable':
                $productConfigIds = $product->getTypeInstance()->getChildrenIds($product->getId());
                $productIds = $productConfigIds[0];
                break;
        }

        // get list catalog price rules name
        $ruleNames = array();
        foreach ($productIds as $prodId) {
            $rules =  $this->_ruleResource->getRulesFromProduct($dateTs, $websiteId, $customerGroupId, $prodId);
            $simpleProduct = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($prodId);

            // sort by priority
            $sort = array();
            foreach ($rules as $key => $row)
            {
                $sort[$key] = $row['sort_order'];
            }
            array_multisort($sort, SORT_ASC, $rules);

            // Get applied rules
            // Case action_stop (Discard subsequent rules)
            // case 1: A, B cùng priority, A tạo trước chọn yes, B tạo sau chọn yes|no => nó apply A
            // case 2: A, B cùng priority, A tạo trước chọn no, B tạo sau chọn yes => nó apply A + B
            // case 3: A priority 0, B priority 1, A, B chọn yes => nó apply A
            // case 4: A priority 0, B priority 1, A chọn no, B chọn yes => nó apply A + B
            foreach ($rules as $rule) {
                if($simpleProduct->getPrice() > 0){
                    if($this->isAddRuleToArray($rule['rule_id'])){
                        $ruleNames[] = $this->getCatalogRuleNameById($rule['rule_id']);
                    }
                }
                if($rule['action_stop'] == 1){break;}
            }
        }

        // unique rules
        $ruleNames = array_unique($ruleNames);

        return $ruleNames;
    }
    public function getIdCatalogRuleByProduct($product){
        // get info catalog price rule
        $priceRules = null;
        $rd = null;
        $productId = $product->getId();
        $price = $product->getPriceInfo()->getPrice('regular_price')->getAmount()->getValue();
        $storeId = $product->getStoreId();
        $dateTs = $this->_localeDate->scopeTimeStamp($storeId);
        $websiteId = $this->_storeManager->getStore($storeId)->getWebsiteId();

        // include customer group id when user logged in
        $customerGroupId = 0; 
        if($this->_customerSession->isLoggedIn()){
            $customerGroupId = $this->_customerSession->getCustomer()->getGroupId();
        }

        // get simple product id or config product ids
        $productType = $product->getTypeId();
        $productIds = array();
        switch ($productType) {
            case 'simple':
                $productIds[] = $product->getId();
                break;
            
            case 'configurable':
                $productConfigIds = $product->getTypeInstance()->getChildrenIds($product->getId());
                $productIds = $productConfigIds[0];
                break;
        }

        // get list catalog price rules name
        $ruleId = array();
        foreach ($productIds as $prodId) {
            $rules =  $this->_ruleResource->getRulesFromProduct($dateTs, $websiteId, $customerGroupId, $prodId);
            $simpleProduct = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($prodId);

            // sort by priority
            $sort = array();
            foreach ($rules as $key => $row)
            {
                $sort[$key] = $row['sort_order'];
            }
            array_multisort($sort, SORT_ASC, $rules);

            // Get applied rules
            // Case action_stop (Discard subsequent rules)
            // case 1: A, B cùng priority, A tạo trước chọn yes, B tạo sau chọn yes|no => nó apply A
            // case 2: A, B cùng priority, A tạo trước chọn no, B tạo sau chọn yes => nó apply A + B
            // case 3: A priority 0, B priority 1, A, B chọn yes => nó apply A
            // case 4: A priority 0, B priority 1, A chọn no, B chọn yes => nó apply A + B
            foreach ($rules as $rule) {
                if($simpleProduct->getPrice() > 0){
                    if($this->isAddRuleToArray($rule['rule_id'])){
                        $ruleId[] = $this->getCatalogRuleIdById($rule['rule_id']);
                    }
                }
                if($rule['action_stop'] == 1){break;}
            }
        }

        // unique rules
        $ruleId = array_unique($ruleId);
        
        return $ruleId;
    }

    public function getCartRuleByProduct($product, $type = array(), $without_type = array(), $simpleAction = array()){
        // get info cart price rule
        $ruleNames = array();
        $rules = $this->_objectManager->create('Magento\SalesRule\Model\RuleFactory')->create();
        $rules=$rules->getCollection();
        if($simpleAction){
            $rules->addFieldToFilter('main_table.simple_action', ['nin' => $simpleAction]);
        }
        $timefc = $this->_objectManager->create('\Magento\Framework\Stdlib\DateTime\DateTime');
        $timeinterface = $this->_objectManager->create('\Magento\Framework\Stdlib\DateTime\TimezoneInterface');
        $_currentTime = strtotime($timeinterface->date($timefc->gmtDate())->format('Y-m-d'));

        // include customer group id when user logged in
        $customerGroupId = 0; 
        if($this->_customerSession->isLoggedIn()){
            $customerGroupId = $this->_customerSession->getCustomer()->getGroupId();
        }

        // get simple product id or config product ids
        $productType = $product->getTypeId();
        $productIds = array();
        switch ($productType) {
            case 'simple':
                $productIds[] = $product->getId();
                break;
            
            case 'configurable':
                $productConfigIds = $product->getTypeInstance()->getChildrenIds($product->getId());
                $productIds = $productConfigIds[0];
                break;
        }

        // get rules
        foreach ($productIds as $prodId) {
            $simpleProduct = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($prodId);

            foreach ($rules as $rule) {
                $fromDate = $rule->getFromDate();
                $toDate = $rule->getToDate();
                $isActive = $rule->getIsActive();
                $stopRulesProcessing = $rule->getStopRulesProcessing();

                // Check status cart rule is active
                // check stop processing is 0 
                // (maximum qty discount = max number) => stop process = 1
                if  (
                    isset($fromDate) 
                    && $_currentTime >= strtotime($fromDate)
                    && ($isActive == 1 && $stopRulesProcessing == 0)
                    && (strtotime($toDate) >= $_currentTime || !isset($toDate))
                    ) 
                {
                    $item = $this->_objectManager->create('Magento\Catalog\Model\Product');
                    $item->setProduct($simpleProduct);
                    if ( $rule->getActions()->validate($item)
                        && $simpleProduct->getPrice() > 0
                        && in_array($customerGroupId, $rule->getCustomerGroupIds())
                        ) 
                    {
                        if($this->isAddRuleToArray($rule->getId())){
                            if(!empty($without_type)){
                                if(!in_array($rule->getData('simple_action'), $without_type)){
                                    $ruleNames[] = $rule->getName();
                                }
                            }elseif(!empty($type)){
                                if(in_array($rule->getData('simple_action'), $type)){
                                    $ruleNames[] = $rule->getName();
                                }
                            }else{
                                $ruleNames[] = $rule->getName();
                            }
                        }
                    }
                }
            }
        }

        // unique rules
        $ruleNames = array_unique($ruleNames);

        return $ruleNames;
    }

    public function getIdCartRuleByProduct($product, $type = array(), $without_type = array()){
        // get info cart price rule
        $ruleId = array();
        $rules = $this->_objectManager->create('Magento\SalesRule\Model\RuleFactory')->create();
        $rules=$rules->getCollection();

        $timefc = $this->_objectManager->create('\Magento\Framework\Stdlib\DateTime\DateTime');
        $timeinterface = $this->_objectManager->create('\Magento\Framework\Stdlib\DateTime\TimezoneInterface');
        $_currentTime = strtotime($timeinterface->date($timefc->gmtDate())->format('Y-m-d'));

        // include customer group id when user logged in
        $customerGroupId = 0; 
        if($this->_customerSession->isLoggedIn()){
            $customerGroupId = $this->_customerSession->getCustomer()->getGroupId();
        }

        // get simple product id or config product ids
        $productType = $product->getTypeId();
        $productIds = array();
        switch ($productType) {
            case 'simple':
                $productIds[] = $product->getId();
                break;
            
            case 'configurable':
                $productConfigIds = $product->getTypeInstance()->getChildrenIds($product->getId());
                $productIds = $productConfigIds[0];
                break;
        }

        // get rules
        foreach ($productIds as $prodId) {
            $simpleProduct = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($prodId);

            foreach ($rules as $rule) {
                $fromDate = $rule->getFromDate();
                $toDate = $rule->getToDate();
                $isActive = $rule->getIsActive();
                $stopRulesProcessing = $rule->getStopRulesProcessing();

                // Check status cart rule is active
                // check stop processing is 0 
                // (maximum qty discount = max number) => stop process = 1
                if  (
                    isset($fromDate) 
                    && $_currentTime >= strtotime($fromDate)
                    && ($isActive == 1 && $stopRulesProcessing == 0)
                    && (strtotime($toDate) >= $_currentTime || !isset($toDate))
                    ) 
                {
                    $item = $this->_objectManager->create('Magento\Catalog\Model\Product');
                    $item->setProduct($simpleProduct);
                    if ( $rule->getActions()->validate($item)
                        && $simpleProduct->getPrice() > 0
                        && in_array($customerGroupId, $rule->getCustomerGroupIds())
                        ) 
                    {
                        if($this->isAddRuleToArray($rule->getId())){
                            if(!empty($without_type)){
                                if(!in_array($rule->getData('simple_action'), $without_type)){
                                    $ruleId[] = $rule->getId();
                                }
                            }elseif(!empty($type)){
                                if(in_array($rule->getData('simple_action'), $type)){
                                    $ruleId[] = $rule->getId();
                                }
                            }else{
                                $ruleId[] = $rule->getId();
                            }
                        }
                    }
                }
            }
        }

        // unique rules
        $ruleId = array_unique($ruleId);
        
        return $ruleId;
    }


    public function getGiftCartRuleByProduct($product, $type = array()){
        // get info cart price rule
        $ruleNames = array();
        $rules = $this->_objectManager->create('Magento\SalesRule\Model\RuleFactory')->create();
        $rules=$rules->getCollection();

        $timefc = $this->_objectManager->create('\Magento\Framework\Stdlib\DateTime\DateTime');
        $timeinterface = $this->_objectManager->create('\Magento\Framework\Stdlib\DateTime\TimezoneInterface');
        $_currentTime = strtotime($timeinterface->date($timefc->gmtDate())->format('Y-m-d'));

        // include customer group id when user logged in
        $customerGroupId = 0; 
        if($this->_customerSession->isLoggedIn()){
            $customerGroupId = $this->_customerSession->getCustomer()->getGroupId();
        }

        // get simple product id or config product ids
        $productType = $product->getTypeId();
        $productIds = array();
        switch ($productType) {
            case 'simple':
                $productIds[] = $product->getId();
                break;
            
            case 'configurable':
                $productConfigIds = $product->getTypeInstance()->getChildrenIds($product->getId());
                $productIds = $productConfigIds[0];
                break;
        }

        // get rules
        foreach ($productIds as $prodId) {
            $simpleProduct = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($prodId);

            foreach ($rules as $rule) {
                $fromDate = $rule->getFromDate();
                $toDate = $rule->getToDate();
                $isActive = $rule->getIsActive();
                $stopRulesProcessing = $rule->getStopRulesProcessing();

                // Check status cart rule is active
                // check stop processing is 0 
                // (maximum qty discount = max number) => stop process = 1
                if  (
                    isset($fromDate) 
                    && $_currentTime >= strtotime($fromDate)
                    && ($isActive == 1 && $stopRulesProcessing == 0)
                    && (strtotime($toDate) >= $_currentTime || !isset($toDate))
                    ) 
                {
                    $item = $this->_objectManager->create('Magento\Catalog\Model\Product');
                    $item->setProduct($simpleProduct);
                    if ( $rule->getActions()->validate($item)
                        && ( in_array($rule->getData('simple_action'), $type) )
                        && $simpleProduct->getPrice() > 0
                        && in_array($customerGroupId, $rule->getCustomerGroupIds())
                        ) 
                    {
                        if($this->isAddRuleToArray($rule->getId())){
                            $ruleNames[] = $rule;
                        }
                    }
                }
            }
        }
        
        // unique rules
        // $ruleNames = array_unique($ruleNames);

        return $ruleNames;
    }

    public function getCatalogRuleIdById($rule_id)
    {
        $catalogRule = null;

        $catalogRule = $this->_ruleCollectionFactory->create()
            ->addFieldToFilter('rule_id', $rule_id);
        $catalogRule = $catalogRule->getFirstItem();

        return $catalogRule->getId();
    }

    public function getCatalogRuleNameById($rule_id)
    {
        $catalogRule = null;

        $catalogRule = $this->_ruleCollectionFactory->create()
            ->addFieldToFilter('rule_id', $rule_id);
        $catalogRule = $catalogRule->getFirstItem();

        return $catalogRule->getName();
    }
    public function getCartRuleIdsConditionSimpleActionByProductCTT($product,$simpleAction,$type = null){
        // get info cart price rule
        $rulecart = array();
        $rules = $this->_objectManager->create('Magento\SalesRule\Model\RuleFactory')->create();
        $rules = $rules->getCollection()->addFieldToFilter('main_table.simple_action', ['eq' => $simpleAction]);
        $resource = $this->_objectManager->get('\Magento\Framework\App\ResourceConnection');
        $second_table_name = $resource->getTableName('amasty_ampromo_rule'); 
        $rules->getSelect()->joinLeft(array('ampromo' => $second_table_name),'main_table.rule_id = ampromo.salesrule_id');
        if($type){
            $rules->getSelect()->where('ampromo.type =?',$type);
        }
        // $this->writeLog($rules->getSelect()->__toString());
        $timefc = $this->_objectManager->create('\Magento\Framework\Stdlib\DateTime\DateTime');
        $timeinterface = $this->_objectManager->create('\Magento\Framework\Stdlib\DateTime\TimezoneInterface');
        $_currentTime = strtotime($timeinterface->date($timefc->gmtDate())->format('Y-m-d'));

        // include customer group id when user logged in
        $customerGroupId = 0; 
        if($this->_customerSession->isLoggedIn()){
            $customerGroupId = $this->_customerSession->getCustomer()->getGroupId();
        }

        // get simple product id or config product ids
        $productType = $product->getTypeId();
        $productIds = array();
        switch ($productType) {
            case 'simple':
                $productIds[] = $product->getId();
                break;
            
            case 'configurable':
                $productConfigIds = $product->getTypeInstance()->getChildrenIds($product->getId());
                $productIds = $productConfigIds[0];
                break;
        }
       
        // get rules
        foreach ($productIds as $prodId) {
           
            $simpleProduct = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($prodId);
            foreach ($rules as $keyrule => $rule) {
                $fromDate = $rule->getFromDate();
                $toDate = $rule->getToDate();
                $isActive = $rule->getIsActive();
                $type = $rule->getType();
                $simpleAction = $rule->getSimpleAction();
                $stopRulesProcessing = $rule->getStopRulesProcessing();
                if  (
                    isset($fromDate) 
                    && $_currentTime >= strtotime($fromDate)
                    && ($isActive == 1 && $stopRulesProcessing == 0)
                    && (strtotime($toDate) >= $_currentTime || !isset($toDate))
                    ) 
                {
                    $item = $this->_objectManager->create('Magento\Catalog\Model\Product');
                    $item->setProduct($simpleProduct);
                    if (
                        $rule->getActions()->validate($item)
                        &&  $simpleProduct->getPrice() > 0
                        && in_array($customerGroupId, $rule->getCustomerGroupIds())
                        )
                    {
                        if($this->isAddRuleToArray($rule->getId())){
                            $skus = $rule->getData('sku');
                            $discountAmount = (int) $rule->getData('discount_amount');
                            $arrskus = array();
                            $arrskus = explode( ',', $skus);
                            if($arrskus){
                                foreach($arrskus as $item){
                                    $rulecart[$keyrule]['simple_action'][] = $simpleAction;
                                    // $rulecart[$keyrule]['type'][] = $type;
                                    $rulecart[$keyrule]['name_rule'][] = $rule->getName(); 
                                    $rulecart[$keyrule]['sku'][] = $item; 
                                    $rulecart[$keyrule]['qty'][] = $discountAmount;
                                    $rulecart[$keyrule]['rule_id'][] = $rule->getId();
                                }
                            }
                        }
                    }
                }
            }
        }
        // $this->writeLog($rulecart);
        return $rulecart;
    }
    
    public function getCartRuleIdsConditionSimpleActionByProduct($product){
        // get info cart price rule
        $rulecart = array();
        $rules = $this->_objectManager->create('Magento\SalesRule\Model\RuleFactory')->create();
        $rules = $rules->getCollection();
        $resource = $this->_objectManager->get('\Magento\Framework\App\ResourceConnection');
        $second_table_name = $resource->getTableName('amasty_ampromo_rule'); 
        $rules->getSelect()->joinLeft(array('ampromo' => $second_table_name),
                                               'main_table.rule_id = ampromo.salesrule_id')
            ->order(array('main_table.from_date DESC','main_table.name DESC'));;
        // $this->writeLog($rules->getSelect()->__toString());
        
        $timefc = $this->_objectManager->create('\Magento\Framework\Stdlib\DateTime\DateTime');
        $timeinterface = $this->_objectManager->create('\Magento\Framework\Stdlib\DateTime\TimezoneInterface');
        $_currentTime = strtotime($timeinterface->date($timefc->gmtDate())->format('Y-m-d'));

        // include customer group id when user logged in
        $customerGroupId = 0; 
        if($this->_customerSession->isLoggedIn()){
            $customerGroupId = $this->_customerSession->getCustomer()->getGroupId();
        }

        // get simple product id or config product ids
        $productType = $product->getTypeId();
        $productIds = array();
        switch ($productType) {
            case 'simple':
                $productIds[] = $product->getId();
                break;
            
            case 'configurable':
                $productConfigIds = $product->getTypeInstance()->getChildrenIds($product->getId());
                $productIds = $productConfigIds[0];
                break;
        }
       
        // get rules
        foreach ($productIds as $prodId) {
           
            $simpleProduct = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($prodId);
            foreach ($rules as $keyrule => $rule) {
                $fromDate = $rule->getFromDate();
                $toDate = $rule->getToDate();
                $isActive = $rule->getIsActive();
                $type = $rule->getType();
                $simpleAction = $rule->getSimpleAction();
                $stopRulesProcessing = $rule->getStopRulesProcessing();
                if  (
                    isset($fromDate) 
                    && $_currentTime >= strtotime($fromDate)
                    && ($isActive == 1 && $stopRulesProcessing == 0)
                    && (strtotime($toDate) >= $_currentTime || !isset($toDate))
                    ) 
                {
                    $item = $this->_objectManager->create('Magento\Catalog\Model\Product');
                    $item->setProduct($simpleProduct);
                    if (
                        $rule->getActions()->validate($item)
                        &&  $simpleProduct->getPrice() > 0
                        && in_array($customerGroupId, $rule->getCustomerGroupIds())
                        )
                    {
                        if($this->isAddRuleToArray($rule->getId())){
                            $skus = $rule->getData('sku');
                            $discountAmount = (int) $rule->getData('discount_amount');
                            $arrskus = array();
                            $arrskus = explode( ',', $skus);
                            if($arrskus){
                                foreach($arrskus as $item){
                                    $rulecart[$keyrule]['simple_action'][] = $simpleAction;
                                    $rulecart[$keyrule]['type'][] = $type;
                                    $rulecart[$keyrule]['name_rule'][] = $rule->getName(); 
                                    $rulecart[$keyrule]['sku'][] = $item; 
                                    $rulecart[$keyrule]['qty'][] = $discountAmount;
                                    $rulecart[$keyrule]['rule_id'][] = $rule->getId();
                                }
                            }
                        }
                    }
                }
            }
        }
        // $this->writeLog($rulecart);
        return $rulecart;
    }
    private function writeLog($info, $type = "info")
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/Rules.log');
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