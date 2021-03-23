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
namespace Chottvn\OfflineShipping\Model;
use Magento\Framework\Model\AbstractModel;
use Chottvn\OfflineShipping\Api\Data\TablerateInterface;


class Tablerate extends AbstractModel implements TablerateInterface
{

    protected function _construct()
    {
        $this->_init(\Chottvn\OfflineShipping\Model\ResourceModel\Tablerate::class);
    }

    public function getId()
    {
        return $this->_getData(self::PK);
    }

    public function getWebsiteId()
    {
        return $this->_getData(self::WEBSITEID);
    }

    public function getDestCountryId(){
        return $this->_getData(self::DESTCOUNTRYID);
    }

    public function getDestRegionId(){
        return $this->_getData(self::DESTREGIONID);
    }

    public function getDestZip(){
        return $this->_getData(self::DESTZIP);
    }
    
    public function getConditionName(){
        return $this->_getData(self::CONDITIONNAME);
    }
    
    public function getConditionValue(){
        return $this->_getData(self::CONDITIONVALUE);
    }

    public function getPrice(){
        return $this->_getData(self::PRICE);
    }
    
    public function getCost(){
        return $this->_getData(self::COST);
    }
    
    public function getMaxDeliveryDates(){
        return $this->_getData(self::MAXDELIVERYDATES);
    }

    public function setPk($param){
        return $this->setData(self::PK, $param);
    }

    public function setWebsiteId($param){
        return $this->setData(self::WEBSITEID, $param);
    }

    public function setDestCountryId($param){
        return $this->setData(self::DESTCOUNTRYID, $param);
    }

    public function setDestRegionId($param){
        return $this->setData(self::DESTREGIONID, $param);
    }

    public function setDestZip($param){
        return $this->setData(self::DESTZIP, $param);
    }
 
    public function setConditionName($param){
        return $this->setData(self::CONDITIONNAME, $param);
    }
    
    public function setConditionValue($param){
        return $this->setData(self::CONDITIONVALUE, $param);
    }

    public function setPrice($param){
        return $this->setData(self::PRICE, $param);
    }
    
    public function setCost($param){
        return $this->setData(self::COST, $param);
    }
 
    public function setMaxDeliveryDates($param){
        return $this->setData(self::MAXDELIVERYDATES, $param);
    }
}