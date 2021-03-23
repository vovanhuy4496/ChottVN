<?php
namespace Chottvn\Sales\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\ObjectManager;

class Data extends AbstractHelper {
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * Data constructor.
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param TransportBuilder $transportBuilder
     * @param StateInterface $inlineTranslation
     * @param LoggerInterface $logger
     */
    public function __construct(Context $context, StoreManagerInterface $storeManager) {
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }
    // get Max Delivery Dates
    function getMaxDeliveryDates($maxDeliveryDates) {
        $object_manager = ObjectManager::getInstance();
        $objDate = $object_manager->create('Magento\Framework\Stdlib\DateTime\TimezoneInterface');
        $startDate = $objDate->date()->format('Y-m-d');
        $endDate = date('Y-m-d', strtotime($startDate . " + " . $maxDeliveryDates . " day"));
        $number_sunday = $this->isWeekend($startDate, $endDate);
        if ($number_sunday > 0) {
            $maxDeliveryDates+= $number_sunday;
        }
        return $maxDeliveryDates;
    }

    // get complete delivery date Form Table chottvn_log_sales_order
    function getCompleteDeliveryLog($order) {
        $getCompleteDelivery = null;
        try {
            $getOrderId = $order->getId();
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $timefc = $objectManager->create('\Magento\Framework\Stdlib\DateTime\TimezoneInterface');
            $logColl = $objectManager->create('Chottvn\Sales\Model\ResourceModel\Log\CollectionFactory');
            $collectionLog = $logColl->create()->addFieldToSelect('created_at')
                                    ->addFieldToFilter('order_status', ['eq' => 'complete'])
                                    ->addFieldToFilter('order_id', ['eq' => $getOrderId])
                                    ->getLastItem();
            if (!empty($collectionLog->getData('created_at'))) {
                $createdDate = $collectionLog->getData('created_at');
            } else {
                $createdDate = $order->getUpdatedAt();
            }
            $getCompleteDelivery = $timefc->date($createdDate)->format('d/m/Y');
        } catch(\Exception $e) {
            $this->writeLog($e->getMessage());
        }
        return $getCompleteDelivery;
    }
    
    // function check weekend
    function isWeekend($dateStart, $dateEnd) {
        $start = new \DateTime($dateStart);
        $end = new \DateTime($dateEnd);
        $days = $start->diff($end, true)->days;
        $sundays = intval($days / 7) + ($start->format('N') + $days % 7 >= 7);
        return $sundays;
    }

    public function getOrderAttributesData($orderid) {
        try {
            //query
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
            $connection = $resource->getConnection();
            // select attributes VAT
            $select_vat = $connection->select()->from(['amasty_order_attribute_entity' => $resource->getTableName('amasty_order_attribute_entity') ], array('amasty_order_attribute_entity_varchar.value', 'eav_attribute.attribute_code'))->joinLeft(['amasty_order_attribute_entity_varchar' => $resource->getTableName('amasty_order_attribute_entity_varchar') ], 'amasty_order_attribute_entity_varchar.entity_id = amasty_order_attribute_entity.entity_id', null)->joinLeft(['eav_attribute' => $resource->getTableName('eav_attribute') ], 'eav_attribute.attribute_id = amasty_order_attribute_entity_varchar.attribute_id', null)->where('amasty_order_attribute_entity.parent_id = ?', $orderid);
            // select attributes orther
            $select_orther = $connection->select()->from(['amasty_order_attribute_entity' => $resource->getTableName('amasty_order_attribute_entity') ], array('amasty_order_attribute_entity_text.value', 'eav_attribute.attribute_code'))->joinLeft(['amasty_order_attribute_entity_text' => $resource->getTableName('amasty_order_attribute_entity_text') ], 'amasty_order_attribute_entity_text.entity_id = amasty_order_attribute_entity.entity_id', null)->joinLeft(['eav_attribute' => $resource->getTableName('eav_attribute') ], 'eav_attribute.attribute_id = amasty_order_attribute_entity_text.attribute_id', null)->where('amasty_order_attribute_entity.parent_id = ?', $orderid);
            // $this->writeLog('#Query_orther: '.$select_orther->__toString());
            // $this->writeLog('#Query_VAT: '.$select_vat->__toString());
            $row_vat = $connection->fetchAll($select_vat);
            $row_all = [];
            $row_orther = $connection->fetchAll($select_orther);
            if (is_array($row_vat) || is_object($row_vat)) {
                if(sizeof($row_vat) > 0){
                    foreach ($row_vat as $value) {
                        if (!is_null($value['attribute_code'])) {
                            $row_all+= [$value['attribute_code'] => $value['value']];
                        }else{
                            if(is_null($value['value'])){
                                $row_vat = [];
                                break;
                            }
                        }
                    }
                }
            }
            if (is_array($row_orther) || is_object($row_orther)) {
                foreach ($row_orther as $value) {
                    if (!is_null($value['attribute_code'])) {
                        $row_all+= [$value['attribute_code'] => $value['value']];
                    }
                }
            }
            return $row_all;
        }
        catch(\Exception $e) {
            $this->writeLog("Exception:");
            $this->writeLog($e);
        }
        return $row_all;
    }

    public function getRulesNameWithOrderCTT($getQuoteItemId, $orderId, $ruleIdsApplied) {
        try {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $orderItemFactory = $objectManager->create('Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory');
            $productRepository = $objectManager->create('Magento\Catalog\Api\ProductRepositoryInterface');
            // $messagesPrefix = $objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('ampromo/messages/prefix');
            $saleRuleFactory = $objectManager->create('Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory');
            $resource = $objectManager->create('Magento\Framework\App\ResourceConnection');
            $modelCatalog = $objectManager->get('\Magento\Catalog\Model\Product');
            $ruleIdsAppliedArray = explode(',', $ruleIdsApplied);
            $amasty_ampromo_rule = $resource->getTableName('amasty_ampromo_rule'); 

            $arrayRules = array();
            $_arrayRules = array();
            $__arrayRules = array();

            $arrayRulesIds = array();
            
            // get sp qtang duoc chon (check box)
            $items = $orderItemFactory->create()->addFieldToFilter('order_id', $orderId)
                                                ->addFieldToFilter('cart_promo_parent_item_id', $getQuoteItemId)
                                                ->addFieldToFilter('cart_promo_ids', array('notnull' => true));
            if ($items) {
                foreach($items as $item) {
                    $cart_promo_qty = $item->getData('cart_promo_qty') ? $item->getData('cart_promo_qty'): 1;
                    $product_unit = $item->getData('product_unit') ? ' '.$item->getData('product_unit'): '';
                    $stringResult = $item->getData('product_name_short').'<span class="unit-product">('.$cart_promo_qty.$product_unit.')</span>';
                    $sku = $item->getData('sku');
                    $productData = $modelCatalog->loadByAttribute('sku',$sku);
                    array_push($_arrayRules, $stringResult);
                    array_push($arrayRulesIds, $item->getCartPromoIds());
                }
            }
        
            // remove rule id duoc chon trong rule applied
            $newRuleIdsApplied = array_diff($ruleIdsAppliedArray, $arrayRulesIds);

            $rulesApplied = $saleRuleFactory->create()->addFieldToFilter('rule_id', array('in' => $newRuleIdsApplied));
            $rulesApplied->getSelect()->joinLeft(
                                                array(
                                                        'ampromo' => $amasty_ampromo_rule
                                                    ),
                                                'main_table.rule_id = ampromo.salesrule_id'
                                                )
                                            ->where('main_table.simple_action = ?','ampromo_items')
                                            ->where('ampromo.sku IS NOT NULL');
            if ($rulesApplied) {
                foreach($rulesApplied as $item) {
                    $stringSkus = $item->getSku();
                    $skus = explode(',', $stringSkus);
                    $cart_promo_qty = $item->getDiscountQty() ?  $item->getDiscountQty(): 1;
                    if ($skus) {
                        if (count($skus) == 1 && $item->getType() == 1) {
                            $productPromo = $productRepository->get($skus[0]);
                            $product_unit = $productPromo->getData('product_unit') ? ' '.$productPromo->getData('product_unit'): '';
                            $stringResult = $productPromo->getName().'<span class="unit-product">('.$cart_promo_qty.$product_unit.')</span>';
                            $productData = $modelCatalog->loadByAttribute('sku',$skus[0]);
                            array_push($__arrayRules, $stringResult);
                        }
                        if (count($skus) > 1 && $item->getType() == 0) {
                            foreach($skus as $key => $sku) {
                                $productPromo = $productRepository->get($sku);
                                $product_unit = $productPromo->getData('product_unit') ? ' '.$productPromo->getData('product_unit'): '';
                                $stringResult = $productPromo->getName().'<span class="unit-product">('.$cart_promo_qty.$product_unit.')</span>';
                                $productData = $modelCatalog->loadByAttribute('sku',$sku);
                                array_push($__arrayRules, $stringResult);
                            }
                        }
                        if (count($skus) == 1 && $item->getType() == 0) {
                            $productPromo = $productRepository->get($skus[0]);
                            $product_unit = $productPromo->getData('product_unit') ? ' '.$productPromo->getData('product_unit'): '';
                            $stringResult = $productPromo->getName().'<span class="unit-product">('.$cart_promo_qty.$product_unit.')</span>';
                            $productData = $modelCatalog->loadByAttribute('sku',$skus[0]);
                            array_push($__arrayRules, $stringResult);
                        }
                    }
                }
            }
            $arrayRules = array_merge($_arrayRules, $__arrayRules);
           
            return $arrayRules;
        } catch(\Exception $e) {
            $this->writeLog("Exception:");
            $this->writeLog($e);
        }
    }

    public function hasProductUrl($product)
    {
        if ($product->isVisibleInSiteVisibility()) {
            return true;
        } else {
            if ($product->hasUrlDataObject()) {
                $data = $product->getUrlDataObject();
                if (in_array($data->getVisibility(), $product->getVisibleInSiteVisibilities())) {
                    return true;
                }
            }
        }

        return false;
    }
    public function url_exists($url) {
        $url_headers = get_headers($url);
        if(!$url_headers || $url_headers[0] == 'HTTP/1.0 404 Not Found') {
            $exists = false;
        }
        else {
            $exists = true;
        }
        return $exists;
    }
          /**
     * get Rules Name of Quote
     * @param $productId
     */
    public function getRulesNameByOrderCTT($getQuoteItemId, $orderId, $ruleIdsApplied) {
        try {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $orderItemFactory = $objectManager->create('Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory');
            $productRepository = $objectManager->create('Magento\Catalog\Api\ProductRepositoryInterface');
            // $messagesPrefix = $objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('ampromo/messages/prefix');
            $saleRuleFactory = $objectManager->create('Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory');
            $resource = $objectManager->create('Magento\Framework\App\ResourceConnection');
            $rulecart = array();
            if($ruleIdsApplied){
                $ruleIdsAppliedArray = explode(',', $ruleIdsApplied);
                $amasty_ampromo_rule = $resource->getTableName('amasty_ampromo_rule'); 
                $rules = $saleRuleFactory->create()->addFieldToFilter('rule_id', array('in' => $ruleIdsAppliedArray));
                $rules->getSelect()->joinLeft(array(
                    'ampromo' => $amasty_ampromo_rule),
                    'main_table.rule_id = ampromo.salesrule_id'
                    )
                    ->where('main_table.simple_action = ?','ampromo_items')
                    ->where('ampromo.sku IS NOT NULL')->order(array('main_table.from_date DESC','main_table.name DESC'));
                    
                if ($rules) {
                    foreach ($rules as $keyrule => $rule){
                        $skus = $rule->getData('sku');
                        $discountAmount = $rule->getData('discount_amount') ? (int)$rule->getData('discount_amount') : 1;
                        $arrskus = array();
                        $arrskus = explode( ',', $skus);
                        $type = $rule->getType();
                        if(!empty($type) && $type == 1){
                            // get sp qtang duoc chon (check box)
                            $items = $orderItemFactory->create()->addFieldToFilter('order_id', $orderId)
                            ->addFieldToFilter('cart_promo_parent_item_id', $getQuoteItemId)
                            ->addFieldToFilter('cart_promo_ids', $keyrule);
                            $fisrtItem = $items->getFirstItem();
                            // get sp qtang duoc chon (check box)
                            if (!empty($fisrtItem) && count($fisrtItem->getData()) > 0) {
                                $sku = $fisrtItem->getData('sku');
                                $productPromo = $productRepository->get($sku);
                                $product_unit = $productPromo->getData('product_unit') ? $productPromo->getData('product_unit'): '';
                                $rulecart[$keyrule]['sku'][] = $sku;
                                $rulecart[$keyrule]['unit_product'][] = $product_unit;
                                $rulecart[$keyrule]['qty'][] = $discountAmount;
                            }
                        }else{
                            foreach($arrskus as $item){
                                $productPromo = $productRepository->get($item);
                                $product_unit = $productPromo->getData('product_unit') ? $productPromo->getData('product_unit'): '';
                                $rulecart[$keyrule]['sku'][] = $item;
                                $rulecart[$keyrule]['unit_product'][] = $product_unit;
                                $rulecart[$keyrule]['qty'][] = $discountAmount;
                            }
                        }
                    }
                }
            }
            return $rulecart;
        } catch(\Exception $e) {
            $this->writeLog("Exception:");
            $this->writeLog($e);
        }
    }

    /**
     * 
     * @return bool
     */
    public function getListOrder($orderItems)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $arrayPrdMain = array();
        $arrayPrdCart = array();
        $arrayMain = array();
        try {
            foreach($orderItems as $item){
                $quoteItem = $objectManager->create('Magento\Quote\Model\Quote\Item')->load($item->getQuoteItemId());
                $cartPromoOption = $quoteItem->getCartPromoOption();
                $parentItem = $item->getParentItemId();
                $cartPromoOption = $cartPromoOption ? $cartPromoOption: '' ;
                $productOptions = $item->getProductOptions();
                $isPromoItem = $this->isPromoItem($productOptions);
                if(!$parentItem && !$isPromoItem && $cartPromoOption == ''){
                    array_push($arrayPrdMain,$item);
                }
                if(!$parentItem && ($cartPromoOption == 'ampromo_cart' || $cartPromoOption == 'ampromo_spent') ){
                    array_push($arrayPrdCart,$item);
                }
            }
            $arrayMain = array_merge($arrayPrdMain, $arrayPrdCart);

            return $arrayMain;
        } catch(\Exception $e) {
            $this->writeLog("Exception:");
            $this->writeLog($e);
        }

        return $arrayMain;
    }
     /**
     * 
     * @return bool
     */
    public function isPromoItemCTT($cartPromoOption)
    {
        try {
            if($cartPromoOption == 'ampromo_cart' || $cartPromoOption == 'ampromo_spent'){
                return true;
            }
            return false;
        } catch(\Exception $e) {
            $this->writeLog("Exception:");
            $this->writeLog($e);
        }
        return false;
    }
    /**
     * 
     * @return bool
     */
    public function isPromoItem($productOptions)
    {
        try {
            if( isset($productOptions['info_buyRequest']) 
                && isset($productOptions['info_buyRequest']['options']) 
                && isset($productOptions['info_buyRequest']['options']['ampromo_rule_id'])){
                return true;
            }
            return false;
        } catch(\Exception $e) {
            $this->writeLog("Exception:");
            $this->writeLog($e);
        }
        return false;
    }
     /**
     * 
     * @return bool
     */
    public function getRulesIdByProductOptions($productOptions)
    {
        $rules = '';
        try {
            $rules = '';
            if( isset($productOptions['info_buyRequest']) 
                && isset($productOptions['info_buyRequest']['options']) 
                && isset($productOptions['info_buyRequest']['options']['ampromo_rule_id'])){
                return $rules = $productOptions['info_buyRequest']['options']['ampromo_rule_id'];
            }
            return $rules;
        } catch(\Exception $e) {
            $this->writeLog("Exception:");
            $this->writeLog($e);
        }
        return $rules;
    }
     
    public function getRulesNameByIdRule($ruleId) {
        
        $nameRules = '';
        try {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $quoteItemFactory = $objectManager->create('Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory');
            $productRepository = $objectManager->create('Magento\Catalog\Api\ProductRepositoryInterface');
            // $messagesPrefix = $objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('ampromo/messages/prefix');
            $saleRuleFactory = $objectManager->create('Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory');
            $resource = $objectManager->create('Magento\Framework\App\ResourceConnection');
            $rules = $saleRuleFactory->create()->addFieldToFilter('rule_id',$ruleId);
            $firstItem = $rules->getFirstItem();
            if($firstItem){
                $nameRules = $firstItem['name'];
            }
            return  $nameRules;
        } catch(\Exception $e) {
            $this->writeLog("Exception:");
            $this->writeLog($e);
        }
        return  $nameRules;
    }


    public function getPromoRuleInfo($ruleId){
        try{
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $connection = $objectManager->get('\Magento\Framework\App\ResourceConnection');
            $conn = $connection->getConnection();        
            $sqlQuery = "
                SELECT sr.rule_id, sr.simple_action, apm.type, apm.sku, sr.discount_amount
                FROM salesrule sr
                LEFT JOIN amasty_ampromo_rule apm ON sr.rule_id = apm.salesrule_id
                WHERE rule_id = $ruleId              
            ";
            $binds = [];
            $data  = $conn->fetchRow($sqlQuery, $binds);
            return $data;
        }catch(\Exception $e){
            $this->writeLog();
            return null;
        }        
    }

    /**
     * @param $info
     * @param $type  [error, warning, info]
     * @return
     */
    private function writeLog($info, $type = "info") {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/chottvn_sales_helper.log');
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
