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

namespace Chottvn\SalesRule\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class UpgradeSchema
 *
 * @package Chottvn\SalesRule\Setup
 */
class UpgradeSchema implements UpgradeSchemaInterface
{

    /**
     * {@inheritdoc}
     */
    public function upgrade(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        if (version_compare($context->getVersion(), '0.0.2') < 0) {
            $setup->startSetup();

            $setup->getConnection()->addColumn(
                $setup->getTable('sales_order'),
                'ctt_applied_rule_ids',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => '255',
                    'nullable' => true,
                    'default' => null,
                    'comment' => 'CTT Applied Rule Ids',
                    'after' => 'affiliate_level'
                ]
            );

            $setup->getConnection()->addColumn(
                $setup->getTable('salesrule_coupon'),
                'phone_number',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => '255',
                    'nullable' => true,
                    'default' => null,
                    'comment' => 'Customer Phone Number',
                    'after' => 'generated_by_dotmailer'
                ]
            );

            $setup->getConnection()->addColumn(
                $setup->getTable('salesrule_coupon'),
                'html_item',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => '255',
                    'nullable' => true,
                    'default' => null,
                    'comment' => 'HTML Template',
                    'after' => 'generated_by_dotmailer'
                ]
            );

            $setup->endSetup();
        }

        if (version_compare($context->getVersion(), '0.0.3') < 0) {
            $setup->startSetup();

            // get table customer_entity
            $eavTableSalesOrder = $setup->getTable('sales_order');

            // Check if the table already exists
            if ($setup->getConnection()->isTableExists($eavTableSalesOrder) == true) {
                $connection = $setup->getConnection();
                $connection->dropColumn($eavTableSalesOrder, 'ctt_applied_rule_ids');
            }

            // add column chottvn_applied_rule_ids
            $setup->getConnection()->addColumn(
                $setup->getTable('sales_order'),
                'chottvn_applied_rule_ids',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => '255',
                    'nullable' => true,
                    'default' => null,
                    'comment' => 'CTT Applied Rule Ids'
                ]
            );

            // add column customer_discount_code
            $setup->getConnection()->addColumn(
                $setup->getTable('sales_order'),
                'customer_discount_code',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => '255',
                    'nullable' => true,
                    'default' => null,
                    'comment' => 'Voucher or Coupon Code Send To Customer'
                ]
            );

            // add column affilate_transaction_id
            $setup->getConnection()->addColumn(
                $setup->getTable('sales_order'),
                'affilate_transaction_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => '255',
                    'nullable' => true,
                    'default' => null,
                    'comment' => 'Id transaction for Affiliate'
                ]
            );

            // add column chottvn_applied_discount_code_rule_id
            $setup->getConnection()->addColumn(
                $setup->getTable('sales_order'),
                'chottvn_applied_discount_code_rule_id',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => '255',
                    'nullable' => true,
                    'default' => null,
                    'comment' => 'ID Rule for Voucher or Coupon'
                ]
            );

            // create table chottvn_salesrule store information with SalesRule table
            $chottvn_salesrule = $setup->getTable('chottvn_salesrule');
            if ($setup->getConnection()->isTableExists($chottvn_salesrule) != true) {
                $table_chottvn_salesrule = $setup->getConnection()
                ->newTable($setup->getTable('chottvn_salesrule'))
                ->addColumn(
                    'entity_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Entity ID'
                )->addColumn(
                    'salesrule_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'default' => -1],
                    'SalesRule ID'
                )->addColumn(
                    'is_hide_catalog_product_detail',
                    \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                    null,
                    ['nullable' => false, 'default' => 0],
                    'Hide Rule in Catalog Product Detail'
                )->addColumn(
                    'is_hide_checkout',
                    \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                    null,
                    ['nullable' => true, 'default' => 0],
                    'Hide Rule in Checkout Page'
                );

                $setup->getConnection()->createTable($table_chottvn_salesrule);
            }
            

            $setup->endSetup();
        }

        if (version_compare($context->getVersion(), '0.0.4') < 0) {
            $setup->startSetup();

            // get table salesrule_coupon
            $eavTableSalesRuleCoupon = $setup->getTable('salesrule_coupon');

            // Check if the table already exists
            if ($setup->getConnection()->isTableExists($eavTableSalesRuleCoupon) == true) {
                $connection = $setup->getConnection();
                $connection->dropColumn($eavTableSalesRuleCoupon, 'html_item');
                $connection->dropColumn($eavTableSalesRuleCoupon, 'phone_number');
            }

            // create table chottvn_salesrule_coupon store information with SalesRule Coupon table
            $chottvn_salesrule_coupon = $setup->getTable('chottvn_salesrule_coupon');
            if ($setup->getConnection()->isTableExists($chottvn_salesrule_coupon) != true) {
                $table_chottvn_salesrule_coupon = $setup->getConnection()
                ->newTable($setup->getTable('chottvn_salesrule_coupon'))
                ->addColumn(
                    'entity_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Entity ID'
                )->addColumn(
                    'salesrule_coupon_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'default' => -1],
                    'SalesRule Coupon ID'
                )->addColumn(
                    'phone_number',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    '255',
                    ['length' => '255','nullable' => false, 'default' => ''],
                    'Customer Phone number'
                )->addColumn(
                    'email',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    '255',
                    ['length' => '255','nullable' => false, 'default' => ''],
                    'Customer Email'
                )->addColumn(
                    'html_item',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    '255',
                    ['nullable' => false, 'default' => ''],
                    'Template HTML Coupon'
                )->addColumn(
                    'status',
                    \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                    null,
                    ['nullable' => false, 'default' => 1],
                    'Show/Hide Coupon On FrontEnd'
                );

                $setup->getConnection()->createTable($table_chottvn_salesrule_coupon);
            }
            

            $setup->endSetup();
        }

        if (version_compare($context->getVersion(), '0.0.5') < 0) {
            $setup->startSetup();

            $setup->getConnection()->addColumn(
                $setup->getTable('chottvn_salesrule'),
                'is_show_promo_url',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => '255',
                    'nullable' => true,
                    'default' => null,
                    'comment' => 'Show Hide Promo Url'
                ]
            );

            $setup->getConnection()->addColumn(
                $setup->getTable('chottvn_salesrule'),
                'promo_url',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => '255',
                    'nullable' => true,
                    'default' => null,
                    'comment' => 'Promo Url'
                ]
            );

            $setup->getConnection()->addColumn(
                $setup->getTable('chottvn_salesrule'),
                'promo_condition_description',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'default' => null,
                    'comment' => 'Promo Condition Description'
                ]
            );

            $setup->endSetup();
        }

        if (version_compare($context->getVersion(), '0.0.6') < 0) {
            $setup->startSetup();

            $setup->getConnection()->addColumn(
                $setup->getTable('chottvn_salesrule'),
                'promo_condition_image',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => '1000',
                    'nullable' => true,
                    'default' => null,
                    'comment' => 'Promo Condition Image'
                ]
            );            

            $setup->endSetup();
        }

        if (version_compare($context->getVersion(), '0.0.7') < 0) {
            $setup->startSetup();

            $setup->getConnection()->changeColumn(
                $setup->getTable('sales_order'),
                'affilate_transaction_id',
                'affiliate_transaction_ids',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => '255',
                    'nullable' => true,
                    'default' => null,
                    'comment' => 'Finance Transaction Ids'
                ]
            );          

            $setup->endSetup();
        }
        if (version_compare($context->getVersion(), '0.0.9') < 0) {
            $setup->startSetup();

            $setup->getConnection()->changeColumn(
                $setup->getTable('chottvn_salesrule_coupon'),
                'status',
                'status',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,                   
                    'nullable' => true,
                    'default' => 1,
                    'comment' => 'Status: 0 - hide, 1 - new, 2 - used, 3 - expired, 4 - revoked'
                ]
            );          

            $setup->endSetup();
        }        
    }
}

