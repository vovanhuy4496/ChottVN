<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Chottvn\Sales\Rewrite\Magento\Quote\Model\Quote;

use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\Data\RegionInterfaceFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\StoreManagerInterface;
/**
 * Sales Quote address model
 *
 * @api
 * @method int getQuoteId()
 * @method Address setQuoteId(int $value)
 * @method string getCreatedAt()
 * @method Address setCreatedAt(string $value)
 * @method string getUpdatedAt()
 * @method Address setUpdatedAt(string $value)
 * @method \Magento\Customer\Api\Data\AddressInterface getCustomerAddress()
 * @method Address setCustomerAddressData(\Magento\Customer\Api\Data\AddressInterface $value)
 * @method string getAddressType()
 * @method Address setAddressType(string $value)
 * @method int getFreeShipping()
 * @method Address setFreeShipping(int $value)
 * @method bool getCollectShippingRates()
 * @method Address setCollectShippingRates(bool $value)
 * @method Address setShippingMethod(string $value)
 * @method string getShippingDescription()
 * @method Address setShippingDescription(string $value)
 * @method float getWeight()
 * @method Address setWeight(float $value)
 * @method float getSubtotal()
 * @method Address setSubtotal(float $value)
 * @method float getBaseSubtotal()
 * @method Address setBaseSubtotal(float $value)
 * @method Address setSubtotalWithDiscount(float $value)
 * @method Address setBaseSubtotalWithDiscount(float $value)
 * @method float getTaxAmount()
 * @method Address setTaxAmount(float $value)
 * @method float getBaseTaxAmount()
 * @method Address setBaseTaxAmount(float $value)
 * @method float getShippingAmount()
 * @method float getBaseShippingAmount()
 * @method float getShippingTaxAmount()
 * @method Address setShippingTaxAmount(float $value)
 * @method float getBaseShippingTaxAmount()
 * @method Address setBaseShippingTaxAmount(float $value)
 * @method float getDiscountAmount()
 * @method Address setDiscountAmount(float $value)
 * @method float getBaseDiscountAmount()
 * @method Address setBaseDiscountAmount(float $value)
 * @method float getGrandTotal()
 * @method Address setGrandTotal(float $value)
 * @method float getBaseGrandTotal()
 * @method Address setBaseGrandTotal(float $value)
 * @method string getCustomerNotes()
 * @method Address setCustomerNotes(string $value)
 * @method string getDiscountDescription()
 * @method Address setDiscountDescription(string $value)
 * @method null|array getDiscountDescriptionArray()
 * @method Address setDiscountDescriptionArray(array $value)
 * @method float getShippingDiscountAmount()
 * @method Address setShippingDiscountAmount(float $value)
 * @method float getBaseShippingDiscountAmount()
 * @method Address setBaseShippingDiscountAmount(float $value)
 * @method float getSubtotalInclTax()
 * @method Address setSubtotalInclTax(float $value)
 * @method float getBaseSubtotalTotalInclTax()
 * @method Address setBaseSubtotalTotalInclTax(float $value)
 * @method int getGiftMessageId()
 * @method Address setGiftMessageId(int $value)
 * @method float getDiscountTaxCompensationAmount()
 * @method Address setDiscountTaxCompensationAmount(float $value)
 * @method float getBaseDiscountTaxCompensationAmount()
 * @method Address setBaseDiscountTaxCompensationAmount(float $value)
 * @method float getShippingDiscountTaxCompensationAmount()
 * @method Address setShippingDiscountTaxCompensationAmount(float $value)
 * @method float getBaseShippingDiscountTaxCompensationAmnt()
 * @method Address setBaseShippingDiscountTaxCompensationAmnt(float $value)
 * @method float getShippingInclTax()
 * @method Address setShippingInclTax(float $value)
 * @method float getBaseShippingInclTax()
 * @method \Magento\SalesRule\Model\Rule[] getCartFixedRules()
 * @method int[] getAppliedRuleIds()
 * @method Address setBaseShippingInclTax(float $value)
 *
 * @property $_objectCopyService \Magento\Framework\DataObject\Copy
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class Address extends \Magento\Quote\Model\Quote\Address implements \Magento\Quote\Api\Data\AddressInterface {
    
    const RATES_FETCH = 1;
    const RATES_RECALCULATE = 2;
    const ADDRESS_TYPE_BILLING = 'billing';
    const ADDRESS_TYPE_SHIPPING = 'shipping';
    /**
     * Prefix of model events
     *
     * @var string
     */
    protected $_eventPrefix = 'sales_quote_address';
    /**
     * Name of event object
     *
     * @var string
     */
    protected $_eventObject = 'quote_address';
    /**
     * Quote object
     *
     * @var \Magento\Quote\Model\Quote
     */
    protected $_items;
    /**
     * Quote object
     *
     * @var \Magento\Quote\Model\Quote
     */
    protected $_quote;
    /**
     * Sales Quote address rates
     *
     * @var \Magento\Quote\Model\Quote\Address\Rate
     */
    protected $_rates;
    /**
     * Total models collector
     *
     * @var \Magento\Quote\Model\Quote\Address\Total\Collector
     */
    protected $_totalCollector;
    /**
     * Total data as array
     *
     * @var array
     */
    protected $_totals = [];
    /**
     * @var array
     */
    protected $_totalAmounts = [];
    /**
     * @var array
     */
    protected $_baseTotalAmounts = [];
    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;
    /**
     * @var \Magento\Quote\Model\Quote\Address\ItemFactory
     */
    protected $_addressItemFactory;
    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote\Address\Item\CollectionFactory
     */
    protected $_itemCollectionFactory;
    /**
     * @var \Magento\Quote\Model\Quote\Address\RateCollectorInterfaceFactory
     */
    protected $_rateCollector;
    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote\Address\Rate\CollectionFactory
     */
    protected $_rateCollectionFactory;
    /**
     * @var \Magento\Quote\Model\Quote\Address\Total\CollectorFactory
     */
    protected $_totalCollectorFactory;
    /**
     * @var \Magento\Quote\Model\Quote\Address\TotalFactory
     */
    protected $_addressTotalFactory;
    /**
     * @var \Magento\Quote\Model\Quote\Address\RateFactory
     * @since 101.0.0
     */
    protected $_addressRateFactory;
    /**
     * @var \Magento\Customer\Api\Data\AddressInterfaceFactory
     */
    protected $addressDataFactory;
    /**
     * @var \Magento\Quote\Model\Quote\Address\Validator
     */
    protected $validator;
    /**
     * @var \Magento\Customer\Model\Address\Mapper
     */
    protected $addressMapper;
    /**
     * @var Address\RateRequestFactory
     */
    protected $_rateRequestFactory;
    /**
     * @var Address\CustomAttributeListInterface
     */
    protected $attributeList;
    /**
     * @var TotalsCollector
     */
    protected $totalsCollector;
    /**
     * @var \Magento\Quote\Model\Quote\TotalsReader
     */
    protected $totalsReader;
    /**
     * @var Json
     */
    private $serializer;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var \Chottvn\Address\Model\ResourceModel\City\Collection
     */
    private $cityCollection;
    /**
     * @var \Chottvn\Address\Model\ResourceModel\Township\Collection
     */
    private $townshipCollection;
    /**
     * @var \Magento\Framework\Webapi\Rest\Request
     */
    private $restRequest;
    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param \Magento\Directory\Helper\Data $directoryData
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Customer\Model\Address\Config $addressConfig
     * @param \Magento\Directory\Model\RegionFactory $regionFactory
     * @param \Magento\Directory\Model\CountryFactory $countryFactory
     * @param AddressMetadataInterface $metadataService
     * @param AddressInterfaceFactory $addressDataFactory
     * @param RegionInterfaceFactory $regionDataFactory
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\ItemFactory $addressItemFactory
     * @param \Magento\Quote\Model\ResourceModel\Quote\Address\Item\CollectionFactory $itemCollectionFactory
     * @param \Magento\Quote\Model\Quote\Address\RateFactory $addressRateFactory
     * @param Address\RateCollectorInterfaceFactory $rateCollector
     * @param \Magento\Quote\Model\ResourceModel\Quote\Address\Rate\CollectionFactory $rateCollectionFactory
     * @param \Magento\Quote\Model\Quote\Address\RateRequestFactory $rateRequestFactory
     * @param \Magento\Quote\Model\Quote\Address\Total\CollectorFactory $totalCollectorFactory
     * @param \Magento\Quote\Model\Quote\Address\TotalFactory $addressTotalFactory
     * @param \Magento\Framework\DataObject\Copy $objectCopyService
     * @param \Magento\Shipping\Model\CarrierFactoryInterface $carrierFactory
     * @param \Magento\Quote\Model\Quote\Address\Validator $validator
     * @param \Magento\Customer\Model\Address\Mapper $addressMapper
     * @param \Magento\Quote\Model\Quote\Address\CustomAttributeListInterface $attributeList
     * @param TotalsCollector $totalsCollector
     * @param \Magento\Quote\Model\Quote\TotalsReader $totalsReader
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     * @param Json $serializer
     * @param StoreManagerInterface $storeManager
     * @param \Chottvn\Address\Model\ResourceModel\City\CollectionFactory $cityCollection
     * @param \Chottvn\Address\Model\ResourceModel\Township\CollectionFactory $townshipCollection
     * @param \Magento\Framework\Webapi\Rest\Request $restRequest
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Directory\Helper\Data $directoryData,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Customer\Model\Address\Config $addressConfig,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        AddressMetadataInterface $metadataService,
        AddressInterfaceFactory $addressDataFactory,
        RegionInterfaceFactory $regionDataFactory,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\ItemFactory $addressItemFactory,
        \Magento\Quote\Model\ResourceModel\Quote\Address\Item\CollectionFactory $itemCollectionFactory,
        \Magento\Quote\Model\Quote\Address\RateFactory $addressRateFactory,
        \Magento\Quote\Model\Quote\Address\RateCollectorInterfaceFactory $rateCollector,
        \Magento\Quote\Model\ResourceModel\Quote\Address\Rate\CollectionFactory $rateCollectionFactory,
        \Magento\Quote\Model\Quote\Address\RateRequestFactory $rateRequestFactory,
        \Magento\Quote\Model\Quote\Address\Total\CollectorFactory $totalCollectorFactory,
        \Magento\Quote\Model\Quote\Address\TotalFactory $addressTotalFactory,
        \Magento\Framework\DataObject\Copy $objectCopyService,
        \Magento\Shipping\Model\CarrierFactoryInterface $carrierFactory,
        \Magento\Quote\Model\Quote\Address\Validator $validator,
        \Magento\Customer\Model\Address\Mapper $addressMapper,
        \Magento\Quote\Model\Quote\Address\CustomAttributeListInterface $attributeList,
        \Magento\Quote\Model\Quote\TotalsCollector $totalsCollector,
        \Magento\Quote\Model\Quote\TotalsReader $totalsReader,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [],
        Json $serializer = null,
        StoreManagerInterface $storeManager = null,
        \Chottvn\Address\Model\ResourceModel\City\CollectionFactory $cityCollection,
        \Chottvn\Address\Model\ResourceModel\Township\CollectionFactory $townshipCollection,
        \Magento\Framework\Webapi\Rest\Request $restRequest
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_addressItemFactory = $addressItemFactory;
        $this->_itemCollectionFactory = $itemCollectionFactory;
        $this->_addressRateFactory = $addressRateFactory;
        $this->_rateCollector = $rateCollector;
        $this->_rateCollectionFactory = $rateCollectionFactory;
        $this->_rateRequestFactory = $rateRequestFactory;
        $this->_totalCollectorFactory = $totalCollectorFactory;
        $this->_addressTotalFactory = $addressTotalFactory;
        $this->_objectCopyService = $objectCopyService;
        $this->_carrierFactory = $carrierFactory;
        $this->addressDataFactory = $addressDataFactory;
        $this->validator = $validator;
        $this->addressMapper = $addressMapper;
        $this->attributeList = $attributeList;
        $this->totalsCollector = $totalsCollector;
        $this->totalsReader = $totalsReader;
        $this->serializer = $serializer ? : ObjectManager::getInstance()->get(Json::class);
        $this->storeManager = $storeManager ? : ObjectManager::getInstance()->get(StoreManagerInterface::class);
        $this->cityCollection = $cityCollection;
        $this->townshipCollection = $townshipCollection;
        $this->restRequest = $restRequest;
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $directoryData,
            $eavConfig,
            $addressConfig,
            $regionFactory,
            $countryFactory,
            $metadataService,
            $addressDataFactory,
            $regionDataFactory,
            $dataObjectHelper,
            $scopeConfig,
            $addressItemFactory,
            $itemCollectionFactory,
            $addressRateFactory,
            $rateCollector,
            $rateCollectionFactory,
            $rateRequestFactory,
            $totalCollectorFactory,
            $addressTotalFactory,
            $objectCopyService,
            $carrierFactory,
            $validator,
            $addressMapper,
            $attributeList,
            $totalsCollector,
            $totalsReader,
            $resource,
            $resourceCollection,
            $data,
            $serializer,
            $storeManager
        );
    }
    /**
     * Initialize resource
     *
     * @return void
     */
    protected function _construct() {
        $this->_init(\Magento\Quote\Model\ResourceModel\Quote\Address::class);
    }
    /**
     * Set the required fields
     *
     * @return void
     */
    protected function _populateBeforeSaveData() {
        if ($this->getQuote()) {
            $this->_dataSaveAllowed = (bool)$this->getQuote()->getId();
            if ($this->getQuote()->getId()) {
                $this->setQuoteId($this->getQuote()->getId());
            }
            $this->setCustomerId($this->getQuote()->getCustomerId());
            /**
             * Init customer address id if customer address is assigned
             */
            if ($this->getCustomerAddressData()) {
                $this->setCustomerAddressId($this->getCustomerAddressData()->getId());
            }
            if (!$this->getId()) {
                $this->setSameAsBilling((int)$this->_isSameAsBilling());
            }
        }
    }
    /**
     * Returns true if shipping address is same as billing
     *
     * @return bool
     */
    protected function _isSameAsBilling() {
        return $this->getAddressType() == \Magento\Quote\Model\Quote\Address::TYPE_SHIPPING && ($this->_isNotRegisteredCustomer() || $this->_isDefaultShippingNullOrSameAsBillingAddress());
    }
    /**
     * Checks if the user is a registered customer
     *
     * @return bool
     */
    protected function _isNotRegisteredCustomer() {
        return !$this->getQuote()->getCustomerId() || $this->getCustomerAddressId() === null;
    }
    /**
     * Returns true if shipping address is same as billing or it is undefined
     *
     * @return bool
     */
    protected function _isDefaultShippingNullOrSameAsBillingAddress() {
        $customer = $this->getQuote()->getCustomer();
        $customerId = $customer->getId();
        $defaultBillingAddress = null;
        $defaultShippingAddress = null;
        if ($customerId) {
            /* we should load data from the service once customer is saved */
            $defaultBillingAddress = $customer->getDefaultBilling();
            $defaultShippingAddress = $customer->getDefaultShipping();
        } else {
            /* we should load data from the quote if customer is not saved yet */
            $defaultBillingAddress = $customer->getDefaultBilling();
            $defaultShippingAddress = $customer->getDefaultShipping();
        }
        return !$defaultShippingAddress || $defaultBillingAddress && $defaultShippingAddress && $defaultBillingAddress == $defaultShippingAddress;
    }
    /**
     * Get PostCode from Address part (City, Township)
     *
     * @param
     * @return
     */
    public function getPostcodeFromAddress() {
        $postCode = null;
        // $this->writeLog('=== GetPostcodeFromAddress');
        // $this->writeLog("QuoteId: ".$this->getQuoteId());
        // $this->writeLog("RegionId :".$this->getRegionId());
        // $this->writeLog("RegionCode :".$this->getRegionCode());
        // $this->writeLog("PostCode :".$this->getPostcode());
        // $this->writeLog("City: ".$this->getCity());
        // $this->writeLog("CityID :".$this->getCityId());
        try {
            if (empty($this->getCityId())) {
                // $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                // $restRequest = $objectManager->get('Magento\Framework\Webapi\Rest\Request');
                // $this->writeLog('request'.$this->restRequest->getBodyParams());
                if ($addressParams = $this->restRequest->getBodyParams()) {
                    if (array_key_exists("address", $addressParams)) {
                        $addressAttrs = $addressParams["address"];
                        if (array_key_exists("custom_attributes", $addressAttrs)) {
                            $addressCustomAttrs = $addressAttrs["custom_attributes"];
                            // $this->writeLog('address'.$addressCustomAttrs);
                            try {
                                foreach ($addressCustomAttrs as $addressCustomAttr) {
                                    $attributeCode = $addressCustomAttr["attribute_code"];
                                    $value = $addressCustomAttr["value"];
                                    // $this->writeLog($attributeCode. " - ".$value);
                                    if ($value != null && $attributeCode) {
                                        $set = 'set' . str_replace(' ', '', ucwords(str_replace('_', ' ', $attributeCode)));
                                        $this->$set($value);
                                    }
                                }
                            }
                            catch(\Exception $e) {
                            }
                        }
                    }
                }
                // $this->writeLog("CityId: ".$this->getCityId());
                
            }
            if (!empty($this->getCityId())) {
                $city = $this->cityCollection->create()->addFieldToFilter('city_id', ['eq' => $this->getCityId() ])->getFirstItem();
                // $this->writeLog($city->getPostcode());
                if (!empty($city)) {
                    $postCode = $city->getPostcode();
                }
            }
            if ($postCode == null) {
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $checkoutSession = $objectManager->get('Magento\Checkout\Model\Session');
                $quoteId = $this->getQuoteId();
                $quoteId = $quoteId ? $quoteId : $checkoutSession->getQuote()->getId();
                if (isset($quoteId)) {
                    $quote = $objectManager->create('Magento\Quote\Model\Quote')->load($quoteId);
                    if (!empty($quote->getShippingAddress()->getCityId())) {
                        $city = $this->cityCollection->create()->addFieldToFilter('city_id', ['eq' => $quote->getShippingAddress()->getCityId() ])->getFirstItem();
                        if (!empty($city)) {
                            $postCode = $city->getPostcode();
                        }
                    }
                }
            }
        }
        catch(\Exception $e) {
            $this->writeLog("Exception:");
            $this->writeLog($e);
        }
        // $this->writeLog($postCode);
        // $this->writeLog('===========================');
        if ($postCode) {
            $this->setPostcode($postCode);
        }
        return $postCode;
    }
    /**
     * Get list of available shipping methods
     *
     * @param \Magento\Quote\Model\Quote\Address $quoteAddress
     * @return int
     */
    public function getMaxDeliveryDatesFromAddress() {
        $maxDeliveryDates = 0;
        $weight = 0;
        $items = $this->getQuote()->getAllItems();
        foreach ($items as $item) {
            $weight += ($item->getWeight() * $item->getQty());
        }
        // $this->writeLog('#weight: '. $weight);
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $tableName = $resource->getTableName('shipping_tablerate');
        $countryId = $this->getCountryId();
        $regionId = $this->getRegionId();
        $postcode = $this->getPostCodeFromAddress();
        if (!$postcode) {
            $postcode = 0;
        }
        $select = $connection->select()->from($tableName)->where("dest_country_id = ? ", $countryId)->where("dest_region_id = ? ", $regionId)->where("condition_name = ? ", 'package_weight')->where("condition_value < ? ", $weight)->where("dest_zip = ? OR dest_zip = '*' ", $postcode)->order("condition_value DESC");
        $row = $connection->fetchRow($select);
        // $this->writeLog("MaxDeliver: ". empty($row));
        // $this->writeLog($select);
        if (!empty($row)) {
            $maxDeliveryDates = $row["max_delivery_dates"];
        }
        // $this->writeLog('#maxDeliveryDates: '.$maxDeliveryDates);
        return $maxDeliveryDates;
    }
    /**
     * Request shipping rates for entire address or specified address item
     *
     * Returns true if current selected shipping method code corresponds to one of the found rates
     *
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function requestShippingRates(\Magento\Quote\Model\Quote\Item\AbstractItem $item = null) {
        // Update PostCode from Address if existed
        if ($postCodeFromAddress = $this->getPostcodeFromAddress()) {
            $this->setPostcode($postCodeFromAddress);
        }
        // $this->writeLog('Final PostCode: '.$this->getPostcode());
        
        /** @var $request \Magento\Quote\Model\Quote\Address\RateRequest */
        $request = $this->_rateRequestFactory->create();
        $request->setAllItems($item ? [$item] : $this->getAllItems());
        $request->setDestCountryId($this->getCountryId());
        $request->setDestRegionId($this->getRegionId());
        $request->setDestRegionCode($this->getRegionCode());
        $request->setDestStreet($this->getStreetFull());
        $request->setDestCity($this->getCity());
        $request->setDestPostcode($this->getPostcode());
        $request->setPackageValue($item ? $item->getBaseRowTotal() : $this->getBaseSubtotal());
        $packageWithDiscount = $item ? $item->getBaseRowTotal() - $item->getBaseDiscountAmount() : $this->getBaseSubtotalWithDiscount();
        $request->setPackageValueWithDiscount($packageWithDiscount);
        $request->setPackageWeight($item ? $item->getRowWeight() : $this->getWeight());
        $request->setPackageQty($item ? $item->getQty() : $this->getItemQty());
        /**
         * Need for shipping methods that use insurance based on price of physical products
         */
        $packagePhysicalValue = $item ? $item->getBaseRowTotal() : $this->getBaseSubtotal() - $this->getBaseVirtualAmount();
        $request->setPackagePhysicalValue($packagePhysicalValue);
        $request->setFreeMethodWeight($item ? 0 : $this->getFreeMethodWeight());
        /**
         * Store and website identifiers specified from StoreManager
         */
        if ($this->getQuote()->getStoreId()) {
            $storeId = $this->getQuote()->getStoreId();
            $request->setStoreId($storeId);
            $request->setWebsiteId($this->storeManager->getStore($storeId)->getWebsiteId());
        } else {
            $request->setStoreId($this->storeManager->getStore()->getId());
            $request->setWebsiteId($this->storeManager->getWebsite()->getId());
        }
        $request->setFreeShipping($this->getFreeShipping());
        /**
         * Currencies need to convert in free shipping
         */
        $request->setBaseCurrency($this->storeManager->getStore()->getBaseCurrency());
        $request->setPackageCurrency($this->storeManager->getStore()->getCurrentCurrency());
        $request->setLimitCarrier($this->getLimitCarrier());
        $baseSubtotalInclTax = $this->getBaseSubtotalTotalInclTax();
        $request->setBaseSubtotalInclTax($baseSubtotalInclTax);
        $result = $this->_rateCollector->create()->collectRates($request)->getResult();
        $found = false;
        if ($result) {
            $shippingRates = $result->getAllRates();
            foreach ($shippingRates as $shippingRate) {
                $rate = $this->_addressRateFactory->create()->importShippingRate($shippingRate);
                if (!$item) {
                    $this->addShippingRate($rate);
                }
                if ($this->getShippingMethod() == $rate->getCode()) {
                    if ($item) {
                        $item->setBaseShippingAmount($rate->getPrice());
                    } else {
                        /** @var \Magento\Store\Api\Data\StoreInterface */
                        $store = $this->storeManager->getStore();
                        $amountPrice = $store->getBaseCurrency()->convert($rate->getPrice(), $store->getCurrentCurrencyCode());
                        $this->setBaseShippingAmount($rate->getPrice());
                        $this->setShippingAmount($amountPrice);
                    }
                    $found = true;
                }
            }
        }
        return $found;
    }
    /**
     * @inheritdoc
     */
    protected function getCustomAttributesCodes() {
        return array_keys($this->attributeList->getAttributes());
    }
    /**
     * @param $info
     * @param $type  [error, warning, info]
     * @return
     */
    private function writeLog($info, $type = "info") {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/quote_address.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        switch ($type) {
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
