<?php
/**
 * Copyright © (c) chotructuyen.vn All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Chottvn\PriceQuote\Model\Data;

use Chottvn\PriceQuote\Api\Data\RequestInterface;

class Request extends \Magento\Framework\Api\AbstractExtensibleObject implements RequestInterface
{

     /**
     * Get discountamount
     * @return string|null
     */
    public function getDiscountAmount(){
        return $this->_get(self::DISCOUNT_AMOUNT);
    }

    /**
     * Set discountamount
     * @param string $discountamount
     * @return \Chottvn\PriceQuote\Api\Data\RequestInterface
     */
    public function setDiscountAmount($discountamount){
        return $this->setData(self::DISCOUNT_AMOUNT, $discountamount);
    }

         
    /**
     * Get base_discount_amount
     * @return string|null
     */
    public function getBaseDiscountAmount(){
        return $this->_get(self::BASE_DISCOUNT_AMOUNT);
    }

    /**
     * Set base_discount_amount
     * @param string $base_discount_amount
     * @return \Chottvn\PriceQuote\Api\Data\RequestInterface
     */
    public function setBaseDiscountAmount($base_discount_amount){
        return $this->setData(self::BASE_DISCOUNT_AMOUNT, $base_discount_amount);
    }

    /**
     * Get request_id
     * @return string|null
     */
    public function getRequestId()
    {
        return $this->_get(self::REQUEST_ID);
    }

    /**
     * Set request_id
     * @param string $requestId
     * @return \Chottvn\PriceQuote\Api\Data\RequestInterface
     */
    public function setRequestId($requestId)
    {
        return $this->setData(self::REQUEST_ID, $requestId);
    }

    /**
     * Get customer_id
     * @return string|null
     */
    public function getCustomerId()
    {
        return $this->_get(self::CUSTOMER_ID);
    }

    /**
     * Set customer_id
     * @param string $customerId
     * @return \Chottvn\PriceQuote\Api\Data\RequestInterface
     */
    public function setCustomerId($customerId)
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Chottvn\PriceQuote\Api\Data\RequestExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * Set an extension attributes object.
     * @param \Chottvn\PriceQuote\Api\Data\RequestExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Chottvn\PriceQuote\Api\Data\RequestExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }

    /**
     * Get company_name
     * @return string|null
     */
    public function getCompanyName()
    {
        return $this->_get(self::COMPANY_NAME);
    }

    /**
     * Set company_name
     * @param string $companyName
     * @return \Chottvn\PriceQuote\Api\Data\RequestInterface
     */
    public function setCompanyName($companyName)
    {
        return $this->setData(self::COMPANY_NAME, $companyName);
    }

    /**
     * Get company_address
     * @return string|null
     */
    public function getCompanyAddress()
    {
        return $this->_get(self::COMPANY_ADDRESS);
    }

    /**
     * Set company_address
     * @param string $companyAddress
     * @return \Chottvn\PriceQuote\Api\Data\RequestInterface
     */
    public function setCompanyAddress($companyAddress)
    {
        return $this->setData(self::COMPANY_ADDRESS, $companyAddress);
    }

    /**
     * Get company_vat_number
     * @return string|null
     */
    public function getCompanyVatNumber()
    {
        return $this->_get(self::COMPANY_VAT_NUMBER);
    }

    /**
     * Set company_vat_number
     * @param string $companyVatNumber
     * @return \Chottvn\PriceQuote\Api\Data\RequestInterface
     */
    public function setCompanyVatNumber($companyVatNumber)
    {
        return $this->setData(self::COMPANY_VAT_NUMBER, $companyVatNumber);
    }

    /**
     * Get contact_name
     * @return string|null
     */
    public function getContactName()
    {
        return $this->_get(self::CONTACT_NAME);
    }

    /**
     * Set contact_name
     * @param string $contactName
     * @return \Chottvn\PriceQuote\Api\Data\RequestInterface
     */
    public function setContactName($contactName)
    {
        return $this->setData(self::CONTACT_NAME, $contactName);
    }

    /**
     * Get contact_phone_number
     * @return string|null
     */
    public function getContactPhoneNumber()
    {
        return $this->_get(self::CONTACT_PHONE_NUMBER);
    }

    /**
     * Set contact_phone_number
     * @param string $contactPhoneNumber
     * @return \Chottvn\PriceQuote\Api\Data\RequestInterface
     */
    public function setContactPhoneNumber($contactPhoneNumber)
    {
        return $this->setData(self::CONTACT_PHONE_NUMBER, $contactPhoneNumber);
    }

    /**
     * Get contact_email
     * @return string|null
     */
    public function getContactEmail()
    {
        return $this->_get(self::CONTACT_EMAIL);
    }

    /**
     * Set contact_email
     * @param string $contactEmail
     * @return \Chottvn\PriceQuote\Api\Data\RequestInterface
     */
    public function setContactEmail($contactEmail)
    {
        return $this->setData(self::CONTACT_EMAIL, $contactEmail);
    }

    /**
     * Get status
     * @return string|null
     */
    public function getStatus()
    {
        return $this->_get(self::STATUS);
    }

    /**
     * Set status
     * @param string $status
     * @return \Chottvn\PriceQuote\Api\Data\RequestInterface
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * Get assignee_id
     * @return string|null
     */
    public function getAssigneeId()
    {
        return $this->_get(self::ASSIGNEE_ID);
    }

    /**
     * Set assignee_id
     * @param string $assigneeId
     * @return \Chottvn\PriceQuote\Api\Data\RequestInterface
     */
    public function setAssigneeId($assigneeId)
    {
        return $this->setData(self::ASSIGNEE_ID, $assigneeId);
    }

    /**
     * Get email_sent_at
     * @return string|null
     */
    public function getEmailSentAt()
    {
        return $this->_get(self::EMAIL_SENT_AT);
    }

    /**
     * Set email_sent_at
     * @param string $emailSentAt
     * @return \Chottvn\PriceQuote\Api\Data\RequestInterface
     */
    public function setEmailSentAt($emailSentAt)
    {
        return $this->setData(self::EMAIL_SENT_AT, $emailSentAt);
    }

    /**
     * Get email_sent_count
     * @return string|null
     */
    public function getEmailSentCount()
    {
        return $this->_get(self::EMAIL_SENT_COUNT);
    }

    /**
     * Set email_sent_count
     * @param string $emailSentCount
     * @return \Chottvn\PriceQuote\Api\Data\RequestInterface
     */
    public function setEmailSentCount($emailSentCount)
    {
        return $this->setData(self::EMAIL_SENT_COUNT, $emailSentCount);
    }

    /**
     * Get sms_sent_at
     * @return string|null
     */
    public function getSmsSentAt()
    {
        return $this->_get(self::SMS_SENT_AT);
    }

    /**
     * Set sms_sent_at
     * @param string $smsSentAt
     * @return \Chottvn\PriceQuote\Api\Data\RequestInterface
     */
    public function setSmsSentAt($smsSentAt)
    {
        return $this->setData(self::SMS_SENT_AT, $smsSentAt);
    }

    /**
     * Get sms_sent_count
     * @return string|null
     */
    public function getSmsSentCount()
    {
        return $this->_get(self::SMS_SENT_COUNT);
    }

    /**
     * Set sms_sent_count
     * @param string $smsSentCount
     * @return \Chottvn\PriceQuote\Api\Data\RequestInterface
     */
    public function setSmsSentCount($smsSentCount)
    {
        return $this->setData(self::SMS_SENT_COUNT, $smsSentCount);
    }

    /**
     * Get created_at
     * @return string|null
     */
    public function getCreatedAt()
    {
        return $this->_get(self::CREATED_AT);
    }

    /**
     * Set created_at
     * @param string $createdAt
     * @return \Chottvn\PriceQuote\Api\Data\RequestInterface
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * Get updated_at
     * @return string|null
     */
    public function getUpdatedAt()
    {
        return $this->_get(self::UPDATED_AT);
    }

    /**
     * Set updated_at
     * @param string $updatedAt
     * @return \Chottvn\PriceQuote\Api\Data\RequestInterface
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }

    /**
     * Get quote_id
     * @return string|null
     */
    public function getQuoteId()
    {
        return $this->_get(self::QUOTE_ID);
    }

    /**
     * Set quote_id
     * @param string $quoteId
     * @return \Chottvn\PriceQuote\Api\Data\RequestInterface
     */
    public function setQuoteId($quoteId)
    {
        return $this->setData(self::QUOTE_ID, $quoteId);
    }

    /**
     * Get order_id
     * @return string|null
     */
    public function getOrderId()
    {
        return $this->_get(self::ORDER_ID);
    }

    /**
     * Set order_id
     * @param string $orderId
     * @return \Chottvn\PriceQuote\Api\Data\RequestInterface
     */
    public function setOrderId($orderId)
    {
        return $this->setData(self::ORDER_ID, $orderId);
    }

    /**
     * Get url_key
     * @return string|null
     */
    public function getUrlKey()
    {
        return $this->_get(self::URL_KEY);
    }

    /**
     * Set url_key
     * @param string $urlKey
     * @return \Chottvn\PriceQuote\Api\Data\RequestInterface
     */
    public function setUrlKey($urlKey)
    {
        return $this->setData(self::URL_KEY, $urlKey);
    }

    /**
     * Get file_path
     * @return string|null
     */
    public function getFilePath()
    {
        return $this->_get(self::FILE_PATH);
    }

    /**
     * Set file_path
     * @param string $filePath
     * @return \Chottvn\PriceQuote\Api\Data\RequestInterface
     */
    public function setFilePath($filePath)
    {
        return $this->setData(self::FILE_PATH, $filePath);
    }
     /**
     * Get grandtotal
     * @return string|null
     */
    public function getGrandTotal(){
        return $this->_get(self::GRAND_TOTAL);
    }

    /**
     * Set grandtotal
     * @param string $grandtotal
     * @return \Chottvn\PriceQuote\Api\Data\RequestInterface
     */
    public function setGrandTotal($grandtotal){
        return $this->setData(self::GRAND_TOTAL, $grandtotal);
    }

    /**
     * Get basegrandtotal
     * @return string|null
     */
    public function getBaseGrandTotal(){
        return $this->_get(self::BASE_GRAND_TOTAL);
    }

    /**
     * Set basegrandtotal
     * @param string $basegrandtotal
     * @return \Chottvn\PriceQuote\Api\Data\RequestInterface
     */
    public function setBaseGrandTotal($basegrandtotal){
        return $this->setData(self::BASE_GRAND_TOTAL, $basegrandtotal);
    }

    /**
     * Get checkoutmethod
     * @return string|null
     */
    public function getCheckoutMethod(){
        return $this->_get(self::CHECKOUT_METHOD);
    }

    /**
     * Set checkoutmethod
     * @param string $checkoutmethod
     * @return \Chottvn\PriceQuote\Api\Data\RequestInterface
     */
    public function setCheckoutMethod($checkoutmethod){
        return $this->setData(self::CHECKOUT_METHOD, $checkoutmethod);
    }

    /**
     * Get appliedruleids
     * @return string|null
     */
    public function getAppliedRuleIds(){
        return $this->_get(self::APPLIED_RULE_IDS);
    }

    /**
     * Set appliedruleids
     * @param string $appliedruleids
     * @return \Chottvn\PriceQuote\Api\Data\RequestInterface
     */
    public function setAppliedRuleIds($appliedruleids){
        return $this->setData(self::APPLIED_RULE_IDS, $appliedruleids);
    }

    /**
     * Get couponcode
     * @return string|null
     */
    public function getCouponCode(){
        return $this->_get(self::COUPON_CODE);
    }

    /**
     * Set couponcode
     * @param string $couponcode
     * @return \Chottvn\PriceQuote\Api\Data\RequestInterface
     */
    public function setCouponCode($couponcode){
        return $this->setData(self::COUPON_CODE, $couponcode);
    }

     /**
     * Get subtotal
     * @return string|null
     */
    public function getSubtotal(){
        return $this->_get(self::SUBTOTAL);
    }

    /**
     * Set subtotal
     * @param string $subtotal
     * @return \Chottvn\PriceQuote\Api\Data\RequestInterface
     */
    public function setSubtotal($subtotal){
        return $this->setData(self::SUBTOTAL, $subtotal);
    }

    /**
     * Get basesubtotal
     * @return string|null
     */
    public function getBaseSubtotal(){
        return $this->_get(self::BASE_SUBTOTAL);
    }

    /**
     * Set basesubtotal
     * @param string $basesubtotal
     * @return \Chottvn\PriceQuote\Api\Data\RequestInterface
     */
    public function setBaseSubtotal($basesubtotal){
        return $this->setData(self::BASE_SUBTOTAL, $basesubtotal);
    }

    /**
     * Get savingsamount
     * @return string|null
     */
    public function getSavingsAmount(){
        return $this->_get(self::SAVINGS_AMOUNT);
    }

    /**
     * Set savingsamount
     * @param string $savingsamount
     * @return \Chottvn\PriceQuote\Api\Data\RequestInterface
     */
    public function setSavingsAmount($savingsamount){
        return $this->setData(self::SAVINGS_AMOUNT, $savingsamount);
    }

     /**
     * Get basesavingsamount
     * @return string|null
     */
    public function getBaseSavingsAmount(){
        return $this->_get(self::BASE_SAVINGS_AMOUNT);
    }

    /**
     * Set basesavingsamount
     * @param string $basesavingsamount
     * @return \Chottvn\PriceQuote\Api\Data\RequestInterface
     */
    public function setBaseSavingsAmount($basesavingsamount){
        return $this->setData(self::BASE_SAVINGS_AMOUNT, $basesavingsamount);
    }

    /**
     * Get originaltotal
     * @return string|null
     */
    public function getOriginalTotal(){
        return $this->_get(self::ORIGINAL_TOTAL);

    }

    /**
     * Set originaltotal
     * @param string $originaltotal
     * @return \Chottvn\PriceQuote\Api\Data\RequestInterface
     */
    public function setOriginalTotal($originaltotal){
        return $this->setData(self::ORIGINAL_TOTAL, $originaltotal);

    }

    /**
     * Get flagshipping
     * @return string|null
     */
    public function getFlagShipping(){
        return $this->_get(self::FLAG_SHIPPING);
    }

    /**
     * Set flagshipping
     * @param string $flagshipping
     * @return \Chottvn\PriceQuote\Api\Data\RequestInterface
     */
    public function setFlagShipping($flagshipping){
        return $this->setData(self::FLAG_SHIPPING, $flagshipping);
    }
     /**
     * Get shippingamount
     * @return string|null
     */
    public function getShippingAmount(){
        return $this->_get(self::SHIPPING_AMOUNT);
    }

    /**
     * Set shippingamount
     * @param string $shippingamount
     * @return \Chottvn\PriceQuote\Api\Data\RequestInterface
     */
    public function setShippingAmount($shippingamount){
        return $this->setData(self::SHIPPING_AMOUNT, $shippingamount);
    }
    
    /**
     * Get street
     * @return string|null
     */
    public function getStreet(){
        return $this->_get(self::STREET);
    }

    /**
     * Set street
     * @param string $street
     * @return \Chottvn\PriceQuote\Api\Data\RequestInterface
     */
    public function setStreet($street){
        return $this->setData(self::STREET, $street);
    }

     /**
     * Get city
     * @return string|null
     */
    public function getCity(){
        return $this->_get(self::CITY);
    }

    /**
     * Set city
     * @param string $city
     * @return \Chottvn\PriceQuote\Api\Data\RequestInterface
     */
    public function setCity($city){
        return $this->setData(self::CITY, $city);
    }

     /**
     * Get region
     * @return string|null
     */
    public function getRegion(){
        return $this->_get(self::REGION);
    }

    /**
     * Set region
     * @param string $region
     * @return \Chottvn\PriceQuote\Api\Data\RequestInterface
     */
    public function setRegion($region){
        return $this->setData(self::REGION, $region);

    }

    /**
     * Get regionid
     * @return string|null
     */
    public function getRegionId(){
        return $this->_get(self::REGION_ID);
    }

    /**
     * Set regionid
     * @param string $regionid
     * @return \Chottvn\PriceQuote\Api\Data\RequestInterface
     */
    public function setRegionId($regionid){
        return $this->setData(self::REGION_ID, $regionid);
    }
    /**
     * Get postcode
     * @return string|null
     */
    public function getPostcode(){
        return $this->_get(self::POSTCODE);
    }

    /**
     * Set postcode
     * @param string $postcode
     * @return \Chottvn\PriceQuote\Api\Data\RequestInterface
     */
    public function setPostcode($postcode){
        return $this->setData(self::POSTCODE, $postcode);
    }
    
    /**
     * Get countryid
     * @return string|null
     */
    public function getCountryId(){

        return $this->_get(self::COUNTRY_ID);
    }

    /**
     * Set countryid
     * @param string $countryid
     * @return \Chottvn\PriceQuote\Api\Data\RequestInterface
     */
    public function setCountryId($countryid){
        return $this->setData(self::COUNTRY_ID, $countryid);

    }

    /**
     * Get shippingmethod
     * @return string|null
     */
    public function getShippingMethod(){
        return $this->_get(self::SHIPPING_METHOD);
    }

    /**
     * Set shippingmethod
     * @param string $shippingmethod
     * @return \Chottvn\PriceQuote\Api\Data\RequestInterface
     */
    public function setShippingMethod($shippingmethod){
        return $this->setData(self::SHIPPING_METHOD, $shippingmethod);
    }

     /**
     * Get cityid
     * @return string|null
     */
    public function getCityId(){
        return $this->_get(self::CITY_ID);

    }

    /**
     * Set cityid
     * @param string $cityid
     * @return \Chottvn\PriceQuote\Api\Data\RequestInterface
     */
    public function setCityId($cityid){
        return $this->setData(self::CITY_ID, $cityid);
    }

     /**
     * Get township
     * @return string|null
     */
    public function getTownship(){
        return $this->_get(self::TOWNSHIP);
    }

    /**
     * Set township
     * @param string $township
     * @return \Chottvn\PriceQuote\Api\Data\RequestInterface
     */
    public function setTownship($township){
        return $this->setData(self::TOWNSHIP, $township);
    }


     /**
     * Get townshipid
     * @return string|null
     */
    public function getTownshipId(){
        return $this->_get(self::TOWNSHIP_ID);

    }

    /**
     * Set townshipid
     * @param string $townshipid
     * @return \Chottvn\PriceQuote\Api\Data\RequestInterface
     */
    public function setTownshipId($townshipid){
        return $this->setData(self::TOWNSHIP_ID, $townshipid);

    }
}

