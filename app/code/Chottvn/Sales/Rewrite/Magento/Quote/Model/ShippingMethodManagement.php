<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Chottvn\Sales\Rewrite\Magento\Quote\Model;

use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\EstimateAddressInterface;
use Magento\Quote\Api\ShipmentEstimationInterface;
use Magento\Quote\Model\ResourceModel\Quote\Address as QuoteAddressResource;
use Chottvn\Sales\Helper\Data as HelperData;
/**
 * Shipping method read service
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ShippingMethodManagement extends \Magento\Quote\Model\ShippingMethodManagement implements
    \Magento\Quote\Api\ShippingMethodManagementInterface,
    \Magento\Quote\Model\ShippingMethodManagementInterface,
    ShipmentEstimationInterface
{
    public $helperData;
    /**
     * Quote repository.
     *
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * Shipping method converter
     *
     * @var \Magento\Quote\Model\Cart\ShippingMethodConverter
     */
    protected $converter;

    /**
     * Customer Address repository
     *
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     */
    protected $addressRepository;

    /**
     * @var Quote\TotalsCollector
     */
    protected $totalsCollector;

    /**
     * @var \Magento\Framework\Reflection\DataObjectProcessor $dataProcessor
     */
    private $dataProcessor;

    /**
     * @var AddressInterfaceFactory $addressFactory
     */
    private $addressFactory;

    /**
     * @var QuoteAddressResource
     */
    private $quoteAddressResource;

    /**
     * Constructor
     *
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magento\Quote\Model\Cart\ShippingMethodConverter $converter
     * @param \Magento\Customer\Api\AddressRepositoryInterface $addressRepository
     * @param Quote\TotalsCollector $totalsCollector
     * @param AddressInterfaceFactory|null $addressFactory
     * @param QuoteAddressResource|null $quoteAddressResource
     */
    public function __construct(
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Quote\Model\Cart\ShippingMethodConverter $converter,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Quote\Model\Quote\TotalsCollector $totalsCollector,
        AddressInterfaceFactory $addressFactory = null,
        HelperData $helperData,
        QuoteAddressResource $quoteAddressResource = null
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->converter = $converter;
        $this->addressRepository = $addressRepository;
        $this->helperData = $helperData;
        $this->totalsCollector = $totalsCollector;
        $this->addressFactory = $addressFactory ?: ObjectManager::getInstance()
            ->get(AddressInterfaceFactory::class);
        $this->quoteAddressResource = $quoteAddressResource ?: ObjectManager::getInstance()
            ->get(QuoteAddressResource::class);

        parent::__construct(
            $quoteRepository,
            $converter,
            $addressRepository,
            $totalsCollector,
            $addressFactory,
            $quoteAddressResource);
    }

    /**
     * {@inheritDoc}
     */
    public function get($cartId)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);

        /** @var \Magento\Quote\Model\Quote\Address $shippingAddress */
        $shippingAddress = $quote->getShippingAddress();
        if (!$shippingAddress->getCountryId()) {
            throw new StateException(__('The shipping address is missing. Set the address and try again.'));
        }

        $shippingMethod = $shippingAddress->getShippingMethod();
        if (!$shippingMethod) {
            return null;
        }

        $shippingAddress->collectShippingRates();
        /** @var \Magento\Quote\Model\Quote\Address\Rate $shippingRate */
        $shippingRate = $shippingAddress->getShippingRateByCode($shippingMethod);
        if (!$shippingRate) {
            return null;
        }
        return $this->converter->modelToDataObject($shippingRate, $quote->getQuoteCurrencyCode());
    }

    /**
     * {@inheritDoc}
     */
    public function getList($cartId)
    {
        $output = [];

        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);

        // no methods applicable for empty carts or carts with virtual products
        if ($quote->isVirtual() || 0 == $quote->getItemsCount()) {
            return [];
        }

        $shippingAddress = $quote->getShippingAddress();
        if (!$shippingAddress->getCountryId()) {
            throw new StateException(__('The shipping address is missing. Set the address and try again.'));
        }
        $shippingAddress->collectShippingRates();
        $shippingRates = $shippingAddress->getGroupedAllShippingRates();
        foreach ($shippingRates as $carrierRates) {
            foreach ($carrierRates as $rate) {
                $output[] = $this->converter->modelToDataObject($rate, $quote->getQuoteCurrencyCode());
            }
        }
        return $output;
    }

    /**
     * {@inheritDoc}
     */
    public function set($cartId, $carrierCode, $methodCode)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);
        try {
            $this->apply($cartId, $carrierCode, $methodCode);
        } catch (\Exception $e) {
            throw $e;
        }

        try {
            $this->quoteRepository->save($quote->collectTotals());
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('The shipping method can\'t be set. %1', $e->getMessage()));
        }
        return true;
    }

    /**
     * @param int $cartId The shopping cart ID.
     * @param string $carrierCode The carrier code.
     * @param string $methodCode The shipping method code.
     * @return void
     * @throws InputException The shipping method is not valid for an empty cart.
     * @throws NoSuchEntityException CThe Cart includes virtual product(s) only, so a shipping address is not used.
     * @throws StateException The billing or shipping address is not set.
     * @throws \Exception
     */
    public function apply($cartId, $carrierCode, $methodCode)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);
        if (0 == $quote->getItemsCount()) {
            throw new InputException(
                __('The shipping method can\'t be set for an empty cart. Add an item to cart and try again.')
            );
        }
        if ($quote->isVirtual()) {
            throw new NoSuchEntityException(
                __('The Cart includes virtual product(s) only, so a shipping address is not used.')
            );
        }
        $shippingAddress = $quote->getShippingAddress();
        if (!$shippingAddress->getCountryId()) {
            // Remove empty quote address
            $this->quoteAddressResource->delete($shippingAddress);
            throw new StateException(__('The shipping address is missing. Set the address and try again.'));
        }
        $shippingAddress->setShippingMethod($carrierCode . '_' . $methodCode);
    }

    /**
     * {@inheritDoc}
     */
    public function estimateByAddress($cartId, \Magento\Quote\Api\Data\EstimateAddressInterface $address)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);

        // no methods applicable for empty carts or carts with virtual products
        if ($quote->isVirtual() || 0 == $quote->getItemsCount()) {
            return [];
        }

        return $this->getShippingMethods($quote, $address);
    }

    /**
     * @inheritdoc
     */
    public function estimateByExtendedAddress($cartId, AddressInterface $address)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);

        // no methods applicable for empty carts or carts with virtual products
        if ($quote->isVirtual() || 0 == $quote->getItemsCount()) {
            return [];
        }
        return $this->getShippingMethods($quote, $address);
    }

    /**
     * {@inheritDoc}
     */
    public function estimateByAddressId($cartId, $addressId)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);

        // no methods applicable for empty carts or carts with virtual products
        if ($quote->isVirtual() || 0 == $quote->getItemsCount()) {
            return [];
        }
        $address = $this->addressRepository->getById($addressId);

        return $this->getShippingMethods($quote, $address);
    }

    /**
     * Get estimated rates
     *
     * @param Quote $quote
     * @param int $country
     * @param string $postcode
     * @param int $regionId
     * @param string $region
     * @param \Magento\Framework\Api\ExtensibleDataInterface|null $address
     * @return \Magento\Quote\Api\Data\ShippingMethodInterface[] An array of shipping methods.
     * @deprecated 100.1.6
     */
    protected function getEstimatedRates(
        \Magento\Quote\Model\Quote $quote,
        $country,
        $postcode,
        $regionId,
        $region,
        $address = null
    ) {
        if (!$address) {
            $address = $this->getAddressFactory()->create()
                ->setCountryId($country)
                ->setPostcode($postcode)
                ->setRegionId($regionId)
                ->setRegion($region);
        }
        return $this->getShippingMethods($quote, $address);
    }

    /**
     * Get list of available shipping methods
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Framework\Api\ExtensibleDataInterface $address
     * @return \Magento\Quote\Api\Data\ShippingMethodInterface[]
     */
    private function getShippingMethods(\Magento\Quote\Model\Quote $quote, $address)
    {
        try {
            $output = [];  
            $shippingAddress = $quote->getShippingAddress();
            $city_id = $address->getExtensionAttributes()->getCityId();
            if($city_id){
                $postcode = $this->getPostcodeFromAddress($city_id);
                $address->setPostCode($postcode);
            }
            $flagShipping = $quote->getFlagShipping();
            $shippingAddress->addData($this->extractAddressData($address));
            $shippingAddress->setCollectShippingRates(true);
            // get maxdelivery
            $maxDeliveryDates = $shippingAddress->getMaxDeliveryDatesFromAddress();
            // handle maxdelivery
            $maxDeliveryDates = $this->helperData->getMaxDeliveryDates($maxDeliveryDates);
            $this->totalsCollector->collectAddressTotals($quote, $shippingAddress);            
            $shippingRates = $shippingAddress->getGroupedAllShippingRates();
            foreach ($shippingRates as $carrierRates) {
                foreach ($carrierRates as $rate) {
                    $methodData = $this->converter->modelToDataObject($rate, $quote->getQuoteCurrencyCode());
                    // $amount = $methodData->getAmount();
                    // if($amount >= 0){
                    //     $flagShipping = $this->getFlagFreeShiping($quote,$amount,$methodData);
                    //     // $quote->setFlagShipping($flagShipping);
                    //     // $quote->save();
                    // }
                    $quote->setMaxDeliveryDates($maxDeliveryDates);
                    $quote->save();
                    if (empty($flagShipping)) {
                        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                        $shipping = $objectManager->get('Chottvn\Sales\Rewrite\Magento\Quote\Model\Quote\Address\Total\Shipping');
                        $flagShipping = $shipping->isOverWeight();
                    }
                    // $this->writeLog($flagShipping);

                    $methodData->setFlagShipping($flagShipping);
                    $methodData->setMaxDeliveryDates($maxDeliveryDates);

                    $output[] = $methodData;
                }
            }
    }
    catch(\Exception $e){
        $this->writeLog("Exception getShippingMethods:");
        $this->writeLog($e);
    }              
        return $output;
    }

    /**
     * Get Flag Free Shiping
     *
     * @param $quote
     * @param $amount
     * @param $methodData
     * @return $flagShipping
     */
    private function getFlagFreeShiping($quote,$amount,$methodData){
        try {
            // rules 
            $quoteId = $quote->getId();
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
            $connection = $resource->getConnection();
            $select = $connection->select()
                ->from(
                    ['ce' => 'quote_item']
                )
                ->where("ce.quote_id = ?", $quoteId);
            $data = $connection->fetchAll($select);
            // get all rules 
            $appliedRuleIds = [];
            foreach ($data as $quoteItem) 
            {
                $array = explode(',', $quoteItem['applied_rule_ids']);
                foreach($array as $item){
                    array_push($appliedRuleIds,$item);
                }
            }
            if($appliedRuleIds){
                $appliedRuleIds = array_unique($appliedRuleIds);
            }
            $rule = $objectManager->create('\Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory');
            $flagShipping = '';
            // total
            $totals = $quote->getTotals();
            $originaltotal = round($totals["original_total"]->getValue()); 
            if($appliedRuleIds){
                foreach($appliedRuleIds as $item){
                    $collection = $rule->create()->addFieldToFilter("rule_id", ["eq"=> $item])->addFieldToFilter("simple_free_shipping", ["neq"=> 0]);
                    if($collection){
                        foreach ($collection as $data) {
                            $simple_free_shipping = $data->getData('simple_free_shipping');
                            $conditions_serialized = $data->getData('conditions_serialized');
                            $value_greater = 0;
                            if($conditions_serialized){
                                $array_conditions_serialized = json_decode($conditions_serialized,true);
                                if (array_key_exists("conditions",$array_conditions_serialized)){
                                    $array_conditions_serialized = $array_conditions_serialized['conditions'];
                                    foreach($array_conditions_serialized as $value){
                                        if (array_key_exists("attribute",$value) && array_key_exists("operator",$value) ){
                                            if($value['attribute'] == 'base_subtotal' && $value['operator'] == '>='){
                                                $value_greater = intval($value['value']);
                                            }
                                        }
                                    }
                                }
                            }
                            if($simple_free_shipping == 2){
                                if($value_greater > 0){
                                    if($originaltotal >= $value_greater){
                                        $flagShipping = 'freeshipping';
                                        $methodData->setAmount(0);
                                        return $flagShipping;
                                    }
                                }
                            } else if($simple_free_shipping == 1){
                                if($amount <= 0){
                                    $flagShipping = 'freeshipping';
                                    $methodData->setAmount(0);
                                    return $flagShipping;
                                }
                            }
                        }
                    }
                }
            }           
            return $flagShipping;
        }
        catch(\Exception $e){
            $this->writeLog("Exception:");
            $this->writeLog($e);
        }              
        return $flagShipping;
    }
    /**
     * Get PostCode from City Id
     *
     * @param 
     * @return 
     */
    private function getPostcodeFromAddress($cityid){
        $postCode =  null;

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        // $this->writeLog('=== GetPostcodeFromAddress');  
            // $this->writeLog("RegionId :".$address->getRegionId());  
            // $this->writeLog("RegionCode :".$address->getRegionCode()); 
            // $this->writeLog("PostCode :".$address->getPostcode());   
            // $this->writeLog("City: ".$address->getCity()); 
        try {         
            if(!empty($cityid)){                
                $city = $objectManager->get('\Chottvn\Address\Model\ResourceModel\City\CollectionFactory')->create()->addFieldToFilter(
                    'city_id',
                    ['eq' => $cityid]
                )->getFirstItem();                
                if (!empty($city)){
                    $postCode = $city->getPostcode();
                }
            }            
        }
        catch(\Exception $e){
            $this->writeLog("Exception:");
            $this->writeLog($e);
        }              
        return $postCode;
    }
    /**
     * Get list of available shipping methods
     *
     * @param \Magento\Quote\Model\Quote\Address $quoteAddress
     * @return int
     */
    /*private function getMaxDeliveryDatesFromQuoteAddress($quoteAddress){
        $maxDeliveryDates =  0;

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();                
        
        $tableName = $resource->getTableName('shipping_tablerate');        
        
        $countryId = $quoteAddress->getCountryId();
        $regionId = $quoteAddress->getRegionId();        
        $postcode = $quoteAddress->getPostCodeFromAddress();
        if(!$postcode){
            $postcode  = 0;
        }
        $select = $connection->select()
            ->from($tableName)
            ->where("dest_country_id = ? "
                , $countryId
            )
            ->where("dest_region_id = ? "
                , $regionId 
            )
            ->where("dest_zip = ? OR dest_zip = '*' "                
                , $postcode
            )
            ->order("dest_zip DESC")
            ;
        
        $row = $connection->fetchRow($select);
        $this->writeLog("MaxDeliver: ". empty($row));
        $this->writeLog($select);        
        $this->writeLog($row["max_delivery_dates"]);

        if(!empty($row)){
            $maxDeliveryDates = $row["max_delivery_dates"];
        }
        return $maxDeliveryDates;
    }*/

    /**
     * Get transform address interface into Array
     *
     * @param \Magento\Framework\Api\ExtensibleDataInterface  $address
     * @return array
     */
    private function extractAddressData($address)
    {
        $className = \Magento\Customer\Api\Data\AddressInterface::class;
        if ($address instanceof \Magento\Quote\Api\Data\AddressInterface) {
            $className = \Magento\Quote\Api\Data\AddressInterface::class;
        } elseif ($address instanceof EstimateAddressInterface) {
            $className = EstimateAddressInterface::class;
        }
        return $this->getDataObjectProcessor()->buildOutputDataArray(
            $address,
            $className
        );
    }

    /**
     * Gets the data object processor
     *
     * @return \Magento\Framework\Reflection\DataObjectProcessor
     * @deprecated 101.0.0
     */
    private function getDataObjectProcessor()
    {
        if ($this->dataProcessor === null) {
            $this->dataProcessor = ObjectManager::getInstance()
                ->get(DataObjectProcessor::class);
        }
        return $this->dataProcessor;
    }

    /**
    * @param $info
    * @param $type  [error, warning, info]
    * @return 
    */
    private function writeLog($info, $type = "info"){
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