<?php

namespace Chottvn\Address\Observer\Adminhtml;

use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Chottvn\Address\Helper\Data;

class UpdateDefaultCountry implements ObserverInterface
{
    /**
     * @var \Magento\Framework\App\Config\ReinitableConfigInterface
     */
    private $appConfig;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    private $scopeConfig;

    /**
     * @var \Magento\Framework\App\Config\ConfigResource\ConfigInterface
     */
    private $configInterface;

    /**
     * @param \Magento\Framework\App\Config\ReinitableConfigInterface $appConfig
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\App\Config\ConfigResource\ConfigInterface $configInterface
     */
    public function __construct(
        \Magento\Framework\App\Config\ReinitableConfigInterface $appConfig,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\Config\ConfigResource\ConfigInterface $configInterface
    ) {
        $this->appConfig = $appConfig;
        $this->scopeConfig = $scopeConfig;
        $this->configInterface = $configInterface;
    }

    /**
     * Set default country
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $website = $observer->getEvent()->getWebsite();
        $scope = ScopeInterface::SCOPE_WEBSITE;
        if (!$website) {
            $website = 0;
            $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT;
        }
        $disableCountry = $this->scopeConfig->getValue(Data::XML_CONFIG_PATH_HIDE_COUNTRY, $scope);
        $defaultCountry = $this->scopeConfig->getValue(Data::XML_CONFIG_PATH_DEFAULT_COUNTRY, $scope);
        if ($disableCountry && $defaultCountry) {
            $this->configInterface->saveConfig('general/country/allow', $defaultCountry, $scope, $website);
            $this->configInterface->saveConfig('general/country/default', $defaultCountry, $scope, $website);
            $this->appConfig->reinit();
        }
    }
}
