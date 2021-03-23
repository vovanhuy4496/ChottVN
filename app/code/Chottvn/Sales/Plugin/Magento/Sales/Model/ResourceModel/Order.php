<?php
namespace Chottvn\Sales\Plugin\Magento\Sales\Model\ResourceModel;

use Chottvn\Sales\Helper\Data as HelperData;

class Order {
    protected $_scopeConfig;
    protected $_request;
    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, \Magento\Framework\Webapi\Rest\Request $request, HelperData $helperData) {
        $this->_scopeConfig = $scopeConfig;
        $this->helperData = $helperData;
        $this->_request = $request;
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
            $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
            /*
             * Auto update Custom fields
            */
            $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/log_status.log');
            $logger = new \Zend\Log\Logger();
            $logger->addWriter($writer);
            $logger->info('Chottvn_Sales: '.$oldStatus." - ".$newStatus);  
            // save log order
            $this->saveLogOrder($object);
            //if ($newStatus == "pending"){
            if (empty($oldStatus)){
                $customerSession = $objectManager->create('Magento\Customer\Model\Session');
                $customerId = $customerSession->getCustomer()->getId();
                $customerData = $customerSession->getCustomer(); 
                $customer_level = $customerData->getData('customer_level') ? $customerData->getData('customer_level'):'';
                $affiliate_level = $customerData->getData('affiliate_level') ? $customerData->getData('affiliate_level'):'';

                $affiliateaccountId = $object->getAffiliateAccountId() ? $object->getAffiliateAccountId():'';
                $createdAt = $object->getCreatedAt();
                if(!$affiliate_level && $affiliateaccountId){
                    $customerRepository = $objectManager->get('Magento\Customer\Api\CustomerRepositoryInterface');
                    $customer = $customerRepository->getById($affiliateaccountId);
                    $affiliate_level = $customer->getCustomAttribute('affiliate_level')->getValue();
                }
                $quoteId = $object->getQuoteId();
                $this->writeLog($quoteId);
                // $quoteItemFactory = $objectManager->get('Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory');
                // $quoteItem = $quoteItemFactory->create()->addFieldToFilter('quote_id', $quoteId);
                $orderItems = $object->getAllItems();
                if ($orderItems) {
                    $connection = $resource->getConnection();
                    //$connection->beginTransaction();
                    foreach ($orderItems as $item) {
                        $itemId = $item->getItemId();
                        // $quoteItemId = $item->getQuoteItemId();
                        $productId = $item->getProductId();
                        // use "create" for "get" to new object instance
                        $modelProduct = $objectManager->create('Magento\Catalog\Model\Product');
                        $product = $modelProduct->load($productId);
                        $tableName = $resource->getTableName('sales_order_item');
                        // Add guarantee
                        $guaranteeValue = $product->getData('guarantee') ? $product->getData('guarantee'):'';
                        // Add name short to product
                        $productNameShort = $product->getNameShort() ? $product->getNameShort():'';
                        // Add model 
                        $model = $product->getData('model') ? $product->getData('model'):'';
                        // Add product unit 
                        $productUnit = $product->getData('product_unit') ? $product->getData('product_unit'): '';
                        // Add brand id
                        $productBrandHelper = $objectManager->create('Ves\Brand\Helper\ProductBrand');
                        $productBrand = $productBrandHelper->getFirstBrandByProduct($product);
                        if(empty($productBrand)){
                            $product_brand_id = null;
                        }else{
                            $product_brand_id = $productBrand->getId() ? $productBrand->getId(): null;
                        }                        
                        // Add product kind
                        $productKind = $product->getData('product_kind') ? $product->getData('product_kind'): $this->getValueDefaultProductKind();
                        // Get rma rule
                        $chottvnRmaRule = $this->getLastRmaRule($productKind,$createdAt);
                        $idCmaRule= isset($chottvnRmaRule['id']) ? $chottvnRmaRule['id'] : '';
                        $returnPeriod = isset($chottvnRmaRule['return_period']) ? $chottvnRmaRule['return_period'] : '';
                        // foreach($quoteItem as $_item){
                        //     $_itemid = $_item->getData('item_id') ? $_item->getData('item_id'):0;
                        //     $cartPromoParentId = $_item->getData('cart_promo_parent_id') ? $_item->getData('cart_promo_parent_id'): '';
                        //     $cartPromoItemIds = $_item->getData('cart_promo_item_ids') ? $_item->getData('cart_promo_item_ids'): '';
                        //     $cartPromoOption = $_item->getData('cart_promo_option') ? $_item->getData('cart_promo_option'): '';
                        //     $cartPromoIds = $_item->getData('cart_promo_ids') ? $_item->getData('cart_promo_ids'): '';
                        //     $cartPromoQty = $_item->getData('cart_promo_qty') ? $_item->getData('cart_promo_qty'): '';
                        //     $cartPromoParentItemId = $_item->getData('cart_promo_parent_item_id') ? $_item->getData('cart_promo_parent_item_id'): '';
                        //     if($_itemid == $quoteItemId){
                        //         $item->setCartPromoParentId($cartPromoParentId);
                        //         $item->setCartPromoItemIds($cartPromoItemIds);
                        //         $item->setCartPromoOption($cartPromoOption);
                        //         $item->setCartPromoIds($cartPromoIds);
                        //         $item->setCartPromoQty($cartPromoQty);
                        //         $item->setCartPromoParentItemId($cartPromoParentItemId);
                        //         break;
                        //     }
                        // }
                        // update data
                        $item->setGuarantee($guaranteeValue);
                        $item->setReturnPeriod($returnPeriod);
                        $item->setChottvnRmaRuleId($idCmaRule);
                        $item->setProductNameShort($productNameShort);
                        $item->setModel($model);
                        $item->setProductKind($productKind);
                        $item->setProductUnit($productUnit);
                        $item->setCustomerLevel($customer_level);
                        $item->setAffiliateLevel($affiliate_level);
                        $item->setProductBrandId($product_brand_id);
                        $item->save();
                    }
                    //$connection->commit();
                }
            }  
        }catch(\Exception $e){
            $this->writeLog($e);
        }
              
        return $result;
    }
    public function saveLogOrder($object){
        $oldStatus = $object->getOrigData('status');
        $newStatus = $object->getData('status');
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
         // log order
         $orderId = $object->getEntityId();
         $timefc = $objectManager->create('\Magento\Framework\Stdlib\DateTime\DateTime');
         $currentDate = $timefc->gmtDate();
        //  $this->writeLog($currentDate);
         // save log
         $value_json = [];
         $currentUser = $objectManager->get('Magento\Backend\Model\Auth\Session');
         if($currentUser->isLoggedIn()){
             $value_json = $currentUser->getUser()->getId() ? $value_json + ['user_id' => $currentUser->getUser()->getId()]: $value_json;
         }
         $value_json = $newStatus ? $value_json + ['status' => $newStatus]: $value_json;
         $request = $objectManager->get('Magento\Framework\App\Request\Http');
         $controllerName   = $request->getControllerName();
        //track action
        if($controllerName){
            $value_json = $value_json + ['action' => $controllerName];
        }else{
            $value_json = $value_json + ['action' => 'new'];
        }
        //  $this->writeLog("-----------------------");
         $log = $objectManager->create('\Chottvn\Sales\Model\Log');
         $log->setOrderId($orderId);
         $log->setOrderStatus($newStatus);
         $log->setValue(json_encode($value_json));
         $log->setValueOld($oldStatus);
         $log->setCreatedAt($currentDate);
         $log->save();
    }
    public function getLastRmaRule($productKind,$createdAt){
        try{
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $collection = $objectManager->get('Chottvn\Rma\Model\ResourceModel\Rule\Collection');
            $collection->addFieldToFilter('product_kind', ['eq' => $productKind])
            ->addFieldToFilter('status', ['eq' =>'1']);
            $collection->getSelect()->where(
                new \Zend_Db_Expr(
                    "'$createdAt'"." BETWEEN `start_date` AND COALESCE(`end_date`, NOW())"
                )
            );
            // $this->writeLog($collection->getSelect()->__toString());
            $arr = [];
            $i = 0;
            if($collection->getData()){
                foreach($collection->getData() as $item){
                    $arr[$i]['id'] = $item['id'];
                    $conditions = json_decode($item['conditions'], true);
                    $arr[$i]['return_period'] = $conditions['return_period'];
                    $i++;
                }
            }
            $result = [];
            if($arr){
                $key = '';
                $max = max(array_column($arr, 'return_period'));
                foreach($arr as $k => $value){
                    if($value['return_period'] == $max){
                        $key = $k;
                        break;
                    }
                }
                $result = $arr[$key];
            }
            // $this->writeLog($result);
            return $result;
        }catch(\Exception $e){
            $this->writeLog($e);
            return [];
        }        
    }
    public function getProductKindIdByCode($code){
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $eavConfig = $objectManager->get("Magento\Eav\Model\Config");
        $attribute = $eavConfig->getAttribute('catalog_product', 'product_kind');
        $getAllOptions = $attribute->getSource()->getAllOptions();
        foreach ($getAllOptions as $option) {
            if($option["label"] == $code){
                return $option["value"];
            }
        }
        return 0;
    }
    public function getValueDefaultProductKind(){
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $tableName = $resource->getTableName('eav_attribute');
        $select =$resource->getConnection()->select()->from($tableName)
            ->where('attribute_code = ?', 'product_kind');
        $items = $resource->getConnection()->fetchRow($select);
        return $items['default_value'];
    }
   
    /**
     * @param $info
     * @param $type  [error, warning, info]
     * @return
     */
    private function writeLog($info, $type = "info") {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/sales_order.log');
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