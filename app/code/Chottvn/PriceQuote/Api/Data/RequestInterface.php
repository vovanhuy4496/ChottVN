<?php
/**
 * Copyright © (c) chotructuyen.vn All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Chottvn\PriceQuote\Api\Data;

interface RequestInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{

    const EMAIL_SENT_COUNT = 'email_sent_count';
    const CONTACT_NAME = 'contact_name';
    const CONTACT_EMAIL = 'contact_email';
    const SMS_SENT_AT = 'sms_sent_at';
    const COMPANY_ADDRESS = 'company_address';
    const UPDATED_AT = 'updated_at';
    const QUOTE_ID = 'quote_id';
    const CUSTOMER_ID = 'customer_id';
    const ORDER_ID = 'order_id';
    const URL_KEY = 'url_key';
    const SMS_SENT_COUNT = 'sms_sent_count';
    const STATUS = 'status';
    const COMPANY_VAT_NUMBER = 'company_vat_number';
    const ASSIGNEE_ID = 'assignee_id';
    const COMPANY_NAME = 'company_name';
    const FILE_PATH = 'file_path';
    const REQUEST_ID = 'request_id';
    const CREATED_AT = 'created_at';
    const EMAIL_SENT_AT = 'email_sent_at';
    const CONTACT_PHONE_NUMBER = 'contact_phone_number';
    const GRAND_TOTAL = 'grand_total';
    const BASE_GRAND_TOTAL = 'base_grand_total';
    const CHECKOUT_METHOD = 'checkout_method';
    const APPLIED_RULE_IDS = 'applied_rule_ids';
    const COUPON_CODE = 'coupon_code';
    const SUBTOTAL = 'subtotal';
    const BASE_SUBTOTAL = 'base_subtotal';
    const SAVINGS_AMOUNT = 'savings_amount';
    const BASE_SAVINGS_AMOUNT = 'base_savings_amount';
    const ORIGINAL_TOTAL = 'original_total';
    const FLAG_SHIPPING = 'flag_shipping';
    const SHIPPING_AMOUNT = 'shipping_amount';
    const STREET = 'street';
    const CITY = 'city';
    const REGION = 'region';
    const REGION_ID = 'region_id';
    const POSTCODE = 'postcode';
    const COUNTRY_ID = 'country_id';
    const SHIPPING_METHOD = 'shipping_method';
    const CITY_ID = 'city_id';
    const TOWNSHIP = 'township';
    const TOWNSHIP_ID = 'township_id';
    const DISCOUNT_AMOUNT = 'discount_amount';
    const BASE_DISCOUNT_AMOUNT = 'base_discount_amount';
     
         
    /**
     * Get discountamount
     * @return string|null
     */
    public function getDiscountAmount();

    /**
     * Set discountamount
     * @param string $discountamount
     * @return \Chottvn\PriceQuote\Api\Data\RequestInterface
     */
    public function setDiscountAmount($discountamount);

         
    /**
     * Get base_discount_amount
     * @return string|null
     */
    public function getBaseDiscountAmount();

    /**
     * Set base_discount_amount
     * @param string $base_discount_amount
     * @return \Chottvn\PriceQuote\Api\Data\RequestInterface
     */
    public function setBaseDiscountAmount($base_discount_amount);

    /**
     * Get request_id
     * @return string|null
     */
    public function getRequestId();

    /**
     * Set request_id
     * @param string $requestId
     * @return \Chottvn\PriceQuote\Api\Data\RequestInterface
     */
    public function setRequestId($requestId);

    /**
     * Get customer_id
     * @return string|null
     */
    public function getCustomerId();

    /**
     * Set customer_id
     * @param string $customerId
     * @return \Chottvn\PriceQuote\Api\Data\RequestInterface
     */
    public function setCustomerId($customerId);

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Chottvn\PriceQuote\Api\Data\RequestExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     * @param \Chottvn\PriceQuote\Api\Data\RequestExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Chottvn\PriceQuote\Api\Data\RequestExtensionInterface $extensionAttributes
    );

    /**
     * Get company_name
     * @return string|null
     */
    public function getCompanyName();

    /**
     * Set company_name
     * @param string $companyName
     * @return \Chottvn\PriceQuote\Api\Data\RequestInterface
     */
    public function setCompanyName($companyName);

    /**
     * Get company_address
     * @return string|null
     */
    public function getCompanyAddress();

    /**
     * Set company_address
     * @param string $companyAddress
     * @return \Chottvn\PriceQuote\Api\Data\RequestInterface
     */
    public function setCompanyAddress($companyAddress);

    /**
     * Get company_vat_number
     * @return string|null
     */
    public function getCompanyVatNumber();

    /**
     * Set company_vat_number
     * @param string $companyVatNumber
     * @return \Chottvn\PriceQuote\Api\Data\RequestInterface
     */
    public function setCompanyVatNumber($companyVatNumber);

    /**
     * Get contact_name
     * @return string|null
     */
    public function getContactName();

    /**
     * Set contact_name
     * @param string $contactName
     * @return \Chottvn\PriceQuote\Api\Data\RequestInterface
     */
    public function setContactName($contactName);

    /**
     * Get contact_phone_number
     * @return string|null
     */
    public function getContactPhoneNumber();

    /**
     * Set contact_phone_number
     * @param string $contactPhoneNumber
     * @return \Chottvn\PriceQuote\Api\Data\RequestInterface
     */
    public function setContactPhoneNumber($contactPhoneNumber);

    /**
     * Get contact_email
     * @return string|null
     */
    public function getContactEmail();

    /**
     * Set contact_email
     * @param string $contactEmail
     * @return \Chottvn\PriceQuote\Api\Data\RequestInterface
     */
    public function setContactEmail($contactEmail);

    /**
     * Get status
     * @return string|null
     */
    public function getStatus();

    /**
     * Set status
     * @param string $status
     * @return \Chottvn\PriceQuote\Api\Data\RequestInterface
     */
    public function setStatus($status);

    /**
     * Get assignee_id
     * @return string|null
     */
    public function getAssigneeId();

    /**
     * Set assignee_id
     * @param string $assigneeId
     * @return \Chottvn\PriceQuote\Api\Data\RequestInterface
     */
    public function setAssigneeId($assigneeId);

    /**
     * Get email_sent_at
     * @return string|null
     */
    public function getEmailSentAt();

    /**
     * Set email_sent_at
     * @param string $emailSentAt
     * @return \Chottvn\PriceQuote\Api\Data\RequestInterface
     */
    public function setEmailSentAt($emailSentAt);

    /**
     * Get email_sent_count
     * @return string|null
     */
    public function getEmailSentCount();

    /**
     * Set email_sent_count
     * @param string $emailSentCount
     * @return \Chottvn\PriceQuote\Api\Data\RequestInterface
     */
    public function setEmailSentCount($emailSentCount);

    /**
     * Get sms_sent_at
     * @return string|null
     */
    public function getSmsSentAt();

    /**
     * Set sms_sent_at
     * @param string $smsSentAt
     * @return \Chottvn\PriceQuote\Api\Data\RequestInterface
     */
    public function setSmsSentAt($smsSentAt);

    /**
     * Get sms_sent_count
     * @return string|null
     */
    public function getSmsSentCount();

    /**
     * Set sms_sent_count
     * @param string $smsSentCount
     * @return \Chottvn\PriceQuote\Api\Data\RequestInterface
     */
    public function setSmsSentCount($smsSentCount);

    /**
     * Get created_at
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Set created_at
     * @param string $createdAt
     * @return \Chottvn\PriceQuote\Api\Data\RequestInterface
     */
    public function setCreatedAt($createdAt);

    /**
     * Get updated_at
     * @return string|null
     */
    public function getUpdatedAt();

    /**
     * Set updated_at
     * @param string $updatedAt
     * @return \Chottvn\PriceQuote\Api\Data\RequestInterface
     */
    public function setUpdatedAt($updatedAt);

    /**
     * Get quote_id
     * @return string|null
     */
    public function getQuoteId();

    /**
     * Set quote_id
     * @param string $quoteId
     * @return \Chottvn\PriceQuote\Api\Data\RequestInterface
     */
    public function setQuoteId($quoteId);

    /**
     * Get order_id
     * @return string|null
     */
    public function getOrderId();

    /**
     * Set order_id
     * @param string $orderId
     * @return \Chottvn\PriceQuote\Api\Data\RequestInterface
     */
    public function setOrderId($orderId);

    /**
     * Get url_key
     * @return string|null
     */
    public function getUrlKey();

    /**
     * Set url_key
     * @param string $urlKey
     * @return \Chottvn\PriceQuote\Api\Data\RequestInterface
     */
    public function setUrlKey($urlKey);

    /**
     * Get file_path
     * @return string|null
     */
    public function getFilePath();

    /**
     * Set file_path
     * @param string $filePath
     * @return \Chottvn\PriceQuote\Api\Data\RequestInterface
     */
    public function setFilePath($filePath);

     /**
     * Get grandtotal
     * @return string|null
     */
    public function getGrandTotal();

    /**
     * Set grandtotal
     * @param string $grandtotal
     * @return \Chottvn\PriceQuote\Api\Data\RequestInterface
     */
    public function setGrandTotal($grandtotal);

    /**
     * Get basegrandtotal
     * @return string|null
     */
    public function getBaseGrandTotal();

    /**
     * Set basegrandtotal
     * @param string $basegrandtotal
     * @return \Chottvn\PriceQuote\Api\Data\RequestInterface
     */
    public function setBaseGrandTotal($basegrandtotal);

    /**
     * Get checkoutmethod
     * @return string|null
     */
    public function getCheckoutMethod();

    /**
     * Set checkoutmethod
     * @param string $checkoutmethod
     * @return \Chottvn\PriceQuote\Api\Data\RequestInterface
     */
    public function setCheckoutMethod($checkoutmethod);

    /**
     * Get appliedruleids
     * @return string|null
     */
    public function getAppliedRuleIds();

    /**
     * Set appliedruleids
     * @param string $appliedruleids
     * @return \Chottvn\PriceQuote\Api\Data\RequestInterface
     */
    public function setAppliedRuleIds($appliedruleids);

    /**
     * Get couponcode
     * @return string|null
     */
    public function getCouponCode();

    /**
     * Set couponcode
     * @param string $couponcode
     * @return \Chottvn\PriceQuote\Api\Data\RequestInterface
     */
    public function setCouponCode($couponcode);

     /**
     * Get subtotal
     * @return string|null
     */
    public function getSubtotal();

    /**
     * Set subtotal
     * @param string $subtotal
     * @return \Chottvn\PriceQuote\Api\Data\RequestInterface
     */
    public function setSubtotal($subtotal);

    /**
     * Get basesubtotal
     * @return string|null
     */
    public function getBaseSubtotal();

    /**
     * Set basesubtotal
     * @param string $basesubtotal
     * @return \Chottvn\PriceQuote\Api\Data\RequestInterface
     */
    public function setBaseSubtotal($basesubtotal);

    /**
     * Get savingsamount
     * @return string|null
     */
    public function getSavingsAmount();

    /**
     * Set savingsamount
     * @param string $savingsamount
     * @return \Chottvn\PriceQuote\Api\Data\RequestInterface
     */
    public function setSavingsAmount($savingsamount);

     /**
     * Get basesavingsamount
     * @return string|null
     */
    public function getBaseSavingsAmount();

    /**
     * Set basesavingsamount
     * @param string $basesavingsamount
     * @return \Chottvn\PriceQuote\Api\Data\RequestInterface
     */
    public function setBaseSavingsAmount($basesavingsamount);

    /**
     * Get originaltotal
     * @return string|null
     */
    public function getOriginalTotal();

    /**
     * Set originaltotal
     * @param string $originaltotal
     * @return \Chottvn\PriceQuote\Api\Data\RequestInterface
     */
    public function setOriginalTotal($originaltotal);

    /**
     * Get flagshipping
     * @return string|null
     */
    public function getFlagShipping();

    /**
     * Set flagshipping
     * @param string $flagshipping
     * @return \Chottvn\PriceQuote\Api\Data\RequestInterface
     */
    public function setFlagShipping($flagshipping);
    /**
     * Get shippingamount
     * @return string|null
     */
    public function getShippingAmount();

    /**
     * Set shippingamount
     * @param string $shippingamount
     * @return \Chottvn\PriceQuote\Api\Data\RequestInterface
     */
    public function setShippingAmount($shippingamount);

    /**
     * Get street
     * @return string|null
     */
    public function getStreet();

    /**
     * Set street
     * @param string $street
     * @return \Chottvn\PriceQuote\Api\Data\RequestInterface
     */
    public function setStreet($street);

     /**
     * Get city
     * @return string|null
     */
    public function getCity();

    /**
     * Set city
     * @param string $city
     * @return \Chottvn\PriceQuote\Api\Data\RequestInterface
     */
    public function setCity($city);

     /**
     * Get region
     * @return string|null
     */
    public function getRegion();

    /**
     * Set region
     * @param string $region
     * @return \Chottvn\PriceQuote\Api\Data\RequestInterface
     */
    public function setRegion($region);

    /**
     * Get regionid
     * @return string|null
     */
    public function getRegionId();

    /**
     * Set regionid
     * @param string $regionid
     * @return \Chottvn\PriceQuote\Api\Data\RequestInterface
     */
    public function setRegionId($regionid);

    /**
     * Get postcode
     * @return string|null
     */
    public function getPostcode();

    /**
     * Set postcode
     * @param string $postcode
     * @return \Chottvn\PriceQuote\Api\Data\RequestInterface
     */
    public function setPostcode($postcode);
    
    /**
     * Get countryid
     * @return string|null
     */
    public function getCountryId();

    /**
     * Set countryid
     * @param string $countryid
     * @return \Chottvn\PriceQuote\Api\Data\RequestInterface
     */
    public function setCountryId($countryid);
        
    /**
     * Get shippingmethod
     * @return string|null
     */
    public function getShippingMethod();

    /**
     * Set shippingmethod
     * @param string $shippingmethod
     * @return \Chottvn\PriceQuote\Api\Data\RequestInterface
     */
    public function setShippingMethod($shippingmethod);
     /**
     * Get cityid
     * @return string|null
     */
    public function getCityId();

    /**
     * Set cityid
     * @param string $cityid
     * @return \Chottvn\PriceQuote\Api\Data\RequestInterface
     */
    public function setCityId($cityid);

     /**
     * Get township
     * @return string|null
     */
    public function getTownship();

    /**
     * Set township
     * @param string $township
     * @return \Chottvn\PriceQuote\Api\Data\RequestInterface
     */
    public function setTownship($township);


     /**
     * Get townshipid
     * @return string|null
     */
    public function getTownshipId();

    /**
     * Set townshipid
     * @param string $townshipid
     * @return \Chottvn\PriceQuote\Api\Data\RequestInterface
     */
    public function setTownshipId($townshipid);


}

