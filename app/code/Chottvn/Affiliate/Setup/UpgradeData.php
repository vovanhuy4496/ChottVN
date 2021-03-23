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

namespace Chottvn\Affiliate\Setup;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Customer\Api\CustomerRepositoryInterface as CustomerRepository;
use Magento\Customer\Model\CustomerFactory;

/**
 * Class UpgradeData
 *
 * @package Chottvn\Affiliate\Setup
 */
class UpgradeData implements UpgradeDataInterface
{

    private  $customerFactory;
    private $eavSetupFactory;
    private $customerSetupFactory;

    public function __construct(
        EavSetupFactory $eavSetupFactory,
        CustomerSetupFactory $customerSetupFactory,
        CustomerFactory $customerFactory
    ){
        $this->eavSetupFactory = $eavSetupFactory;
        $this->customerSetupFactory = $customerSetupFactory;
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

        if (version_compare($context->getVersion(), "0.1.1", "<")) {
            $setup->getConnection()->addColumn(
                $setup->getTable('customer_entity'),
                'affiliate_code',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => true,
                    'default' => '',
                    'comment' => 'Affiliate Code'
                ]
            );

            $setup->getConnection()->addColumn(
                $setup->getTable('customer_entity'),
                'affiliate_status',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => true,
                    'default' => '',
                    'comment' => 'Affiliate Status'
                ]
            );
        }

        if (version_compare($context->getVersion(), "0.1.3", "<")) {
            // get table customer_entity
            $eavTable = $setup->getTable('customer_entity');

            // Check if the table already exists
            if ($setup->getConnection()->isTableExists($eavTable) == true) {
                $connection = $setup->getConnection();

                // del_flg = column name which you want to delete
                $connection->dropColumn($eavTable, 'affiliate_code');
                $connection->dropColumn($eavTable, 'affiliate_status');
            }


            /** @var CustomerSetupFactory $customerSetup **/
            $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);
            $customerSetup->addAttribute(
                Customer::ENTITY,
                'affiliate_code',
                [
                    'label' => 'Affiliate Code',
                    'input' => 'text',
                    'length' => 255,
                    'required' => false,
                    'sort_order' => 901,
                    'visible' => true,
                    'system' => false,
                    'is_used_in_grid' => false,
                    'is_visible_in_grid' => true,
                    'is_filterable_in_grid' => true,
                    'is_searchable_in_grid' => true
                ]
            );

            $customerSetup->addAttribute(
                Customer::ENTITY,
                'affiliate_status',
                [
                    'label' => 'Affiliate Status',
                    'input' => 'text',
                    'length' => 255,
                    'required' => false,
                    'sort_order' => 902,
                    'visible' => true,
                    'system' => false,
                    'is_used_in_grid' => false,
                    'is_visible_in_grid' => true,
                    'is_filterable_in_grid' => true,
                    'is_searchable_in_grid' => true
                ]
            );
        }

        if (version_compare($context->getVersion(), "0.1.5", "<")) {

            /** @var CustomerSetupFactory $customerSetup **/
            $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);
            $customerSetup->addAttribute(
                Customer::ENTITY,
                'customer_email',
                [
                    'label' => 'Customer Email',
                    'input' => 'text',
                    'length' => 255,
                    'required' => false,
                    'sort_order' => 901,
                    'visible' => true,
                    'system' => false,
                    'is_used_in_grid' => false,
                    'is_visible_in_grid' => true,
                    'is_filterable_in_grid' => true,
                    'is_searchable_in_grid' => true
                ]
            );
        }
        // if (version_compare($context->getVersion(), "0.1.4", "<=")) {
        //     // Set Attribute Default Value
        //     $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);
        //     $customerSetup->updateAttribute(Customer::ENTITY, 'affiliate_level', 'is_required', 0);
        //     $customerSetup->updateAttribute(Customer::ENTITY, 'affiliate_level', 'default_value', "ctv");
        //     // Set Default Value for Legacy Customer
        //     $customers = $this->customerFactory->create()->getCollection()
        //         ->addAttributeToSelect("*")
        //         ->addAttributeToFilter("affiliate_level", array('null' => true))
        //         ->load();           
        //     foreach ($customers as $customer) {
        //         try {
        //             $customer->setCustomerLevel('ctv');
        //             $customer->save();
        //         }
        //         catch(Exception $e) {
        //             echo 'Error: ' .$e->getMessage();
        //         }
        //     }
            
        // }

        $setup->endSetup();
    }
}

