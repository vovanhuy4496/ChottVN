<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Chottvn\Sales\Rewrite\Magento\Sales\Model\Order\Email\Sender;

use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Container\OrderIdentity;
use Magento\Sales\Model\Order\Email\Container\Template;
use Magento\Sales\Model\Order\Email\Sender;
use Magento\Sales\Model\ResourceModel\Order as OrderResource;
use Magento\Sales\Model\Order\Address\Renderer;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\DataObject;

/**
 * Class OrderSender
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class OrderSender extends \Magento\Sales\Model\Order\Email\Sender\OrderSender
{
    public $noteShipping;

    /**
     * @var PaymentHelper
     */
    protected $paymentHelper;

    /**
     * @var OrderResource
     */
    protected $orderResource;

    /**
     * Global configuration storage.
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $globalConfig;

    /**
     * @var Renderer
     */
    protected $addressRenderer;

    /**
     * Application Event Dispatcher
     *
     * @var ManagerInterface
     */
    protected $eventManager;

    /**
     * @param Template $templateContainer
     * @param OrderIdentity $identityContainer
     * @param Order\Email\SenderBuilderFactory $senderBuilderFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param Renderer $addressRenderer
     * @param PaymentHelper $paymentHelper
     * @param OrderResource $orderResource
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $globalConfig
     * @param ManagerInterface $eventManager
     */
    public function __construct(
        Template $templateContainer,
        OrderIdentity $identityContainer,
        \Magento\Sales\Model\Order\Email\SenderBuilderFactory $senderBuilderFactory,
        \Psr\Log\LoggerInterface $logger,
        Renderer $addressRenderer,
        PaymentHelper $paymentHelper,
        OrderResource $orderResource,
        \Magento\Framework\App\Config\ScopeConfigInterface $globalConfig,
        ManagerInterface $eventManager
    ) {
        parent::__construct($templateContainer, $identityContainer, $senderBuilderFactory, $logger, $addressRenderer,
            $paymentHelper,
            $orderResource,
            $globalConfig,
            $eventManager
        );
        // $this->paymentHelper = $paymentHelper;
        // $this->orderResource = $orderResource;
        // $this->globalConfig = $globalConfig;
        // $this->addressRenderer = $addressRenderer;
        // $this->eventManager = $eventManager;
    }

    /**
     * Sends order email to the customer.
     *
     * Email will be sent immediately in two cases:
     *
     * - if asynchronous email sending is disabled in global settings
     * - if $forceSyncMode parameter is set to TRUE
     *
     * Otherwise, email will be sent later during running of
     * corresponding cron job.
     *
     * @param Order $order
     * @param bool $forceSyncMode
     * @return bool
     */
    public function send(Order $order, $forceSyncMode = false)
    {
        $order->setSendEmail($this->identityContainer->isEnabled());

        if (!$this->globalConfig->getValue('sales_email/general/async_sending') || $forceSyncMode) {
            if ($this->checkAndSend($order)) {
                $order->setEmailSent(true);
                $this->orderResource->saveAttribute($order, ['send_email', 'email_sent']);
                return true;
            }
        } else {
            $order->setEmailSent(null);
            $this->orderResource->saveAttribute($order, 'email_sent');
        }

        $this->orderResource->saveAttribute($order, 'send_email');

        return false;
    }

    /**
     * Prepare email template with variables
     *
     * @param Order $order
     * @return void
     */
    protected function prepareTemplate(Order $order)
    {
        parent::prepareTemplate($order);
        // Add isquotation variable
        $templateVars = $this->templateContainer->getTemplateVars();
        
        $templateVars['billingInvoiceInfo'] = $this->billingInvoiceInfo($order);
        $templateVars['formatAddress'] = $this->formatAddress($order);
        $templateVars['totalSummary'] = $this->totalSummary($order);

        $templateVars['headerOrderConfirmation'] = $this->headerOrderConfirmation($order);
        $templateVars['footerOrderConfirmation'] = $this->footerOrderConfirmation($order);
        
        $this->templateContainer->setTemplateVars($templateVars);  
    }

    /**
     * Get payment info block as html
     *
     * @param Order $order
     * @return string
     */
    protected function getPaymentHtml(Order $order)
    {
        return $this->paymentHelper->getInfoBlockHtml(
            $order->getPayment(),
            $this->identityContainer->getStore()->getStoreId()
        );
    }

    public function formatAddress(Order $order)
    {
        $billingInvoiceInfo = $this->billingInvoiceInfo($order);

        $billingAddress = $order->getBillingAddress();
        $shippingAddress = $order->getShippingAddress();

        $name_billing = $billingAddress->getFirstName();
        $tele_billing = $billingAddress->getTelephone();
        $email_billing = $billingAddress->getEmail();
        $html = '<div class="order-totals">';
            $html .= '<p class="order-total-title">'.__('Customer Information').'</p>';
            $html .= '<div class="card">';
                $html .= '<div class="card-body">';
                    if($name_billing && $tele_billing && 
                        (($name_billing != $shippingAddress->getFirstName()) || 
                        ($tele_billing != $shippingAddress->getTelephone()))) {
                            $html .= '<p class="title-infor-cus">'.__('Billing Information').':'.'</p>';
                            $html .= '<p>'.$name_billing.' - '.$tele_billing.'</p>';
                            if ($email_billing) {
                                $html .= '<p><span>'.__('Email').': '.'</span>'.$email_billing.'</p>';
                            }
                            $html .= '<p class="title-infor-cus">'.__('Shipping Information').':'.'</p>';
                            $html .= '<p>'.$shippingAddress->getFirstName().' - '.$shippingAddress->getTelephone().'</p>';
                    } else {
                        $html .= '<p>'.$shippingAddress->getFirstName().'</p>';
                        $html .= '<p>'.__('Phone number').': '.'<span>'.$shippingAddress->getTelephone().'</span>'.'</p>';
                        if ($email_billing) {
                            $html .= '<p><span>'.__('Email').': '.'</span>'.$email_billing.'</p>';
                        }
                    }
                    $html .= '<p>'.__('Address').':'.' ';
                        $html .= '<span>';
                            $html .= $shippingAddress->getStreet()[0];
                            $html .= $shippingAddress->getTownship() ? ", ".$shippingAddress->getTownship() : "";
                            $html .= $shippingAddress->getCity() ? ", ".$shippingAddress->getCity() : "";
                            $html .= $shippingAddress->getRegion() ? ", ".$shippingAddress->getRegion() : "";
                        $html .= '</span>';
                    $html .= '</p>';
                    // $this->writeLog($this->noteShipping);
                    if (isset($this->noteShipping['note_shipping']) && $this->noteShipping['note_shipping']) {
                        $html .= '<p><span>'.__('Notes').': '.'</span>'.$this->noteShipping['note_shipping'].'</p>';
                    }
                $html .= '</div>';
            $html .= '</div>';
        $html .= '</div>';

        // $this->writeLog($html);
        return $html;
    }

    public function billingInvoiceInfo(Order $order)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        
        $saleHelper = $objectManager->get('Chottvn\Sales\Helper\Data');
        $orderAttributeList = $saleHelper->getOrderAttributesData($order->getId());
        $arr_vat_invoice_required = [];
        $arr_note_shipping = [];

        foreach ($orderAttributeList as $key => $value) {
            if ($key == 'vat_company'  || $key == 'vat_address' || $key == 'vat_number' || $key == 'vat_contact_name' || $key == 'vat_contact_phone_number' || $key == 'vat_contact_email') {
                $arr_vat_invoice_required += [$key => $value];
            }
            if ($key == 'note_shipping') {
                $arr_note_shipping += [$key => $value];
                $this->noteShipping = $arr_note_shipping;

                // $this->writeLog($arr_note_shipping);
                // $this->writeLog($this->noteShipping);
            }
        }
        $payment = $order->getPayment();
        $paymentMethod = $payment->getMethodInstance();
        $paymentMethodCode = $paymentMethod->getCode();
        $paymentMethodTitle = $paymentMethod->getTitle();
        $orderResource = $objectManager->create('\Magento\Sales\Model\OrderRepository')->get($order->getId());
        $bankTransferNote = $orderResource->getBankTransferNote();

        $html = '<div class="order-totals order-totals-billing-invoice">';
            $html .= '<p class="order-total-title">'.__('Payment & Invoice Information').'</p>';
            $html .= '<div class="card">';
                $html .= '<div class="card-body">';
                    $html .= '<p>'.$paymentMethodTitle.' '.$bankTransferNote.'</p>';
                    if (sizeof($arr_vat_invoice_required) > 0) {
                        $html .= '<p class="order-total-title-request">'.__("Invoice Information").'</p>';
                        if (isset($arr_vat_invoice_required['vat_company'])) {
                            $html .= '<p>'.$arr_vat_invoice_required['vat_company'].'</p>';
                        }
                        if (isset($arr_vat_invoice_required['vat_address'])) {
                            $html .= '<p>'.'<span>'. __('Address').': '.'</span>'.$arr_vat_invoice_required['vat_address'].'</p>';
                        }
                        if (isset($arr_vat_invoice_required['vat_number'])) {
                            $html .= '<p>'.'<span>'. __('MST').': '.'</span>'.$arr_vat_invoice_required['vat_number'].'</p>';
                        }
                        if (isset($arr_vat_invoice_required['vat_contact_name'])) {
                            $html .= '<p>';
                                $html .= '<span>'.$arr_vat_invoice_required['vat_contact_name'].'</span>';
                                if (isset($arr_vat_invoice_required['vat_contact_phone_number']) && $arr_vat_invoice_required['vat_contact_phone_number']) {
                                    $html .= ' - '.$arr_vat_invoice_required['vat_contact_phone_number'];
                                }
                            $html .= '</p>';
                        }
                        if (isset($arr_vat_invoice_required['vat_contact_email'])) {
                            $html .= '<p>'.'<span>'. __('Email').': '.'</span>'.$arr_vat_invoice_required['vat_contact_email'].'</p>';
                        }
                    } else {
                        $billingAddress = $order->getBillingAddress();
                        $shippingAddress = $order->getShippingAddress();

                        $html .= '<p class="order-total-title-request">'.__("Invoice Information").'</p>';
                        $html .= '<p>'.$billingAddress->getFirstName().'</p>';
                        $html .= '<p>'.__('Phone number').': '.'<span>'.$shippingAddress->getTelephone().'</span>'.'</p>';
                        $html .= '<p><span>'.__('Address').':'.' '.'</span>';
                            $html .= '<span>';
                                $html .= $shippingAddress->getStreet()[0];
                                $html .= $shippingAddress->getTownship() ? ", ".$shippingAddress->getTownship() : "";
                                $html .= $shippingAddress->getCity() ? ", ".$shippingAddress->getCity() : "";
                                $html .= $shippingAddress->getRegion() ? ", ".$shippingAddress->getRegion() : "";
                            $html .= '</span>';
                        $html .= '</p>';
                        if ($billingAddress->getEmail()) {
                            $html .= '<p>'.__('Email').':'.' '.'<span>'.$billingAddress->getEmail().'</span>'.'</p>';
                        }
                    }
                $html .= '</div>';
            $html .= '</div>';
        $html .= '</div>';

        // $this->writeLog($html);
        return $html;
    }

    public function totalSummary(Order $order)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        $checkoutHelper = $objectManager->get('Chottvn\PriceDecimal\Helper\Data');
        $shipping_amount = $this->emailFormatPrice($order->getShippingAmount());
        
        $grandTotal = __('Grand Total');
        $quote = $objectManager->create('Magento\Quote\Model\Quote')->load($order->getQuoteId());
        $flagShipping = $quote->getFlagShipping();
        if ($flagShipping === 'freeshipping') {
            $shipping_amount = __('Free Shipping');
        }
        if ($flagShipping === 'over') {
            $grandTotal = __('Grand Total(temp)');
            $shipping_amount = __('Price Contact');
        }
        $discountAmount = $order->getDiscountAmount();

        // $customerSession = $objectManager->create('Magento\Customer\Model\Session');
        // $grCustomer = $customerSession->getCustomerGroupId();
        $couponcode = $order->getCouponCode();
        $codeAffiliate = $order->getAffiliateAccountCode();
        // id CTV
        $idAffiliate = $order->getAffiliateAccountId() ? $order->getAffiliateAccountId(): -1;
        // customerId
        $customerId = $order->getCustomerId() ? $order->getCustomerId(): -2;
        // $this->writeLog($codeAffiliate);
        // $billingAddress = $order->getBillingAddress();
        // $tele_billing = $billingAddress->getTelephone();
        // $customerObj = $objectManager->create('Magento\Customer\Model\Customer')->load($order->getAffiliateAccountId());
        // $phone_number_aff = $customerObj->getData('phone_number');
        
        $html = '<div class="order-totals">';
            $html .= '<p class="order-total-title">'.__('Order summary').'</p>';
            $html .= '<div class="card">';
                $html .= '<div class="card-body card-body-total-summary">';
                    $html .= '<p>'.__('Original Total').': '.'<span class="float-right">'.$this->emailFormatPrice($order->getOriginalTotal()).'</span></p>';
                    if ((int)$order->getSavingsAmount() > 0) {
                        $html .= '<p>'.__('Saving amount').': '.'<span class="float-right">'.$this->emailFormatPrice($order->getSavingsAmount()).'</span></p>';
                    }
                    if ($discountAmount < 0) {
                        if (is_numeric($discountAmount)) {
                            $discountAmount = $this->emailFormatPrice(ltrim(strval($discountAmount), '-'));
                        }
                        $html .= '<p>'.__('Discount Amount').': '.'<span class="float-right">'.$discountAmount.'</span></p>';
                    }
                    $html .= '<p>'.__('Shipping & Handling').': '.'<span class="float-right">'.$shipping_amount.'</span></p>';
                $html .= '</div>';
                $html .= '<div class="card-footer">';
                    $html .= '<p class="clearfix">';
                        $html .= '<span class="grand-total">'.$grandTotal.': '.'</span>';
                        $html .= '<span class="float-right text-right display-grid">'.$this->emailFormatPrice($order->getGrandTotal()).'<span class="include-vat">'.__('Include VAT').'</span>'.'</span>';
                    $html .= '</p>';
                    if ($couponcode) {
                        $html .= '<p class="remove-border-top">'.__('Discount code').': '.'<span class="float-right text-right">'.$couponcode.'</span></p>';
                    }
                    if ($codeAffiliate) {
                        $html .= '<p class="remove-border-top">';
                        if ($idAffiliate == $customerId) {
                            $html .= __('Affiliate Code To Order');
                        } else {
                            $html .= __('Affiliate Code Order');
                        }
                        $html .= ':';
                            $html .= '<span class="float-right text-right">'.$codeAffiliate.'</span>';
                        $html .= '</p>';
                    }
                $html .= '</div>';
            $html .= '</div>';
        $html .= '</div>';

        // $this->writeLog($html);

        return $html;
    }

    public function orderSummary(Order $order)
    {
        $createdAt = $order->getCreatedAt();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $timezoneInterface = $objectManager->get('Magento\Framework\Stdlib\DateTime\TimezoneInterface');
        $dateTimeZone = $timezoneInterface->date(new \DateTime($createdAt))->format('d/m/Y');
        $checkoutHelper = $objectManager->get('Chottvn\PriceDecimal\Helper\Data');
        $html = '<div class="col-sm-12 order-summary">';
            $html .= '<div class="row order-detail order-detail-sender">';
                $html .= '<div class="order-summary-left product-item-detail-mobile">';
                    $html .= '<span class="order-label">'.__('Order').': <span class="order-value">#'.$order->getRealOrderId().'</span></span><br />';
                    $html .= '<span class="date-label">'.__('Order date:').'<span class="date-value">'.$dateTimeZone.'</span></span>';
                $html .= '</div>';
                $html .= '<div class="order-summary-right product-item-detail-mobile">';
                    $html .= '<span class="amount-value">'.$this->emailFormatPrice($order->getGrandTotal()).'</span><br />';
                    $html .= '<span class="items-value">';
                        $html .= (string)(int)$order->getTotalQtyOrdered();
                        $html .= ' ';
                        $html .= ((int)$order->getTotalQtyOrdered() > 1) ? __('products') : __('product');
                    $html .= '</span>';
                $html .= '</div>';
            $html .= '</div>';
        $html .= '</div>';
        return $html;
    }

    public function headerOrderConfirmation(Order $order)
    {
        $getCustomerFirstname = $order->getCustomerFirstname();

        $actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
        // $this->writeLog($actual_link);

        $html = '<div class="header-email"><p>'.__('Hello').' '.$getCustomerFirstname.'.</p>';
        $html .= '<p>'.__('Thank you for ordering now').' '.'<a href="'.$actual_link.'">'.__('chotructuyen.co').'</a>'.'.</p>';
        $html .= '<p>';
            $html .= __('Your order has been successfully recorded and is being processed. We will notify you as soon as the package is ready to be shipped. Please check order information and shipping information carefully.');
        $html .= '</p></div>';

        return $html;
    }

    public function footerOrderConfirmation(Order $order)
    {
        $html = '<div class="note-checkout-success"><p>'.
                    __("Any questions about the order. Please contact customer care department via Hotline <span class='tele'> 0899 00 20 20 </span> (8: 00-20: 00 every day including Saturday 7, Sunday), email: <span class='email'> lienhe@chotructuyen.co </span>").
                '</p>';
        $html .= '<p class="margin-bottom-0">'.__('Thank you very much.').'</p>';
        $html .= '<p>Cty CP Chợ Trực Tuyến.</p></div>';

        return $html;
    }

    public function headerSuccessfulDelivery(Order $order)
    {
        $getCustomerFirstname = $order->getCustomerFirstname();

        $actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";

        $html = '<div class="header-email"><p>'.__('Hello').' '.$getCustomerFirstname.'.</p>';
        $html .= '<p>'.__('Thank you for ordering now').' '.'<a href="'.$actual_link.'">'.__('chotructuyen.co').'</a>'.'.</p>';
        $html .= '<p>';
            $html .= __("Your order #%1 has been updated in the status of Delivery Success.", $order->getRealOrderId());
            $html .= ' ';
            $html .= __("You can check the status of your order by going <a href='%1'>Login</a> into your account or <a href='%2'>Track your order</a> here.", $actual_link.'/customer/account/login', $actual_link.'/sales/guest/form');
        $html .= '</p></div>';

        // $this->writeLog($html);

        return $html;
    }

    public function footerSuccessfulDelivery(Order $order)
    {
        $actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";

        $html = '<div class="note-checkout-success"><p>'.
                    __("Any questions about the order. Please contact customer care department via Hotline <span class='tele'> 0899 00 20 20 </span> (8: 00-20: 00 every day including Saturday 7, Sunday), email: <span class='email'> lienhe@chotructuyen.co </span>").
                '</p>';
        $html .= '<p>'.__("Thank you again for your trust and purchase at <a href='%1'>chotructuyen.co!</a> We are very pleased to continue to serve you in the future.", $actual_link).'</p>';
        $html .= '<p>Cty CP Chợ Trực Tuyến.</p></div>';

        // $this->writeLog($html);

        return $html;
    }

    public function headerShipping(Order $order)
    {
        $getCustomerFirstname = $order->getCustomerFirstname();
        $actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";

        $html = '<div class="header-email"><p>'.__('Hello').' '.$getCustomerFirstname.'.</p>';
        $html .= '<p>';
            $html .= __("Your order #%1 has been packaged and delivered to a carrier.", $order->getRealOrderId());
            $html .= ' ';
            $html .= __("You can check the status of your order by going <a href='%1'>Login</a> into your account or <a href='%2'>Track your order</a> here.", $actual_link.'/customer/account/login', $actual_link.'/sales/guest/form');
        $html .= '</p></div>';

        return $html;
    }

    public function footerShipping(Order $order)
    {
        $actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";

        $html = '<div class="note-checkout-success"><p class="note-shipping">'.__("Note:").'</p>';
        $html .= '<p>'.__('During the delivery day, CTy CP CTT or shipping staff will contact you directly before delivery.').'</p>';
        $html .= '<p>'.__('When receiving goods, please carefully check order information and package status (Order number, product name, quantity, brand, seal box ...) before receiving goods.').'</p>';
        $html .= '<p>'.__('In case the package shows signs of being disassembled, the goods are distorted/broken or do not match the information on the order and website ... You should contact CTy Online Market Joint Stock within 24 hours after receipt. for processing assistance. Please do not try the product or activate electrical appliances.').'</p>';
        $html .= '<p>'.__("You need to keep the invoice, product box and warranty card (if any) to facilitate return or warranty. Details of the delivery method and return policy can be found at the <a href='%1'> Delivery policy </a> and <a href='%2'> Policy exchange, return and warranty </a> page or contact Hotline <span class='tele'> 0899 00 20 20 </span> (8: 00-20: 00 every day including Saturday 7, Sunday), email: <span class='email'> lienhe@chotructuyen.co </span>", $actual_link.'/chinh-sach-giao-hang.html', $actual_link.'/chinh-sach-doi-tra-bao-hanh.html').'</p>';
        $html .= '<p class="margin-bottom-0">'.__('Thank you very much.').'</p>';
        $html .= '<p>Cty CP Chợ Trực Tuyến.</p></div>';

        return $html;
    }

    public function headerCanceled(Order $order)
    {
        $getCustomerFirstname = $order->getCustomerFirstname();

        $actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";

        $html = '<div class="header-email"><p>'.__('Hello').' '.$getCustomerFirstname.'.</p>';
        $html .= '<p>'.__('Thank you for ordering now').' '.'<a href="'.$actual_link.'">'.__('chotructuyen.co').'</a>'.'.</p>';
        $html .= '<p>';
            $html .= __("Your order #%1 has been updated with <span class='cancel'> canceled </span>.", $order->getRealOrderId());
            $html .= ' ';
            $html .= __("You can check the status of your order by going <a href='%1'>Login</a> into your account or <a href='%2'>Track your order</a> here.", $actual_link.'/customer/account/login', $actual_link.'/sales/guest/form');
        $html .= '</p></div>';

        return $html;
    }

    public function footerCanceled(Order $order)
    {
        $html = '<div class="note-checkout-success">';
        $html .= '<p>'.__("Any questions about the order. Please contact customer care department via Hotline <span class='tele'> 0899 00 20 20 </span> (8: 00-20: 00 every day including Saturday 7, Sunday), email: <span class='email'> lienhe@chotructuyen.co </span>").'</p>';
        $html .= '<p class="margin-bottom-0">'.__('Thank you very much.').'</p>';
        $html .= '<p>Cty CP Chợ Trực Tuyến.</p></div>';

        return $html;
    }

    public function emailFormatPrice($price)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $checkoutHelper = $objectManager->get('Chottvn\PriceDecimal\Helper\Data');

        $price = str_replace("₫", "", $checkoutHelper->formatPrice($price));
        $price = str_replace("đ", "", $price);
        $price = str_replace(",", ".", $price);
        $price = str_replace("</span>", "đ</span>", $price);

        return $price;
    }

    /**
	* @param $info
	* @param $type  [error, warning, info]
	* @return 
	*/
	private function writeLog($info, $type = "info") {
		$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/format_email.log');
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
