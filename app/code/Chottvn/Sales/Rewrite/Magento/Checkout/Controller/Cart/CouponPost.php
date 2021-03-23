<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Chottvn\Sales\Rewrite\Magento\Checkout\Controller\Cart;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CouponPost extends \Magento\Checkout\Controller\Cart\CouponPost
{
    /**
     * Sales quote repository
     *
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * Coupon factory
     *
     * @var \Magento\SalesRule\Model\CouponFactory
     */
    protected $couponFactory;
    
    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param \Magento\Checkout\Model\Cart $cart
     * @param \Magento\SalesRule\Model\CouponFactory $couponFactory
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\SalesRule\Model\CouponFactory $couponFactory,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
    ) {
        $this->resultFactory = $context->getResultFactory();
        parent::__construct(
            $context,
            $scopeConfig,
            $checkoutSession,
            $storeManager,
            $formKeyValidator,
            $cart,
            $couponFactory,
            $quoteRepository
        );
        // $this->couponFactory = $couponFactory;
        // $this->quoteRepository = $quoteRepository;
    }

    /**
     * Initialize coupon
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $couponCode = $this->getRequest()->getParam('remove') == 1
            ? ''
            : trim($this->getRequest()->getParam('coupon_code'));
        // $this->writeLog($couponCode);

        $cartQuote = $this->cart->getQuote();
        $oldCouponCode = $cartQuote->getCouponCode();

        $codeLength = strlen($couponCode);
        if (!$codeLength && !strlen($oldCouponCode)) {
            return $this->_goBack();
        }

        try {
            $block = $this->_objectManager->get('Chottvn\PriceQuote\Block\Index\Index');
            $isCodeLengthValid = $codeLength && $codeLength <= \Magento\Checkout\Helper\Cart::COUPON_CODE_MAX_LENGTH;
            $itemsCount = $cartQuote->getItemsCount();
            if ($itemsCount) {
                $cartQuote->getShippingAddress()->setCollectShippingRates(true);
                $cartQuote->setCouponCode($isCodeLengthValid ? $couponCode : '')->collectTotals();
                $this->quoteRepository->save($cartQuote);
            }
            if ($codeLength) {
                $escaper = $this->_objectManager->get(\Magento\Framework\Escaper::class);
                $coupon = $this->couponFactory->create();
                $coupon->load($couponCode, 'code');
                // $this->writeLog($couponCode);
                $listCoupon = $this->getDiscounts($couponCode);
                // $this->writeLog($listCoupon);
                if (!$itemsCount) {
                    if ($isCodeLengthValid && $coupon->getId()) {
                        $this->_checkoutSession->getQuote()->setCouponCode($couponCode)->save();
                        $discountAmount = (string) $block->getDiscountAmount();
                        $discountAmount = (int) substr($discountAmount, 1);
                        $isFreeShip = $this->checkHaveFreeShipByCouponCode($couponCode);
                        $value = [
                            'original_total' => $block->formatPrice($block->getOriginalTotal()),
                            'savings_amount' => (int)$block->getSavingsAmount() > 0 ? $block->formatPrice($block->getSavingsAmount()) : 0,
                            'discount_amount' => $block->formatPrice($discountAmount),
                            'shipping_amount' => $block->getCustomShippingAmount(),
                            'title_grand_total' => $block->getCustomTitleGrandTotal($block->getShippingAmount()),
                            'grand_total' =>  $block->formatPrice($block->getGrandTotal()),
                            'list_coupon' =>  $listCoupon,
                            'is_freeship' => $isFreeShip
                        ];
                        $response = $this->resultFactory
                        ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
                        ->setData([
                            'status'  => "success",
                            'message' =>  __(
                                'You used coupon code "%1".',
                                $escaper->escapeHtml($couponCode)
                            ),
                            'value'  => $value
                        ]);
                    } else {
                        $response = $this->resultFactory
                        ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
                        ->setData([
                            'status'  => "error",
                            'message' =>   __(
                                'The coupon code "%1" is not valid.',
                                $escaper->escapeHtml($couponCode)
                            )
                        ]);
                    }
                } else {
                    if ($isCodeLengthValid && $coupon->getId() && $couponCode == $cartQuote->getCouponCode()) {
                        $listCoupon = $this->getDiscounts($couponCode);
                        $discountAmount = (string) $block->getDiscountAmount();
                        $discountAmount = (int) substr($discountAmount, 1);
                        $isFreeShip = $this->checkHaveFreeShipByCouponCode($couponCode);
                        $this->writeLog($isFreeShip);
                        $value = [
                            'original_total' => $block->formatPrice($block->getOriginalTotal()),
                            'savings_amount' => (int)$block->getSavingsAmount() > 0 ? $block->formatPrice($block->getSavingsAmount()) : 0,
                            'discount_amount' => $block->formatPrice($discountAmount),
                            'shipping_amount' => $block->getCustomShippingAmount(),
                            'title_grand_total' => $block->getCustomTitleGrandTotal($block->getShippingAmount()),
                            'grand_total' =>  $block->formatPrice($block->getGrandTotal()),
                            'list_coupon' =>  $listCoupon,
                            'is_freeship' => $isFreeShip
                        ];
                        $response = $this->resultFactory
                        ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
                        ->setData([
                            'status'  => "success",
                            'message' =>   __(
                                'You used coupon code "%1".',
                                $escaper->escapeHtml($couponCode)
                            ),
                            'value'  => $value
                        ]);
                    } else {
                        $response = $this->resultFactory
                        ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
                        ->setData([
                            'status'  => "error",
                            'message' =>   __(
                                'The coupon code "%1" is not valid.',
                                $escaper->escapeHtml($couponCode)
                            )
                        ]);
                    }
                }
            } else {
                $listCoupon = $this->getDiscounts($couponCode);
                $discountAmount = (string) $block->getDiscountAmount();
                $discountAmount = (int) substr($discountAmount, 1);
                $isFreeShip = $this->checkHaveFreeShipByCouponCode($couponCode);
                $this->writeLog($isFreeShip);
                $value = [
                    'original_total' => $block->formatPrice($block->getOriginalTotal()),
                    'savings_amount' => (int)$block->getSavingsAmount() > 0 ? $block->formatPrice($block->getSavingsAmount()) : 0,
                    'discount_amount' => $block->formatPrice($discountAmount),
                    'shipping_amount' => $block->getCustomShippingAmount(),
                    'title_grand_total' => $block->getCustomTitleGrandTotal($block->getShippingAmount()),
                    'grand_total' =>  $block->formatPrice($block->getGrandTotal()),
                    'list_coupon' =>  $listCoupon,
                    'is_freeship' => $isFreeShip
                ];
                $response = $this->resultFactory
                ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
                ->setData([
                    'status'  => "canceled",
                    'message' =>  __('You canceled the coupon code.'),
                    'value'  => $value
                ]);
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $response = $this->resultFactory
            ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
            ->setData([
                'status'  => "error",
                'message' =>  __($e->getMessage())
            ]);
        } catch (\Exception $e) {
            $response = $this->resultFactory
            ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
            ->setData([
                'status'  => "error",
                'message' =>  __('We cannot apply the coupon code.')
            ]);
            $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
        }
        return $response;
    }
    public function getDiscounts($couponCode)
    {
        $discounts = [];

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $coupon = $objectManager->get('\Magento\SalesRule\Model\Coupon');
        $block = $objectManager->get('Chottvn\PriceQuote\Block\Index\Index');
        if (empty($couponCode)) {
            return $discounts;
        }
        $coupon->loadByCode($couponCode);
        $salesruleIds[] = $coupon->getRuleId();

        if (count($salesruleIds) < 1) {
            return $discounts;
        }

        foreach($salesruleIds as $index => $salesruleId) {
            $salesRule = $objectManager->get('\Magento\SalesRule\Model\Rule');
            $rule = $salesRule->load($salesruleId);
            if ($rule->getIsActive()) {
                $discount['name'] = $rule->getName();
                $discount['discount_amount'] = $block->formatPrice($rule->getDiscountAmount());
                $discounts[$index] = $discount;
            }
        }
        return $discounts;
    }
     
     public function checkHaveFreeShipByCouponCode($couponCode)
     {
        try {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $salesRuleCollection = $objectManager->get('\Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory');
            $rules = $salesRuleCollection->create();
            $rules->addFieldToFilter('code', ['eq' => $couponCode]);
            $firstItemRule = $rules->getFirstItem()->getData();
            $productHaveFreeShip = 0;
            $salesruleId = '';

            if(!empty($firstItemRule) && count($firstItemRule)>0){
                $salesruleId = $firstItemRule['rule_id'];
            }

            if($salesruleId) {
                $salesRule = $objectManager->get('\Magento\SalesRule\Model\Rule');
                $rule = $salesRule->load($salesruleId);
                    // $this->writeLog('------------------------------------');
                    $productHaveFreeShip = $rule->getSimpleFreeShipping();
                        if ($rule && $rule->getIsActive()) {
                    // $this->writeLog($rule->getName());
                }
            }
            $this->writeLog('productHaveFreeShip');
            $this->writeLog($productHaveFreeShip);
            if ($productHaveFreeShip == 1) {
                return true;
            }
        } catch (\Exception $e) {
            $this->writeLog($e);
        }
        return false;
     }
     /**
    * @param $info
    * @param $type  [error, warning, info]
    * @return 
    */
    private function writeLog($info, $type = "info"){
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/conpon.log');
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

