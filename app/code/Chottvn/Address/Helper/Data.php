<?php

namespace Chottvn\Address\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_CONFIG_PATH_HIDE_COUNTRY = 'customer/customer_address/disable_country';
    const XML_CONFIG_PATH_DEFAULT_COUNTRY = 'customer/customer_address/default_country';
    const XML_CONFIG_PATH_HIDE_TOWNSHIP = 'customer/customer_address/disable_township';

    /**
     * @var \Magento\Framework\DataObject\Copy\Config
     */
    private $fieldsetConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\App\Cache\Type\Config
     */
    private $configCacheType;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $jsonHelper;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    private $localeResolver;

    /**
     * @var \Magento\Customer\Model\AddressFactory
     */
    private $addressFactory;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Address
     */
    private $addressResource;

    /**
     * @var \Chottvn\Address\Model\ResourceModel\City\Collection
     */
    private $cityCollection;

    /**
     * @var \Chottvn\Address\Model\ResourceModel\Township\Collection
     */
    private $townshipCollection;

    /**
     * @var string
     */
    private $cityJson;

    /**
     * @var string
     */
    private $townshipJson;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\DataObject\Copy\Config $fieldsetConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\Cache\Type\Config $configCacheType
     * @param \Magento\Framework\Serialize\Serializer\Json $jsonHelper
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param \Magento\Customer\Model\AddressFactory $addressFactory
     * @param \Magento\Customer\Model\ResourceModel\Address $addressResource
     * @param \Chottvn\Address\Model\ResourceModel\City\CollectionFactory $cityCollection
     * @param \Chottvn\Address\Model\ResourceModel\Township\CollectionFactory $townshipCollection
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\DataObject\Copy\Config $fieldsetConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Cache\Type\Config $configCacheType,
        \Magento\Framework\Serialize\Serializer\Json $jsonHelper,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Customer\Model\AddressFactory $addressFactory,
        \Magento\Customer\Model\ResourceModel\Address $addressResource,
        \Chottvn\Address\Model\ResourceModel\City\CollectionFactory $cityCollection,
        \Chottvn\Address\Model\ResourceModel\Township\CollectionFactory $townshipCollection
    ) {
        $this->fieldsetConfig = $fieldsetConfig;
        $this->storeManager = $storeManager;
        $this->configCacheType = $configCacheType;
        $this->jsonHelper = $jsonHelper;
        $this->localeResolver = $localeResolver;
        $this->addressFactory = $addressFactory;
        $this->addressResource = $addressResource;
        $this->cityCollection = $cityCollection;
        $this->townshipCollection = $townshipCollection;
        parent::__construct($context);
    }

    /**
     * Get extra checkout address fields
     *
     * @return array
     */
    public function getExtraCheckoutAddressFields(
        $fieldset = 'extra_checkout_billing_address_fields',
        $root = 'global'
    ) {
        $fields = $this->fieldsetConfig->getFieldset($fieldset, $root);
        $extraCheckoutFields = [];
        foreach ($fields as $field => $fieldInfo) {
            $extraCheckoutFields[] = $field;
        }
        return $extraCheckoutFields;
    }

    /**
     * Transport attrbiute to object
     *
     * @return object
     */
    public function transportFieldsFromExtensionAttributesToObject(
        $fromObject,
        $toObject,
        $fieldset = 'extra_checkout_billing_address_fields'
    ) {
        foreach ($this->getExtraCheckoutAddressFields($fieldset) as $extraField) {
            $set = 'set' . str_replace(' ', '', ucwords(str_replace('_', ' ', $extraField)));
            $get = 'get' . str_replace(' ', '', ucwords(str_replace('_', ' ', $extraField)));
            $value = $fromObject->$get();

            if ($extraField == 'township' && empty($value) && $fromObject->getTownshipId()) {
                $collection = $this->townshipCollection->create();
                $collection->addFieldToFilter('township_id', $fromObject->getTownshipId());
                $township = $collection->getFirstItem();
                if ($township) {
                    $value = ($township->getName() != '') ? $township->getName() : $township->getDefaultName();
                }
            }

            try {
                if ($value != null) {
                    $toObject->$set($value);
                }
            } catch (\Exception $e) {
                $this->_logger->critical($e->getMessage());
            }
        }
        return $toObject;
    }

    /**
     * Retrieve city data option array
     *
     * @return array
     */
    public function getCityDataProvider()
    {
        return $this->cityCollection->create()->toOptionArray();
    }

    /**
     * Retrieve city data json
     *
     * @return string
     */
    public function getCityJson()
    {
        if (!$this->cityJson) {
            $cacheKey = 'DIRECTORY_CITY_JSON_STORE' . $this->storeManager->getStore()->getId();
            $json = $this->configCacheType->load($cacheKey);
            if (empty($json)) {
                $cities = $this->cityCollection->create()->getCityData();
                $json = $this->jsonHelper->serialize($cities);
                if ($json === false) {
                    $json = 'false';
                }
                $this->configCacheType->save($json, $cacheKey);
            }
            $this->cityJson = $json;
        }
        return $this->cityJson;
    }

    /**
     * Retrieve township option array
     *
     * @return array
     */
    public function getTownshipDataProvider()
    {
        return $this->townshipCollection->create()->toOptionArray();
    }

    /**
     * Retrieve township data json
     *
     * @return string
     */
    public function getTownshipJson()
    {
        if (!$this->townshipJson) {
            $cacheKey = 'DIRECTORY_TOWNSHIP_JSON_STORE' . $this->storeManager->getStore()->getId();
            $json = $this->configCacheType->load($cacheKey);
            if (empty($json)) {
                $townships = $this->townshipCollection->create()->getTownshipData();
                $json = $this->jsonHelper->serialize($townships);
                if ($json === false) {
                    $json = 'false';
                }
                $this->configCacheType->save($json, $cacheKey);
            }
            $this->townshipJson = $json;
        }
        return $this->townshipJson;
    }

    /**
     * Retrieve address data
     *
     * @return string|bool
     */
    public function getAddressData($addressId, $field)
    {
        try {
            $address = $this->addressFactory->create();
            $this->addressResource->load($address, $addressId);
            if ($address->getId()) {
                return $address->getData($field);
            }
        } catch (\Exception $e) {
        }
        return false;
    }

    /**
     * Retrieve system config value
     *
     * @return string
     */
    public function getConfigValue(
        $configPath,
        $scope = \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT
    ) {
        return $this->scopeConfig->getValue($configPath, $scope);
    }

    /**
     * Get current locale
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->localeResolver->getLocale();
    }
}
