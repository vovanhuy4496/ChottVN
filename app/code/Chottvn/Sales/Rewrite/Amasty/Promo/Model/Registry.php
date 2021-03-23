<?php
/**
 * @author 
 * @copyright 
 * @package Chottvn_Sales
 */


namespace Chottvn\Sales\Rewrite\Amasty\Promo\Model;

class Registry extends \Amasty\Promo\Model\Registry
{
    protected $_hasItems = false;
    protected $_locked = false;
    protected $_isHandled = [];
    protected $autoAddTypes = ['simple', 'virtual', 'downloadable'];

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Amasty\Promo\Helper\Item
     */
    protected $promoItemHelper;

    /**
     * @var \Amasty\Promo\Helper\Messages
     */
    protected $promoMessagesHelper;

    /**
     * @var \Amasty\Promo\Model\Config
     */
    private $config;

    /**
     * @var \Magento\Store\Model\Store
     */
    private $store;

    /**
     * @var array
     */
    private $fullDiscountItems;

    /**
     * @var \Amasty\Promo\Model\Product
     */
    private $product;

    /**
     * @var \Amasty\Promo\Model\DiscountCalculator
     */
    private $discountCalculator;

    /**
     * @var array
     */
    private $validItems = [];

    /**
     * @var array
     */
    private $usedRules = [];

    public function __construct(
        \Magento\Checkout\Model\Session $resourceSession,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Amasty\Promo\Helper\Item $promoItemHelper,
        \Amasty\Promo\Helper\Messages $promoMessagesHelper,
        \Amasty\Promo\Model\Config $config,
        \Magento\Store\Model\Store $store,
        \Amasty\Promo\Model\Product $product,
        \Amasty\Promo\Model\DiscountCalculator $discountCalculator
    ) {
        $this->_checkoutSession    = $resourceSession;
        $this->scopeConfig         = $scopeConfig;
        $this->_productFactory     = $productFactory;
        $this->_storeManager       = $storeManager;
        $this->promoItemHelper     = $promoItemHelper;
        $this->promoMessagesHelper = $promoMessagesHelper;
        $this->config              = $config;
        $this->store               = $store;
        $this->fullDiscountItems   = [];
        $this->product = $product;
        $this->discountCalculator = $discountCalculator;
    }

    public function getApplyAttempt($ruleId)
    {
        if (isset($this->_isHandled[$ruleId])) {
            return false;
        }
        $this->_isHandled[$ruleId] = true;

        return true;
    }

    /**
     * @param $sku
     * @param $qty
     * @param $ruleId
     * @param $discountData
     * @param $type
     * @param $discountAmount
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function addPromoItem($sku, $qty, $ruleId, $discountData, $type, $discountAmount)
    {
        if ($this->_locked) {
            return;
        }

        if (!$this->_hasItems) {
            $this->reset();
        }

        $discountData = $this->getCurrencyDiscount($discountData);

        $this->_hasItems = true;
        $items = $this->getPromoItems();
        $autoAdd = false;

        $isFullDiscount = $this->discountCalculator->isFullDiscount($discountData);

        if (is_array($sku) && count($sku) == 1) {
            $sku = $sku[0];
        }

        if (!is_array($sku)) {
            $productQty = $this->product->getProductQty($sku);

            if ($productQty !== false && $qty > $productQty) {
                $qty = $productQty;
            }

            if ($this->discountCalculator->isEnableAutoAdd($discountData)) {
                $collection = $this->_productFactory->create()->getCollection()
                    ->addAttributeToSelect(['name', 'status', 'required_options'])
                    ->addAttributeToFilter('sku', $sku)
                    ->setPage(1, 1);

                $product = $collection->getFirstItem();

                $currentWebsiteId = $this->_storeManager->getWebsite()->getId();
                if (!is_array($product->getWebsiteIds())
                    || !in_array($currentWebsiteId, $product->getWebsiteIds())
                ) {
                    // Ignore products from other websites
                    return;
                }

                if (!$product || !$product->isInStock() || !$product->isSalable()) {
                    $this->promoMessagesHelper->addAvailabilityError($product);
                } else {
                    if (in_array($product->getTypeId(), $this->autoAddTypes)
                        && !$product->getTypeInstance(true)->hasRequiredOptions($product)
                    ) {
                        $autoAdd = true;
                    }
                }
            }

            if (isset($items[$ruleId])) {
                $items[$ruleId]['sku'] += [
                    $sku => [
                        'sku' => $sku,
                        'qty' => $qty,
                        'auto_add' => $autoAdd,
                        'discount' => $discountData,
                    ],
                ];
            } elseif (isset($items[$ruleId]) && isset($items[$ruleId]['sku'][$sku])) {
                $items[$ruleId][$sku]['qty'] += $qty;
            } else {
                $items[$ruleId] = [
                    'sku' => [
                        $sku => [
                            'qty' => $qty,
                            'auto_add' => $autoAdd,
                            'discount' => $discountData,
                        ],
                    ],
                    'rule_type' => $type,
                    'discount_amount' => $discountAmount
                ];
            }
        } else {
            $items['_groups'][$ruleId] = [
                'sku' => $sku,
                'qty' => $qty,
                'discount' => $discountData,
                'rule_type' => $type,
                'discount_amount' => $discountAmount
            ];
        }

        if ($isFullDiscount) {
            if (!is_array($sku)) {
                $sku = [$sku];
            }

            foreach ($sku as $itemSku) {
                $this->fullDiscountItems[$itemSku]['rule_ids'][] = $ruleId;
            }
        }

        $this->_checkoutSession->setAmpromoFullDiscountItems($this->fullDiscountItems);
        $this->_checkoutSession->setAmpromoItems($items);
    }

    /**
     * @param $discountData
     * @return mixed
     */
    private function getCurrencyDiscount($discountData)
    {
        preg_match('/^-*\d+.*\d*$/', $discountData['discount_item'], $discount);
        if (isset($discount[0]) && is_numeric($discount[0])) {
            $discountData['discount_item'] = $discount[0] * $this->store->getCurrentCurrencyRate();
        }

        return $discountData;
    }

    public function setPromoItems($items)
    {
        $this->_checkoutSession->setAmpromoItems($items);
    }

    public function getPromoItems()
    {
        $items = $this->_checkoutSession->getAmpromoItems();

        if (!$items) {
            return ['_groups' => []];
        }

        return $items;
    }

    /**
     * @param int $ruleId
     * @param int $itemId
     */
    public function addValidItem($ruleId, $itemId)
    {
        $this->validItems[$ruleId][$itemId] = $itemId;
    }

    /**
     * @param \Magento\SalesRule\Model\Rule $rule
     */
    public function addRuleUsed($rule)
    {
        $this->usedRules[$rule->getId()] = $rule;
    }

    /**
     * @param int $ruleId
     * @param int $itemId
     *
     * @return bool
     */
    public function isValidRuleItem($ruleId, $itemId)
    {
        return isset($this->validItems[$ruleId][$itemId]);
    }

    /**
     * @return array
     */
    public function getUsedRules()
    {
        return $this->usedRules;
    }

    public function reset()
    {
        if ($this->_hasItems) {
            $this->_locked = true;
            return;
        }

        $this->_checkoutSession->setAmpromoItems(['_groups' => []]);
    }

    public function getLimits()
    {
        $allowed = null;
        $quote   = $this->_checkoutSession->getQuote();

        if ($quote->getId() > 0) {
            $allowed = $this->getPromoItems();
            foreach ($quote->getAllItems() as $item) {
                $sku = $item->getProduct()->getData('sku');

                if ($this->promoItemHelper->isPromoItem($item)) {
                    $ruleId = $this->promoItemHelper->getRuleId($item);

                    if (isset($allowed['_groups'][$ruleId])) {
                        if ($item->getParentItem()) {
                            continue;
                        }

                        $allowed['_groups'][$ruleId]['qty'] -= $item->getQty();
                        if ($allowed['_groups'][$ruleId]['qty'] <= 0) {
                            unset($allowed['_groups'][$ruleId]);
                        }
                    } else {
                        $groups = $allowed['_groups'];
                        unset($allowed['_groups']);
                        foreach ($allowed as $allowedRuleId => &$rule) {
                            if (isset($rule['sku'][$sku]) && $allowedRuleId === $ruleId) {
                                $rule['sku'][$sku]['qty'] -= $item->getQty();

                                if ($rule['sku'][$sku]['qty'] <= 0) {
                                    unset($allowed[$allowedRuleId]['sku'][$sku]);
                                }
                            }
                        }

                        $allowed['_groups'] = $groups;
                    }
                }
            }
        }        

        //return $allowed;
        return $this->filterOutAmpromoItemRules($allowed);
    }

    public function deleteProduct($sku)
    {
        $deletedItems = $this->_checkoutSession->getAmpromoDeletedItems();
        $fullDiscountItems = $this->_checkoutSession->getAmpromoFullDiscountItems();

        if (!$deletedItems) {
            $deletedItems = [];
        }

        $deletedItems[$sku] = true;

        $this->_checkoutSession->setAmpromoDeletedItems($deletedItems);

        if (isset($fullDiscountItems[$sku])) {
            unset($fullDiscountItems[$sku]);
            $this->_checkoutSession->setAmpromoFullDiscountItems($fullDiscountItems);
        }
    }

    public function restore($sku)
    {
        $deletedItems = $this->_checkoutSession->getAmpromoDeletedItems();

        if (!$deletedItems || !isset($deletedItems[$sku])) {
            return;
        }

        unset($deletedItems[$sku]);

        $this->_checkoutSession->setAmpromoDeletedItems($deletedItems);
    }

    public function getDeletedItems()
    {
        $deletedItems = $this->_checkoutSession->getAmpromoDeletedItems();

        if (!$deletedItems) {
            $deletedItems = [];
        }

        return $deletedItems;
    }



    /*
     * Filter ampromo_items
     */
    private function filterOutAmpromoItemRules($rules){
        if (empty($rules)){
            return $rules;
        }
        $finalAllowedItems = [
            "_groups" => []
        ];
        foreach ($rules as $key => $value) {
            if($key == "_groups"){
                foreach ($value as $keySub => $valueSub) {
                    if (! $this->shouldRemoveFromOption($keySub)){
                        $finalAllowedItems["_groups"][$keySub]=$valueSub;
                    }
                }
            }else{
                if (! $this->shouldRemoveFromOption($key)){
                    $finalAllowedItems[$key]=$value;
                }
            }
        }
        return $finalAllowedItems;        
    }

    private function getPromoRuleInfo($ruleId){
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
            $this->writeLog($e);
            return null;
        }        
    }


    private function shouldRemoveFromOption($ruleId){        
        $promoInfo = $this->getPromoRuleInfo($ruleId);        
        if(empty($promoInfo)){
            return true;
        }
        if ($promoInfo["simple_action"] == "ampromo_items"){
            return true;
        }
        /*
        if ($promoInfo["simple_action"] == "ampromo_cart"
            && $promoInfo["type"] == 0){ // Auto add all items            
            return true;
        }*/
        return false;
    }

    /**
     * @param $info
     * @param $type  [error, warning, info]
     * @return
     */
    private function writeLog($info, $type = "info") {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/chottvn_sales_amasty.log');
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
