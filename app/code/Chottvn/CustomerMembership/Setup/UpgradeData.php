<?php
/**
 * Copyright (c) 2019 ChottVN
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace Chottvn\CustomerMembership\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Customer\Api\CustomerRepositoryInterface as CustomerRepository;
use Magento\Customer\Model\CustomerFactory;

/**
 * Class UpgradeData
 *
 * @package Chottvn\CustomerMembership\Setup
 */
class UpgradeData implements UpgradeDataInterface
{
    private $customerSetupFactory;
    //private $customerRepository;
    private  $customerFactory;

    public function __construct(
        CustomerSetupFactory $customerSetupFactory,
        //CustomerRepository $customerRepository,
        CustomerFactory $customerFactory
    )
    {
        $this->customerSetupFactory = $customerSetupFactory;
        //$this->$customerRepository = $customerRepository;
        $this->customerFactory = $customerFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context        
    ) {
        $setup->startSetup();
        if (version_compare($context->getVersion(), "0.0.8", "<=")) {
            // Set Attribute Default Value
            $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);
            $customerSetup->updateAttribute(Customer::ENTITY, 'customer_level', 'is_required', 0);
            $customerSetup->updateAttribute(Customer::ENTITY, 'customer_level', 'default_value', "member");
            // Set Default Value for Legacy Customer
            $customers = $this->customerFactory->create()->getCollection()
                ->addAttributeToSelect("*")
                ->addAttributeToFilter("customer_level", array('null' => true))
                ->load();           
            foreach ($customers as $customer) {
                try {
                    $customer->setCustomerLevel('member');
                    $customer->save();
                }
                catch(Exception $e) {
                    echo 'Error: ' .$e->getMessage();
                }
            }
            
        }

        $setup->endSetup();
    }
}

