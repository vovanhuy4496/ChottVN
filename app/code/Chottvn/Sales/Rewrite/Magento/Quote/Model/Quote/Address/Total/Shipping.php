<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Chottvn\Sales\Rewrite\Magento\Quote\Model\Quote\Address\Total;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Model\Quote\Address\FreeShippingInterface;

/**
 * Collect totals for shipping.
 */
class Shipping extends \Magento\Quote\Model\Quote\Address\Total\Shipping
{
    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var FreeShippingInterface
     */
    protected $freeShipping;

    /**
     * @param PriceCurrencyInterface $priceCurrency
     * @param FreeShippingInterface $freeShipping
     */
    public function __construct(
        PriceCurrencyInterface $priceCurrency,
        FreeShippingInterface $freeShipping
    ) {
        $this->priceCurrency = $priceCurrency;
        $this->freeShipping = $freeShipping;
        $this->setCode('shipping');
    }

    /**
     * Collect totals information about shipping
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return $this
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);

        $address = $shippingAssignment->getShipping()->getAddress();
        $method = $shippingAssignment->getShipping()->getMethod();

        $total->setTotalAmount($this->getCode(), 0);
        $total->setBaseTotalAmount($this->getCode(), 0);

        if (!count($shippingAssignment->getItems())) {
            return $this;
        }
        $data = $this->getAssignmentWeightData($address, $shippingAssignment->getItems());
        $address->setItemQty($data['addressQty']);
        $address->setWeight($data['freeMethodWeight']);
        $address->setFreeMethodWeight($data['freeMethodWeight']);
        $addressFreeShipping = (bool)$address->getFreeShipping();
        $isFreeShipping = $this->freeShipping->isFreeShipping($quote, $shippingAssignment->getItems());
        $address->setFreeShipping($isFreeShipping);
        if (!$addressFreeShipping && $isFreeShipping) {
            $data = $this->getAssignmentWeightData($address, $shippingAssignment->getItems());
            $address->setItemQty($data['addressQty']);
            $address->setWeight($data['freeMethodWeight']);
            $address->setFreeMethodWeight($data['freeMethodWeight']);
        }
        $this->writeLog('#addressWeight: '.$data['addressWeight']);
        $this->writeLog('#freeMethodWeight: '.$data['freeMethodWeight']);
        $address->collectShippingRates();
        $freeMethodWeight = 0;
        if ($method) {
            foreach ($address->getAllShippingRates() as $rate) {
                if ($rate->getCode() == $method) {
                    // $addressWeight = $data['addressWeight'];
                    $freeMethodWeight = $data['freeMethodWeight'];
                    $store = $quote->getStore();
                    $shippingAmountRate = $this->getShippingAmount($address, $freeMethodWeight);
                    $this->writeLog('#rate->getPrice(): '.$rate->getPrice());
                    $this->writeLog('#shippingAmountRate: '.$shippingAmountRate);
                    // $shippingAmountRate = $rate->getPrice();
                    // // check qty > 1 set shipping amount
                    // if ($addressWeight > $freeMethodWeight) {
                    //     $shippingAmountRate = $this->getShippingAmount($address, $freeMethodWeight);
                    // }
                    $amountPrice = $this->priceCurrency->convert(
                        $shippingAmountRate,
                        $store
                    );
                    $total->setTotalAmount($this->getCode(), $amountPrice);
                    $total->setBaseTotalAmount($this->getCode(),  $shippingAmountRate);
                    $shippingDescription = $rate->getCarrierTitle() . ' - ' . $rate->getMethodTitle();
                    $address->setShippingDescription(trim($shippingDescription, ' -'));
                    $total->setBaseShippingAmount($shippingAmountRate);
                    $total->setShippingAmount($amountPrice);
                    $total->setShippingDescription($address->getShippingDescription());
                    break;
                }
            }
        }
        $this->writeLog('---------------------exit---------------------');

        $shippingAmount = 0;
        // neu don hang la free ship
        // addressFreeShipping: free shipping toan bo sp trong cart
        // isFreeShipping: 1 sp free shipping
        if ($isFreeShipping) {
            $quote->setFlagShipping('freeshipping');
            $quote->save();
            $total->setShippingAmount($shippingAmount);
            $total->setBaseShippingAmount($shippingAmount);
            return $this;
        }

        if ($this->isOverWeight($freeMethodWeight, $shippingAssignment->getItems()) == 'over') {
            $quote->setFlagShipping('over');
            $quote->save();
            $total->setShippingAmount($shippingAmount);
            $total->setBaseShippingAmount($shippingAmount);
            return $this;
        }
        $quote->setFlagShipping('accept');
        $quote->save();

        return $this;
    }
    public function getShippingAmount($address, $freeMethodWeight)
    {
        $postCode = $address->getPostcode();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $tablerate = $objectManager->create('Chottvn\OfflineShipping\Model\Tablerate');
        $collection = $tablerate->getCollection()
                                ->addFieldToFilter('condition_value', ['lteq' => $freeMethodWeight])
                                ->addFieldToFilter('dest_country_id', ['eq' => $address->getCountryId()])
                                ->addFieldToFilter(
                                ['dest_zip','dest_zip'],
                                [
                                    ['eq' => $postCode],
                                    ['eq' => '*']
                                ])
                                ->addFieldToFilter('dest_region_id', ['eq' => $address->getRegionId()]);
        $collection->getSelect()->order('dest_zip ASC');
        $collection->getSelect()->order('condition_value ASC');
        $this->writeLog($collection->getSelect()->__toString());
        $getLastItem = $collection->getLastItem();
        $shipping = $getLastItem->getData('price') ? $getLastItem->getData('price'): 0;
        return $shipping;
    }

    public function isOverWeight($totalWeight, $items)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $handlingOverWeightFee = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('carriers/tablerate/handling_over_weight_fee');
        $flag = false;
        foreach ($items as $item) {
            $productType = $item->getProductType();
            $itemQuote = $objectManager->get('Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory');
            $itemQuoteCollection = $itemQuote->create()->addFieldToFilter('item_id',$item->getId())->addFieldToFilter('product_type','simple');
            $lastItemQuote = $itemQuoteCollection->getLastItem();
            $parentItemId = $lastItemQuote->getData('parent_item_id');
            if (($productType == 'simple' || $productType == 'configurable') && !$parentItemId) {
                $product = $item->getProduct();
                $weight = $product->getWeight() ? floatval($product->getWeight()) : -1;
                if ($weight == 0) {
                    $flag = true;
                    break;
                }
            }
        }
       
        if ($totalWeight > $handlingOverWeightFee || $flag == true) {
            return "over";
        }
        return "accept";
    }

    public function checkWeight()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $checkoutSession = $objectManager->get('Magento\Checkout\Model\Session');
        // $ruleResource = $objectManager->get('Magento\CatalogRule\Model\ResourceModel\Rule');
        $totalWeight = 0;
        $items = $checkoutSession->getQuote()->getAllItems();
        $rule = false;

        foreach ($items as $item) {
            $arrayAppliedRuleIds = explode(',', $item->getAppliedRuleIds());
            // $this->writeLog($arrayAppliedRuleIds);
            // neu sp la free ship => ko tinh weight sp do
            $rule = $this->checkProductHaveFreeShip($arrayAppliedRuleIds);
            if ($item->getQty() > 0 && empty($item->getAmpromoRuleId()) && !$rule) {
                // $this->writeLog($item->getWeight());
                $totalWeight = $totalWeight + ($item->getWeight() * $item->getQty());
            }
        }

        return $totalWeight;
    }

    // kiem tra xem sp co phai la free ship hay ko?
    public function checkProductHaveFreeShip($salesruleIds)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $productHaveFreeShip = 0;

        foreach($salesruleIds as $salesruleId) {
            if ($salesruleId) {
                $salesRule = $objectManager->get('\Magento\SalesRule\Model\Rule');
                $rule = $salesRule->load($salesruleId);
                if ($rule && $rule->getIsActive()) {
                    // $this->writeLog($rule->getName());
                    // $this->writeLog('------------------------------------');
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
     * Add shipping totals information to address object
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function fetch(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total)
    {
        // $this->writeLog(($quote->getShippingAddress()->getRegionId()));
        $flagShipping = __('Price is calculating');
        $getFlagShipping = $quote->getFlagShipping();
        $amount = 0;
        // neu nhu chua chon address
        if (empty($quote->getShippingAddress()->getRegionId())) {
            $flagShipping = __('Not included yet');
        }
        if ($getFlagShipping == 'freeshipping') {
            $flagShipping = __('Free Shipping');
        }
        if ($getFlagShipping == 'over') {
            $flagShipping = __('Price Contact');
        }
        if ($getFlagShipping == 'accept' && $total->getShippingAmount() > 0) {
            $flagShipping = '';
            $amount = $total->getShippingAmount();
        }
        if ($getFlagShipping == 'accept' && $total->getShippingAmount() == 0) {
            $flagShipping = __('Not included yet');
        }
        $shippingDescription = $total->getShippingDescription();
        $title = ($shippingDescription)
            ? __('Shipping & Handling (%1)', $shippingDescription)
            : __('Shipping & Handling');
        return [
            'code' => $this->getCode(),
            'title' => $title,
            'value' => $amount,
            'area' => $flagShipping
        ];
    }

    /**
     * Get Shipping label
     *
     * @return \Magento\Framework\Phrase
     */
    public function getLabel()
    {
        return __('Shipping');
    }

    /**
     * Gets shipping assignments data like items weight, address weight, items quantity.
     *
     * @param AddressInterface $address
     * @param array $items
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function getAssignmentWeightData(AddressInterface $address, array $items): array
    {
        // $address->setWeight(0);
        $address->setFreeMethodWeight(0);
        $addressWeight = $address->getWeight();
        $this->writeLog('$address->getWeight(): '.$address->getWeight());
        $freeMethodWeight = $address->getFreeMethodWeight();
        $this->writeLog('$address->getFreeMethodWeight(): '.$address->getFreeMethodWeight());
        $addressFreeShipping = (bool)$address->getFreeShipping();
        $addressQty = 0;
        foreach ($items as $item) {
            $this->writeLog('item->getProduct()->getId(): '.$item->getProduct()->getId());
            $this->writeLog('isVirtual: '.$item->getProduct()->isVirtual());
            /**
             * Skip if this item is virtual
             */
            if ($item->getProduct()->isVirtual()) {
                continue;
            }
            /**
             * Children weight we calculate for parent
             */
            if ($item->getParentItem()) {
                $this->writeLog('item->getParentItem: die() ---------------');
                continue;
            }

            $itemQty = (float)$item->getQty();
            $itemWeight = (float)$item->getWeight();
            $this->writeLog('itemQty: '. $itemQty);
            $this->writeLog('itemWeight: '. $itemWeight);

            if ($item->getHasChildren() && $item->isShipSeparately()) {
                foreach ($item->getChildren() as $child) {
                    // $this->writeLog('getHasChildren');
                    if ($child->getProduct()->isVirtual()) {
                        continue;
                    }
                    $addressQty += $child->getTotalQty();

                    if (!$item->getProduct()->getWeightType()) {
                        $itemWeight = (float)$child->getWeight();
                        $itemQty = (float)$child->getTotalQty();
                        $addressWeight += ($itemWeight * $itemQty);
                        $rowWeight = $this->getItemRowWeight(
                            $addressFreeShipping,
                            $itemWeight,
                            $itemQty,
                            $child->getFreeShipping()
                        );
                        $freeMethodWeight += $rowWeight;
                        $item->setRowWeight($rowWeight);
                    }
                }
                // $this->writeLog('getWeightType: '. $item->getProduct()->getWeightType());
                if ($item->getProduct()->getWeightType()) {
                    $addressWeight += ($itemWeight * $itemQty);
                    $rowWeight = $this->getItemRowWeight(
                        $addressFreeShipping,
                        $itemWeight,
                        $itemQty,
                        $item->getFreeShipping()
                    );
                    $freeMethodWeight += $rowWeight;
                    $item->setRowWeight($rowWeight);
                }
            } else {
                if (!$item->getProduct()->isVirtual()) {
                    $addressQty += $itemQty;
                }
                $addressWeight += ($itemWeight * $itemQty);
                $this->writeLog('addressWeight: '.$addressWeight);
                $rowWeight = $this->getItemRowWeight(
                    $addressFreeShipping,
                    $itemWeight,
                    $itemQty,
                    $item->getFreeShipping()
                );
                $this->writeLog('freeMethodWeight = '.$freeMethodWeight.' + '.$rowWeight);
                $freeMethodWeight += $rowWeight;
                $item->setRowWeight($rowWeight);
            }
        }
        $this->writeLog('sum freeMethodWeight: '.$freeMethodWeight);
        return [
            'addressQty' => $addressQty,
            'addressWeight' => $addressWeight,
            'freeMethodWeight' => $freeMethodWeight
        ];
    }

    /**
     * Calculates item row weight.
     *
     * @param bool $addressFreeShipping
     * @param float $itemWeight
     * @param float $itemQty
     * @param bool $freeShipping
     * @return float
     */
    private function getItemRowWeight(
        bool $addressFreeShipping,
        float $itemWeight,
        float $itemQty,
        $freeShipping
    ): float {
        $rowWeight = $itemWeight * $itemQty;
        if ($addressFreeShipping || $freeShipping === true) {
            $rowWeight = 0;
        } elseif (is_numeric($freeShipping)) {
            $freeQty = $freeShipping;
            $this->writeLog('getItemRowWeight->itemQty: '. $itemQty);
            $this->writeLog('getItemRowWeight->freeQty: '. $freeQty);
            if ($itemQty > $freeQty) {
                $rowWeight = $itemWeight * ($itemQty - $freeQty);
            } else {
                $rowWeight = 0;
            }
        }
        $this->writeLog('rowWeight: '.$rowWeight);
        return (float)$rowWeight;
    }

    /**
	* @param $info
	* @param $type  [error, warning, info]
	* @return 
	*/
	private function writeLog($info, $type = "info") {
		$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/quote_shipping.log');
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
