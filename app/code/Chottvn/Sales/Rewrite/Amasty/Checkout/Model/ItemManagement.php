<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Checkout
 */


namespace Chottvn\Sales\Rewrite\Amasty\Checkout\Model;

use Amasty\Checkout\Api\ItemManagementInterface;
use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\Catalog\Model\Product\Configuration\Item\ItemResolverInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\CartTotalRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\TotalsInterface;
use Magento\Quote\Api\PaymentMethodManagementInterface;
use Magento\Quote\Api\ShipmentEstimationInterface;
use Magento\Quote\Model\Cart\ShippingMethod;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Zend\Uri\Uri;

class ItemManagement extends \Amasty\Checkout\Model\ItemManagement
{
    /**
     * @var CartRepositoryInterface
     */
    protected $cartRepository;

    /**
     * @var CartTotalRepositoryInterface
     */
    protected $cartTotalRepository;

    /**
     * @var CustomerCart
     */
    protected $cart;

    /**
     * @var TotalsFactory
     */
    protected $totalsFactory;

    /**
     * @var PaymentMethodManagementInterface
     */
    protected $paymentMethodManagement;

    /**
     * @var ShipmentEstimationInterface
     */
    protected $shipmentEstimation;

    /**
     * @var ItemResolverInterface
     */
    private $itemResolver;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Uri
     */
    private $zendUri;

    public function __construct(
        CartRepositoryInterface $cartRepository,
        CartTotalRepositoryInterface $cartTotalRepository,
        CustomerCart $cart,
        \Amasty\Checkout\Model\TotalsFactory $totalsFactory,
        ShipmentEstimationInterface $shipmentEstimation,
        PaymentMethodManagementInterface $paymentMethodManagement,
        ObjectManagerInterface $objectManager,
        Uri $zendUri
    ) {
        $this->cartRepository = $cartRepository;
        $this->cartTotalRepository = $cartTotalRepository;
        $this->cart = $cart;
        $this->totalsFactory = $totalsFactory;
        $this->paymentMethodManagement = $paymentMethodManagement;
        $this->shipmentEstimation = $shipmentEstimation;
        $this->objectManager = $objectManager;
        $this->zendUri = $zendUri;

        if (interface_exists(ItemResolverInterface::class)) {
            $this->itemResolver = $this->objectManager->get(ItemResolverInterface::class);
        }
    }

    /**
     * @inheritdoc
     */
    public function remove($cartId, $itemId, AddressInterface $address)
    {
        /** @var Quote $quote */
        $quote = $this->cartRepository->get($cartId);
        $initialVirtualState = $quote->isVirtual();
        /** @var QuoteItem $item */
        $item = $quote->getItemById($itemId);

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $quoteFactory = $objectManager->create('Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory');
        $quoteItems = $quoteFactory->create()->addFieldToFilter('cart_promo_parent_item_id', $itemId);

        if (count($quoteItems->getData()) > 0) {
            foreach ($quoteItems as $quoteItem) {
                // $this->writeLog('remove $quoteItem->getId(): '.$quoteItem->getId());
                if ($quoteItem && $quoteItem->getId()) {
                    $quote->deleteItem($quoteItem);
                }
            }
        }

        if ($item && $item->getId()) {
            $quote->deleteItem($item);
            $this->cartRepository->save($quote);
        }

        if ($quote->isVirtual() && !$initialVirtualState) {
            return false;
        }

        /** @var ShippingMethod $shippingMethods */
        $shippingMethods = $this->shipmentEstimation->estimateByExtendedAddress($cartId, $address);
        /** @var Totals $totals */
        $totals = $this->totalsFactory->create(
            [
                'data' => [
                    'totals' => $this->cartTotalRepository->get($cartId),
                    'shipping' => $shippingMethods,
                    'payment' => $this->paymentMethodManagement->getList($cartId)
                ]
            ]
        );

        return $totals;
    }

    /**
     * @inheritdoc
     */
    public function update($cartId, $itemId, $formData)
    {
        /** @var Quote $quote */
        $quote = $this->cartRepository->get($cartId);
        $initialVirtualState = $quote->isVirtual();

        $this->cart->setQuote($quote);
        $params = $this->parseStr($formData);
        $inputQty = (int)$params['qty'];
        /** @var QuoteItem $item */
        $item = $this->cart->getQuote()->getItemById($itemId);

        if (!$item) {
            throw new LocalizedException(__('We can\'t find the quote item.'));
        }

        $params = $this->prepareParams($params, $itemId);

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $checkoutSession = $objectManager->get('Magento\Checkout\Model\Session');
        $getQuotes = $checkoutSession->getQuote();
        $quoteId = $getQuotes->getId();

        $quoteFactory = $objectManager->create('Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory');
        $productRepository = $objectManager->get('Magento\Catalog\Api\ProductRepositoryInterface');
        $quoteItems = $quoteFactory->create()->addFieldToFilter('quote_id', $quoteId)
                                            ->addFieldToFilter('cart_promo_parent_item_id', $itemId);
        // $productMainOfConfig = $quoteFactory->create()->addFieldToFilter('quote_id', $quoteId)
        //                                     ->addFieldToFilter('parent_item_id', array('notnull' => true));
        // $this->writeLog($productMainOfConfig->getSelect()->__toString());

        if (count($quoteItems->getData()) > 0) {
            $itemUpdate = $quote->getItemById($itemId);
            $itemUpdate->setQty((int)$inputQty);
            $itemUpdate->save();

            $this->cart->save();

            foreach ($quoteItems as $item) {
                $updateQtyPromo = (int)$inputQty * (int)$item->getCartPromoQty();

                // $this->writeLog('update $item->getId(): '.$item->getId());

                $itemUpdate = $quote->getItemById($item->getId());
                if (!$itemUpdate) {
                    continue;
                }
                $itemUpdate->setQty($updateQtyPromo);
                $itemUpdate->save();
            }
        } else {
            $item = $this->cart->updateItem($itemId, new DataObject($params));
        }

        // if (count($productMainOfConfig->getData()) > 0) {
        //     // update qty product chinh cua product config
        //     foreach ($productMainOfConfig as $item) {
        //         if (!empty($item->getParentItemId())) {
        //             $productConfig = $quote->getItemById($item->getParentItemId());
        //             if (!$productConfig) {
        //                 continue;
        //             }
        //             if (!empty($productConfig->getQty())) {
        //                 $updateQty = $productConfig->getQty();
        //                 $productSimple = $quote->getItemById($item->getId());
        //                 $productSimple->setQty((int)$updateQty);
        //                 $productSimple->save();
        //             }
        //         }
        //     }
        // }

        if (is_string($item)) {
            throw new LocalizedException(__($item));
        }
        if ($item->getHasError()) {
            throw new LocalizedException(__($item->getMessage()));
        }

        if (count($quoteItems->getData()) > 0) {
        } else {
            $this->cart->save();
        }

        if ($quote->isVirtual() && !$initialVirtualState) {
            return false;
        }

        /** @var TotalsInterface[] $items */
        $items = $this->cartTotalRepository->get($cartId);
        // $getItems = $items->getItems();
        // foreach ($getItems as $index => $item) {
        //     $getItem = $quote->getItemById($item->getItemId());
        //     if (!empty($getItem->getCartPromoOption())) {
        //         $item->getExtensionAttributes()->getAmastyPromo()->setImageAlt($getItem->getCartPromoOption());
        //     }
        // }
        // $items = $items->setItems($getItems);
        
        return $this->totalsFactory->create(['data' => [
            'totals' => $items,
            'payment' => $this->paymentMethodManagement->getList($cartId)
        ]]);
    }

    /**
     * @param string $str
     *
     * @return array
     */
    public function parseStr($str)
    {
        $this->zendUri->setQuery($str);
        $params = $this->zendUri->getQueryAsArray();

        return $params;
    }

    /**
     * @param array $params
     * @param int $itemId
     *
     * @return array
     */
    private function prepareParams($params, $itemId)
    {
        if (isset($params['qty'])) {
            $params['qty'] = (int)$params['qty'];
            $params['reset_count'] = true;
        }

        if (!isset($params['options'])) {
            $params['options'] = [];
        }

        $params['id'] = $itemId;

        return $params;
    }

    /**
	* @param $info
	* @param $type  [error, warning, info]
	* @return 
	*/
	private function writeLog($info, $type = "info") {
		$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/Promo_QuoteItem.log');
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
