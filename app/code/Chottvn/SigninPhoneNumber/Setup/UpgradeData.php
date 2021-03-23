<?php

namespace Chottvn\SigninPhoneNumber\Setup;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Address;
use Magento\Customer\Setup\CustomerSetupFactory;

class UpgradeData implements \Magento\Framework\Setup\UpgradeDataInterface
{
    private $customerSetupFactory;
    private $addressSetupFactory;
    
    /**
     * @var string Customer Phone Number attribute.
     */
    const PHONE_NUMBER = 'phone_number';

    public function __construct(
        CustomerSetupFactory $customerSetupFactory
    )
    {
        $this->customerSetupFactory = $customerSetupFactory;
    }

    public function upgrade(ModuleDataSetupInterface $setup,
                            ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '0.1.2', '<')) {
            // Customer - unrequired email
            $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);
            $customerSetup->updateAttribute(Customer::ENTITY, 'email', 'is_required', 0);
        }

        if (version_compare($context->getVersion(), '0.1.3', '<')) {
            // Customer - unrequired lastname
            $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);
            $customerSetup->updateAttribute(Customer::ENTITY, 'lastname', 'is_required', 0);
            $customerSetup->updateAttribute("customer_address", 'lastname', 'is_required', 0);
            
        }

        if (version_compare($context->getVersion(), '0.1.6', '<')) {
            // Customer - phone_number -> not unique
            $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);
            $attribute = $customerSetup->getEavConfig()->getAttribute(
                Customer::ENTITY,
                self::PHONE_NUMBER
            );
            $attribute->setData('is_unique', false);
            $attribute->save();
            
        }

        $setup->endSetup();
    }
}