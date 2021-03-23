<?php
/**
 * Chottvn
 *
 * NOTICE OF LICENSE
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to contact@bird.eu so we can send you a copy immediately.
 *
 */

namespace Chottvn\OfflineShipping\Api\Data;
  
/**
 * Interface StudentInterface
 * @api
 */
interface TablerateInterface
{
    const PK = 'pk';	
    const WEBSITEID = 'website_id';	
    const DESTCOUNTRYID = 'dest_country_id';	
    const DESTREGIONID	= 'dest_region_id';
    const DESTZIP	= 'dest_zip';
    const CONDITIONNAME	= 'condition_name';
    const CONDITIONVALUE= 'condition_value';
    const PRICE= 'price';
    const COST = 'cost';
    const MAXDELIVERYDATES= 'max_delivery_dates';

     /**
     * Get PK
     *
     * @return string
     */
    public function getId();

    /**
     * Get Id Website
     *
     * @return string
     */
    public function getWebsiteId();

    /**
     * Get Dest Country ID
     *
     * @return int
     */
    public function getDestCountryId();

     /**
     * Get Dest Region Id
     *
     * @return int
     */
    public function getDestRegionId();

    /**
     * Get Dest Zip
     *
     * @return int
     */
    public function getDestZip();
    
    /**
     * Get Condition Name
     *
     * @return int
     */
    public function getConditionName();
    
    /**
     * Get Condition Name
     *
     * @return int
     */
    public function getConditionValue();

    /**
     * Get Condition Name
     *
     * @return int
     */
    public function getPrice();
    
     /**
     * Get Condition Name
     *
     * @return int
     */
    public function getCost();
    
    /**
     * Get Max Delivery Dates
     *
     * @return int
     */
    public function getMaxDeliveryDates();
    
    /**
     * 
     *
     * @param int $param
     * @return 
     */
    public function setId($param);

    /**
     * 
     *
     * @param string $param
     * @return 
     */
    public function setWebsiteId($param);

    /**
     * 
     *
     * @param int $param
     * @return 
     */
    public function setDestCountryId($param);

    /**
     * 
     *
     * @param int $param
     * @return 
     */
    public function setDestRegionId($param);

    /**
     * 
     *
     * @param int $param
     * @return 
     */
    public function setDestZip($param);
    /**
     * Set Condition Name
     *
     * @return int
     */
    public function setConditionName($param);
    
    /**
     * Set Condition Name
     *
     * @return int
     */
    public function setConditionValue($param);

    /**
     * Set Condition Name
     *
     * @return int
     */
    public function setPrice($param);
    
     /**
     * Set Cost
     *
     * @return int
     */
    public function setCost($param);
    
    /**
     * Set Max Delivery Dates
     *
     * @return $param
     */
    public function setMaxDeliveryDates($param);
    
}