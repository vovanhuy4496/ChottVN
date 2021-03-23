<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Chottvn\Sales\Rewrite\Magento\Sales\Block\Order;

use Magento\Sales\Model\Order;

/**
 * @api
 * @since 100.0.2
 */
class Totals extends \Magento\Sales\Block\Order\Totals
{
    /**
     * Associated array of totals
     * array(
     *  $totalCode => $totalObject
     * )
     *
     * @var array
     */
    protected $_totals;

    /**
     * @var Order|null
     */
    protected $_order = null;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    // public function __construct(
    //     \Magento\Framework\View\Element\Template\Context $context,
    //     \Magento\Framework\Registry $registry,
    //     array $data = []
    // ) {
    //     $this->_coreRegistry = $registry;
    //     parent::__construct($context, $data);
    // }

    /**
     * Initialize self totals and children blocks totals before html building
     *
     * @return $this
     */
    protected function _beforeToHtml()
    {
        $this->_initTotals();
        foreach ($this->getLayout()->getChildBlocks($this->getNameInLayout()) as $child) {
            if (method_exists($child, 'initTotals') && is_callable([$child, 'initTotals'])) {
                $child->initTotals();
            }
        }
        return parent::_beforeToHtml();
    }

    /**
     * Get order object
     *
     * @return Order
     */
    public function getOrder()
    {
        if ($this->_order === null) {
            if ($this->hasData('order')) {
                $this->_order = $this->_getData('order');
            } elseif ($this->_coreRegistry->registry('current_order')) {
                $this->_order = $this->_coreRegistry->registry('current_order');
            } elseif ($this->getParentBlock()->getOrder()) {
                $this->_order = $this->getParentBlock()->getOrder();
            }
        }
        return $this->_order;
    }

    /**
     * @param Order $order
     * @return $this
     */
    public function setOrder($order)
    {
        $this->_order = $order;
        return $this;
    }

    /**
     * Get totals source object
     *
     * @return Order
     */
    public function getSource()
    {
        return $this->getOrder();
    }

    /**
     * Initialize order totals array
     *
     * @return $this
     */
    protected function _initTotals()
    {
        $source = $this->getSource();

        $this->_totals = [];

        $this->_totals['original_total'] = new \Magento\Framework\DataObject(
            [
                'code' => 'original_total',
                'value' => $source->getOriginalTotal(),
                'label' => __('Original Total')
            ]
        );
        
        $this->_totals['savings_amount'] = new \Magento\Framework\DataObject(
            [
                'code' => 'savings_amount',
                'value' => $source->getSavingsAmount() > 0 ? $source->getSavingsAmount() : 0,
                'label' => __('Savings Amount'),
            ]
        );
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $quoteId = $this->getOrder()->getQuoteId();
        $quote = $objectManager->create('Magento\Quote\Model\Quote')->load($quoteId);
        $flagShipping = $quote->getFlagShipping();

        $shipping_amount = $this->getSource()->getShippingAmount();
        $grandTotal = __('Grand Total');

        if ($flagShipping === 'freeshipping') {
            $shipping_amount = __('Free Shipping');
        }
        if ($flagShipping === 'over') {
            $grandTotal = __('Grand Total(temp)');
            $shipping_amount = __('Price Contact');
        }
        $this->_totals['shipping'] = new \Magento\Framework\DataObject(
            [
                'code' => 'shipping',
                'field' => 'shipping_amount',
                'value' => $shipping_amount,
                'label' => __('Shipping & Handling'),
            ]
        );

        // $this->_totals['subtotal'] = new \Magento\Framework\DataObject(
        //     ['code' => 'subtotal', 'value' => $source->getSubtotal(), 'label' => __('Subtotal')]
        // );

        /**
         * Add shipping
         */
        // if (!$source->getIsVirtual() && ((double)$source->getShippingAmount() || $source->getShippingDescription())) {
        //     $this->_totals['shipping'] = new \Magento\Framework\DataObject(
        //         [
        //             'code' => 'shipping',
        //             'field' => 'shipping_amount',
        //             'value' => $this->getSource()->getShippingAmount(),
        //             'label' => __('Shipping & Handling'),
        //         ]
        //     );
        // }

        /**
         * Add discount
         */
        if ((double)$this->getSource()->getDiscountAmount() != 0) {
            if ($this->getSource()->getDiscountDescription()) {
                $discountLabel = __('Discount (%1)', $source->getDiscountDescription());
            } else {
                $discountLabel = __('Discount');
            }
            $this->_totals['discount'] = new \Magento\Framework\DataObject(
                [
                    'code' => 'discount',
                    'field' => 'discount_amount',
                    'value' => $source->getDiscountAmount(),
                    'label' => $discountLabel,
                ]
            );
        }

        $this->_totals['grand_total'] = new \Magento\Framework\DataObject(
            [
                'code' => 'grand_total',
                'field' => 'grand_total',
                'strong' => true,
                'value' => $source->getGrandTotal(),
                'label' => $grandTotal,
            ]
        );

        /**
         * Base grandtotal
         */
        if ($this->getOrder()->isCurrencyDifferent()) {
            $this->_totals['base_grandtotal'] = new \Magento\Framework\DataObject(
                [
                    'code' => 'base_grandtotal',
                    'value' => $this->getOrder()->formatBasePrice($source->getBaseGrandTotal()),
                    'label' => __('Grand Total to be Charged'),
                    'is_formated' => true,
                ]
            );
        }

        $checkoutSession = $objectManager->get('Magento\Checkout\Model\Session');
        $_order = $checkoutSession->getLastRealOrder();
        $createdDate = $_order->getCreatedAt();
        // $this->writeLog('#create Order: '.$createdDate);
        $estimatedDeliveryDate = "";
        $timefc = $objectManager->create('\Magento\Framework\Stdlib\DateTime\TimezoneInterface');
        $createdDate = $timefc->date($createdDate)->format('Y-m-d');
        $maxDeliveryDates = $quote->getMaxDeliveryDates();
        // $this->writeLog('#maxDeliveryDates: '.$maxDeliveryDates);
        $estimatedDeliveryDate = date('d/m/Y', strtotime($createdDate . " + ".$maxDeliveryDates." day"));
        if ($flagShipping == 'over') {
            $this->_totals['max_delivery_dates'] = new \Magento\Framework\DataObject(
                [
                    'code' => 'max_delivery_dates',
                    'value' => __('Max Delivery Dates Contact'),
                    'label' => __('Max Delivery Dates '),
                    'is_formated' => true,
                ]
            );
        } else {
            if($maxDeliveryDates){
                $this->_totals['max_delivery_dates'] = new \Magento\Framework\DataObject(
                    [
                        'code' => 'max_delivery_dates',
                        'value' => $estimatedDeliveryDate,
                        'label' => __('Max Delivery Dates '),
                        'is_formated' => true,
                    ]
                );
            }
        }
        return $this;
    }

    /**
     * Add new total to totals array after specific total or before last total by default
     *
     * @param   \Magento\Framework\DataObject $total
     * @param   null|string $after
     * @return  $this
     */
    public function addTotal(\Magento\Framework\DataObject $total, $after = null)
    {
        if ($after !== null && $after != 'last' && $after != 'first') {
            $totals = [];
            $added = false;
            foreach ($this->_totals as $code => $item) {
                $totals[$code] = $item;
                if ($code == $after) {
                    $added = true;
                    $totals[$total->getCode()] = $total;
                }
            }
            if (!$added) {
                $last = array_pop($totals);
                $totals[$total->getCode()] = $total;
                $totals[$last->getCode()] = $last;
            }
            $this->_totals = $totals;
        } elseif ($after == 'last') {
            $this->_totals[$total->getCode()] = $total;
        } elseif ($after == 'first') {
            $totals = [$total->getCode() => $total];
            $this->_totals = array_merge($totals, $this->_totals);
        } else {
            $last = array_pop($this->_totals);
            $this->_totals[$total->getCode()] = $total;
            $this->_totals[$last->getCode()] = $last;
        }
        return $this;
    }

    /**
     * Add new total to totals array before specific total or after first total by default
     *
     * @param   \Magento\Framework\DataObject $total
     * @param   null|string $before
     * @return  $this
     */
    public function addTotalBefore(\Magento\Framework\DataObject $total, $before = null)
    {
        if ($before !== null) {
            if (!is_array($before)) {
                $before = [$before];
            }
            foreach ($before as $beforeTotals) {
                if (isset($this->_totals[$beforeTotals])) {
                    $totals = [];
                    foreach ($this->_totals as $code => $item) {
                        if ($code == $beforeTotals) {
                            $totals[$total->getCode()] = $total;
                        }
                        $totals[$code] = $item;
                    }
                    $this->_totals = $totals;
                    return $this;
                }
            }
        }
        $totals = [];
        $first = array_shift($this->_totals);
        $totals[$first->getCode()] = $first;
        $totals[$total->getCode()] = $total;
        foreach ($this->_totals as $code => $item) {
            $totals[$code] = $item;
        }
        $this->_totals = $totals;
        return $this;
    }

    /**
     * Get Total object by code
     *
     * @param string $code
     * @return mixed
     */
    public function getTotal($code)
    {
        if (isset($this->_totals[$code])) {
            return $this->_totals[$code];
        }
        return false;
    }

    /**
     * Delete total by specific
     *
     * @param   string $code
     * @return  $this
     */
    public function removeTotal($code)
    {
        unset($this->_totals[$code]);
        return $this;
    }

    /**
     * Apply sort orders to totals array.
     * Array should have next structure
     * array(
     *  $totalCode => $totalSortOrder
     * )
     *
     *
     * @param   array $order
     * @return  $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function applySortOrder($order)
    {
        \uksort(
            $this->_totals,
            function ($code1, $code2) use ($order) {
                return ($order[$code1] ?? 0) <=> ($order[$code2] ?? 0);
            }
        );
        return $this;
    }

    /**
     * get totals array for visualization
     *
     * @param array|null $area
     * @return array
     */
    public function getTotals($area = null)
    {
        $totals = [];
        if ($area === null) {
            $totals = $this->_totals;
        } else {
            $area = (string)$area;
            foreach ($this->_totals as $total) {
                $totalArea = (string)$total->getArea();
                if ($totalArea == $area) {
                    $totals[] = $total;
                }
            }
        }
        return $totals;
    }

    /**
     * Format total value based on order currency
     *
     * @param   \Magento\Framework\DataObject $total
     * @return  string
     */
    public function formatValue($total)
    {
        if (!$total->getIsFormated()) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $checkoutHelper = $objectManager->get('Chottvn\PriceDecimal\Helper\Data');
            return $checkoutHelper->formatPrice($total->getValue());
            // return $this->getOrder()->formatPrice($total->getValue());
        }
        return $total->getValue();
    }

    /**
	* @param $info
	* @param $type  [error, warning, info]
	* @return 
	*/
	private function writeLog($info, $type = "info") {
		$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/default_config_provider.log');
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
