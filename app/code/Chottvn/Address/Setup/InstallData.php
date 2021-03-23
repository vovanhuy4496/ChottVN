<?php

namespace Chottvn\Address\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Model\Config;

class InstallData implements InstallDataInterface
{
    /**
     * @var EavSetup
     */
    private $eavSetup;

    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * InstallData constructor.
     * @param EavSetup $eavSetup
     * @param Config $eavConfig
     */
    public function __construct(
        EavSetup $eavSetup,
        Config $eavConfig
    ) {
        $this->eavSetup = $eavSetup;
        $this->eavConfig = $eavConfig;
    }

    /**
     * Install data
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $attributeConfig = [
            'city_id' => [
                'type' => 'static',
                'label' => 'City',
                'input' => 'hidden',
                'required' => false,
                'visible' => true,
                'user_defined' => false,
                'sort_order' => 107,
                'position' => 107,
                'system' => 1,
                'source' => \Chottvn\Address\Model\ResourceModel\Address\Attribute\Source\City::class
            ],
            'township_id' => [
                'type' => 'static',
                'label' => 'Township',
                'input' => 'hidden',
                'required' => false,
                'visible' => true,
                'user_defined' => false,
                'sort_order' => 109,
                'position' => 109,
                'system' => 1,
                'source' => \Chottvn\Address\Model\ResourceModel\Address\Attribute\Source\Township::class
            ],
            'township' => [
                'type' => 'static',
                'label' => 'Township',
                'input' => 'text',
                'required' => false,
                'visible' => true,
                'user_defined' => false,
                'sort_order' => 109,
                'position' => 109,
                'system' => 1
            ]
        ];

        foreach ($attributeConfig as $code => $config) {
            $this->processAttribute($code, $config);
        }

        $city = $this->eavConfig->getAttribute(
            AddressMetadataInterface::ENTITY_TYPE_ADDRESS,
            'city'
        );
        $city->setData('sort_order', 108);
        $city->save();

        $setup->endSetup();
    }

    /**
     * Process add attribute
     * @param string $attributeCode
     * @param array $config
     * @return void
     */
    private function processAttribute($attributeCode, $config)
    {
        $this->eavSetup->addAttribute(
            AddressMetadataInterface::ENTITY_TYPE_ADDRESS,
            $attributeCode,
            $config
        );
        $attribute = $this->eavConfig->getAttribute(
            AddressMetadataInterface::ENTITY_TYPE_ADDRESS,
            $attributeCode
        );
        $attribute->setData(
            'used_in_forms',
            ['adminhtml_customer_address', 'customer_address_edit', 'customer_register_address']
        );
        $attribute->save();
    }
}
