<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Promo
 */


namespace Amasty\Promo\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Amasty\Promo\Model\Registry
     */
    protected $promoRegistry;

    /**
     * @var \Amasty\Promo\Helper\Messages
     */
    protected $promoMessagesHelper;

    /**
     * @var \Amasty\Promo\Helper\Cart
     */
    protected $promoCartHelper;

    protected $_productsCache = null;

    protected $_allowedTypes = [
        'simple',
        'configurable',
        'virtual',
        'downloadable',
        'giftcard',
    ];

    /**
     * @var \Amasty\Promo\Model\Product
     */
    private $product;

    private $promoSku = [];

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $session;

    /**
     * @var \Amasty\Promo\Helper\Item
     */
    private $promoItemHelper;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    private $collectionFactory;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Amasty\Promo\Model\Registry $promoRegistry,
        \Amasty\Promo\Helper\Messages $promoMessagesHelper,
        \Amasty\Promo\Helper\Cart $promoCartHelper,
        \Amasty\Promo\Model\Product $product,
        \Magento\Checkout\Model\Session $session,
        \Amasty\Promo\Helper\Item $promoItemHelper,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory
    ) {
        parent::__construct($context);

        $this->promoRegistry = $promoRegistry;
        $this->promoMessagesHelper = $promoMessagesHelper;
        $this->promoCartHelper = $promoCartHelper;
        $this->product = $product;
        $this->session = $session;
        $this->promoItemHelper = $promoItemHelper;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @return bool|null
     */
    public function getNewItems()
    {
        if ($this->_productsCache === null) {
            $ruleItems = $this->promoRegistry->getLimits();
            $allowedSku = [];

            $groups = $ruleItems['_groups'];
            unset($ruleItems['_groups']);

            if (!$ruleItems && !$groups) {
                $this->_productsCache = false;

                return false;
            }

            foreach ($ruleItems as $ruleItem) {
                if (isset($ruleItem['sku'])) {
                    $allowedSku = array_merge($allowedSku, array_keys($ruleItem['sku']));
                }
            }

            foreach ($groups as $rule) {
                $allowedSku = array_merge($allowedSku, $rule['sku']);
            }

            foreach ($allowedSku as $key => $value) {
                $allowedSku[$key] = (string)$value;
            }

            $products = $this->collectionFactory->create()
                ->addAttributeToSelect(['name', 'small_image', 'status', 'visibility'])
                ->addFieldToFilter('sku', ['in' => $allowedSku])
                ->setFlag('has_stock_status_filter', false);

            foreach ($products as $key => $product) {
                if (!in_array($product->getTypeId(), $this->_allowedTypes)) {
                    $this->promoMessagesHelper->showMessage(__(
                        "We apologize, but products of type <strong>%1</strong> are not supported",
                        $product->getTypeId()
                    ));

                    $products->removeItemByKey($key);
                }

                if ($product->getTypeId() == 'simple' && (!$product->isSalable()
                    || !$this->promoCartHelper->checkAvailableQty($product, 1))
                ) {
                    $this->promoMessagesHelper->addAvailabilityError($product);

                    $products->removeItemByKey($key);
                }

                foreach ($product->getProductOptionsCollection() as $option) {
                    $option->setProduct($product);
                    $product->addOption($option);
                }

                $this->recursiveFindDiscount($ruleItems, $product);
            }

            if ($products->getSize() > 0) {
                $this->_productsCache = $products;
            } else {
                $this->_productsCache = false;
            }
        }

        return $this->_productsCache;
    }

    /**
     * @param $ruleItems
     * @param $product
     *
     * @return bool
     */
    private function recursiveFindDiscount($ruleItems, $product)
    {
        $iterator  = new \RecursiveArrayIterator($ruleItems);
        $recursive = new \RecursiveIteratorIterator(
            $iterator,
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($recursive as $key => $value) {
            if ($key === $product->getSku()) {
                $product->setAmpromoDiscount($value['discount']);
            }
        }

        return true;
    }

    /**
     * @return null
     */
    public function getAllowedProductQty()
    {
        $result = [];
        $rulesItems = $this->promoRegistry->getLimits();
        $discountData = [];
        $qty = 0;

        if (isset($rulesItems['_groups'])) {
            $rules = $rulesItems['_groups'];
            foreach ($rules as $ruleId => $rule) {
                if (isset($rule['sku'])) {
                    $qty += $rule['qty'];
                    $discountData[$ruleId]['rule_type'] = $rule['rule_type'];
                    $discountData[$ruleId]['discount_amount'] = $rule['discount_amount'];
                    foreach ($rule['sku'] as $sku) {
                        $discountData[$ruleId]['sku'][$sku] = [
                            'discount' => $rule['discount'],
                            'qty' => $rule['qty']
                        ];
                    }
                }
            }

            unset($rulesItems['_groups']);
            if (is_array($rulesItems)) {
                foreach ($rulesItems as $key => $ruleItems) {
                    foreach ($ruleItems['sku'] as $sku => $item) {
                        if (isset($item['qty'])) {
                            $productQty = $this->product->getProductQty($sku);

                            if ($productQty !== false && $item['qty'] > $productQty) {
                                $rulesItems[$key]['sku'][$sku]['qty'] = $productQty;
                                $item['qty'] = $productQty;
                            }

                            $qty += $item['qty'];
                        }
                    }
                }
            }

            $discountData += $rulesItems;
            array_map([$this, 'promoSkuMerge'], $discountData);

            $result += [
                'common_qty' => $qty,
                'triggered_products' => $discountData,
                'promo_sku' => $this->promoSku
            ];
        }

        return $result;
    }

    /**
     * @param $item
     *
     * @return bool
     */
    public function promoSkuMerge($item)
    {
        $this->promoSku += $item['sku'];

        return true;
    }
}
