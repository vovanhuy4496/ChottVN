<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_Blog
 * @copyright   Copyright (c) 2018 Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Chottvn\Sales\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

/**
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        $connection = $installer->getConnection();
        if (version_compare($context->getVersion(), '1.0.1', '<')) {
            if ($installer->tableExists('sales_order')) {
                $connection->addColumn($installer->getTable('sales_order'), 'chott_customer_phone_number', [
                    'type' => Table::TYPE_TEXT,
                    'length' => '255',
                    'nullable' => true,
                    'comment' => 'Customer Phone Number',
                ]);
            }
        }
        if (version_compare($context->getVersion(), '1.0.2', '<')) {
            if ($installer->tableExists('sales_order_item')) {
                $connection->addColumn($installer->getTable('sales_order_item'), 'guarantee', [
                    'type' => Table::TYPE_TEXT,
                    'length' => '255',
                    'nullable' => true,
                    'comment' => 'Guarantee Order',
                ]);
            }
        }
        if (version_compare($context->getVersion(), '1.0.3', '<')) {
            if ($installer->tableExists('sales_order')) {
                $connection->addColumn($installer->getTable('sales_order'), 'max_delivery_dates', [
                    'type' => Table::TYPE_INTEGER,
                    'length' => '11',
                    'nullable' => true,
                    'comment' => 'Max Delivery Dates',
                ]);
            }
        }

        if (version_compare($context->getVersion(), '1.0.4', '<')) {
            if ($installer->tableExists('sales_order_item')) {
                $connection->addColumn($installer->getTable('sales_order_item'), 'product_name_short', [
                    'type' => Table::TYPE_TEXT,
                    'length' => '255',
                    'nullable' => true,
                    'comment' => 'Product Short Name',
                ]);
            }
        }

        if (version_compare($context->getVersion(), '1.0.5', '<')) {
            if ($installer->tableExists('sales_order')) {
                $connection->addColumn($installer->getTable('sales_order'), 'base_savings_amount', [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length' => '20,4',
                    'nullable' => true,
                    'default' => null,
                    'comment' => 'Base Savings Amount',
                    'after' => 'base_subtotal'
                ]);
            }
            if ($installer->tableExists('sales_order')) {
                $connection->addColumn($installer->getTable('sales_order'), 'savings_amount', [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length' => '20,4',
                    'nullable' => true,
                    'default' => null,
                    'comment' => 'Savings Amount',
                    'after' => 'base_subtotal'
                ]);
            }
            if ($installer->tableExists('quote')) {
                $connection->addColumn($installer->getTable('quote'), 'base_savings_amount', [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length' => '20,4',
                    'nullable' => true,
                    'default' => null,
                    'comment' => 'Base Savings Amount',
                    'after' => 'base_subtotal'
                ]);
            }
            if ($installer->tableExists('quote')) {
                $connection->addColumn($installer->getTable('quote'), 'savings_amount', [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length' => '20,4',
                    'nullable' => true,
                    'default' => null,
                    'comment' => 'Savings Amount',
                    'after' => 'base_subtotal'
                ]);
            }
            
        }
        if (version_compare($context->getVersion(), '1.0.6', '<')) {
            if ($installer->tableExists('quote')) {
                $connection->addColumn($installer->getTable('quote'), 'flag', [
                    'type' => Table::TYPE_TEXT,
                    'length' => '255',
                    'nullable' => true,
                    'comment' => 'Flag Free Shipping',
                ]);
            }
            if ($installer->tableExists('sales_order')) {
                $connection->addColumn($installer->getTable('sales_order'), 'flag', [
                    'type' => Table::TYPE_TEXT,
                    'length' => '255',
                    'nullable' => true,
                    'comment' => 'Flag Free Shipping',
                ]);
            }
        }
        if (version_compare($context->getVersion(), '1.0.7', '<')) {
            if ($installer->tableExists('sales_order')) {
                $connection->addColumn($installer->getTable('sales_order'), 'affiliate_account_code', [
                    'type' => Table::TYPE_TEXT,
                    'length' => '255',
                    'nullable' => true,
                    'comment' => 'Affiliate Account Code',
                ]);
            }
            if ($installer->tableExists('sales_order')) {
                $connection->addColumn($installer->getTable('sales_order'), 'affiliate_account_id', [
                    'type' => Table::TYPE_TEXT,
                    'length' => '255',
                    'nullable' => true,
                    'comment' => 'Affiliate Account Id',
                ]);
            }
        }
        if (version_compare($context->getVersion(), '1.0.8', '<')) {
            if ($installer->tableExists('sales_order')) {
                $connection->changeColumn($installer->getTable('sales_order'), 'affiliate_account_id', 'affiliate_account_id', [
                    'type' => Table::TYPE_INTEGER,
                    'length' => '10',
                    'nullable' => true,
                    'comment' => 'Affiliate Account Id',
                ]);
            }
        }
        if (version_compare($context->getVersion(), '1.0.9', '<')) {
            if ($installer->tableExists('quote')) {
                $connection->changeColumn($installer->getTable('quote'), 'flag', 'flag_shipping', [
                    'type' => Table::TYPE_TEXT,
                    'length' => '255',
                    'nullable' => true,
                    'comment' => 'Flag Shipping',
                ]);
            }
            if ($installer->tableExists('sales_order')) {
                $connection->changeColumn($installer->getTable('sales_order'), 'flag', 'flag_shipping', [
                    'type' => Table::TYPE_TEXT,
                    'length' => '255',
                    'nullable' => true,
                    'comment' => 'Flag Shipping',
                ]);
            }

        }
        if (version_compare($context->getVersion(), '1.1.0', '<')) {
            if ($installer->tableExists('quote')) {
                $connection->addColumn($installer->getTable('quote'), 'max_delivery_dates', [
                    'type' => Table::TYPE_INTEGER,
                    'length' => '11',
                    'nullable' => true,
                    'comment' => 'Max Delivery Dates',
                ]);
            }
        }
        if (version_compare($context->getVersion(), '1.1.1', '<')) {
            if ($installer->tableExists('sales_order_item')) {
                $connection->addColumn($installer->getTable('sales_order_item'), 'model', [
                    'type' => Table::TYPE_TEXT,
                    'length' => '255',
                    'nullable' => true,
                    'comment' => 'Product Model',
                ]);
            }
            if ($installer->tableExists('sales_order_item')) {
                $connection->addColumn($installer->getTable('sales_order_item'), 'product_unit', [
                    'type' => Table::TYPE_TEXT,
                    'length' => '255',
                    'nullable' => true,
                    'comment' => 'Product Unit',
                ]); 
            }
            if ($installer->tableExists('sales_order_item')) {
                $connection->addColumn($installer->getTable('sales_order_item'), 'product_brand_id', [
                    'type' => Table::TYPE_INTEGER,
                    'length' => '10',
                    'nullable' => true,
                    'comment' => 'Product Brand Id',
                ]);
            }
        }
        if (version_compare($context->getVersion(), '1.1.2', '<')) {
            if ($installer->tableExists('sales_order_item')) {
                $connection->addColumn($installer->getTable('sales_order_item'), 'product_kind', [
                    'type' => Table::TYPE_INTEGER,
                    'length' => '10',
                    'nullable' => true,
                    'comment' => 'Product Kind',
                ]);
                $connection->addColumn($installer->getTable('sales_order_item'), 'customer_level', [
                    'type' => Table::TYPE_TEXT,
                    'length' => '255',
                    'nullable' => true,
                    'comment' => 'Customer Level',
                ]);
                $connection->addColumn($installer->getTable('sales_order_item'), 'affiliate_level', [
                    'type' => Table::TYPE_TEXT,
                    'length' => '255',
                    'nullable' => true,
                    'comment' => 'Affiliate Level',
                ]);
                $connection->addColumn($installer->getTable('sales_order_item'), 'chottvn_rma_rule_id', [
                    'type' => Table::TYPE_BIGINT,
                    'nullable' => true,
                    'comment' => 'Chottvn Rma Rule Id',
                ]);
                $connection->addColumn($installer->getTable('sales_order_item'), 'chottvn_affiliate_reward_rule_ids', [
                    'type' => Table::TYPE_BIGINT,
                    'nullable' => true,
                    'comment' => 'Chottvn Affiliate Reward Rule Ids',
                ]);
                $connection->addColumn($installer->getTable('sales_order_item'), 'affiliate_amount', [
                    'type' => Table::TYPE_DECIMAL,
                    'length' => '20,4',
                    'nullable' => true,
                    'comment' => 'Affiliate Amount',
                ]);
                $connection->addColumn($installer->getTable('sales_order_item'), 'base_affiliate_amount', [
                    'type' => Table::TYPE_DECIMAL,
                    'length' => '20,4',
                    'nullable' => true,
                    'comment' => 'Base Affiliate Amount',
                ]);
            }
        }
        if (version_compare($context->getVersion(), '1.1.3', '<')) {
            if ($installer->tableExists('sales_order_item')) {
                $connection->changeColumn($installer->getTable('sales_order_item'), 'chottvn_affiliate_reward_rule_ids', 'chottvn_affiliate_reward_rule_ids', [
                    'type' => Table::TYPE_TEXT,
                    'nullable' => true,
                    'comment' => 'Chottvn Affiliate Reward Rule Ids',
                ]);
            }
        }
        if (version_compare($context->getVersion(), '1.1.4', '<')) {
            if ($installer->tableExists('sales_order_item')) {
                $connection->addColumn($installer->getTable('sales_order_item'), 'return_period', [
                    'type' => Table::TYPE_INTEGER,
                    'length' => '10',
                    'nullable' => true,
                    'comment' => 'Return Period',
                ]);
            }
        }
        if (version_compare($context->getVersion(), '1.1.5', '<')) {
            if ($installer->tableExists('sales_order_item')) {
                $connection->addColumn($installer->getTable('sales_order_item'), 'affiliate_amount_item', [
                    'type' => Table::TYPE_DECIMAL,
                    'length' => '20,4',
                    'nullable' => true,
                    'comment' => 'Affiliate Amount per Item',
                ]);
                $connection->addColumn($installer->getTable('sales_order_item'), 'base_affiliate_amount_item', [
                    'type' => Table::TYPE_DECIMAL,
                    'length' => '20,4',
                    'nullable' => true,
                    'comment' => 'Base Affiliate Amount per Item',
                ]);
            }
        }
        if (version_compare($context->getVersion(), '1.1.6', '<')) {
            if ($installer->tableExists('sales_order')) {
                $connection->addColumn($installer->getTable('sales_order'), 'affiliate_level', [
                    'type' => Table::TYPE_TEXT,
                    'length' => '255',
                    'nullable' => true,
                    'comment' => 'Affiliate Level',
                ]);
            }
        }
        if (version_compare($context->getVersion(), '1.1.7', '<')) {
            if ($installer->tableExists('sales_order_item')) {
                $connection->addColumn($installer->getTable('sales_order_item'), 'sku_gift', [
                    'type' => Table::TYPE_TEXT,
                    'length' => '255',
                    'nullable' => true,
                    'comment' => 'Sku Gift',
                ]);
                $connection->addColumn($installer->getTable('sales_order_item'), 'qty_gift', [
                    'type' => Table::TYPE_INTEGER,
                    'length' => '10',
                    'nullable' => true,
                    'comment' => 'Qty Gift',
                ]);
            }
            if ($installer->tableExists('quote_item')) {
                $connection->addColumn($installer->getTable('quote_item'), 'sku_gift', [
                    'type' => Table::TYPE_TEXT,
                    'length' => '255',
                    'nullable' => true,
                    'comment' => 'Sku gift',
                ]);
                $connection->addColumn($installer->getTable('quote_item'), 'qty_gift', [
                    'type' => Table::TYPE_INTEGER,
                    'length' => '10',
                    'nullable' => true,
                    'comment' => 'Qty Gift',
                ]);
            }
        }
        if (version_compare($context->getVersion(), "1.1.8", "<")) {

            $eavTable_1 = $installer->getTable('sales_order_item');
            $eavTable_2 = $installer->getTable('quote_item');

            // Check if the table already exists
            if ($installer->getConnection()->isTableExists($eavTable_1) == true) {
                $connection = $installer->getConnection();

                $connection->dropColumn($eavTable_1, 'sku_gift');
                $connection->dropColumn($eavTable_1, 'qty_gift');
            }
            // Check if the table already exists
            if ($installer->getConnection()->isTableExists($eavTable_2) == true) {
                $connection = $installer->getConnection();
                $connection->dropColumn($eavTable_2, 'sku_gift');
                $connection->dropColumn($eavTable_2, 'qty_gift');
            }
        }

        if (version_compare($context->getVersion(), '1.1.9', '<')) {
            if ($installer->tableExists('sales_order_item')) {
                $connection->addColumn($installer->getTable('sales_order_item'), 'cart_promo_option', [
                    'type' => Table::TYPE_TEXT,
                    'length' => '255',
                    'nullable' => true,
                    'comment' => 'Cart promo option',
                ]);
                $connection->addColumn($installer->getTable('sales_order_item'), 'cart_promo_ids', [
                    'type' => Table::TYPE_TEXT,
                    'length' => '255',
                    'nullable' => true,
                    'comment' => 'Cart promo ids',
                ]);
            }
            if ($installer->tableExists('quote_item')) {
                $connection->addColumn($installer->getTable('quote_item'), 'cart_promo_option', [
                    'type' => Table::TYPE_TEXT,
                    'length' => '255',
                    'nullable' => true,
                    'comment' => 'Cart promo option',
                ]);
                $connection->addColumn($installer->getTable('quote_item'), 'cart_promo_ids', [
                    'type' => Table::TYPE_TEXT,
                    'length' => '255',
                    'nullable' => true,
                    'comment' => 'Cart promo ids',
                ]);
            }
        }

        if (version_compare($context->getVersion(), '1.2.1', '<')) {
            if ($installer->tableExists('sales_order_item')) {
                $connection->addColumn($installer->getTable('sales_order_item'), 'cart_promo_parent_id', [
                    'type' => Table::TYPE_TEXT,
                    'length' => '255',
                    'nullable' => true,
                    'comment' => 'Cart promo parent id',
                ]);
            }
            if ($installer->tableExists('sales_order_item')) {
                $connection->addColumn($installer->getTable('sales_order_item'), 'cart_promo_item_ids', [
                    'type' => Table::TYPE_TEXT,
                    'length' => '255',
                    'nullable' => true,
                    'comment' => 'Cart promo item ids',
                ]);
            }
            if ($installer->tableExists('sales_order_item')) {
                $connection->addColumn($installer->getTable('sales_order_item'), 'cart_promo_qty', [
                    'type' => Table::TYPE_TEXT,
                    'length' => '255',
                    'nullable' => true,
                    'comment' => 'Cart promo qty',
                ]);
            }
            if ($installer->tableExists('quote_item')) {
                $connection->addColumn($installer->getTable('quote_item'), 'cart_promo_parent_id', [
                    'type' => Table::TYPE_TEXT,
                    'length' => '255',
                    'nullable' => true,
                    'comment' => 'Cart promo parent id',
                ]);
            }
            if ($installer->tableExists('quote_item')) {
                $connection->addColumn($installer->getTable('quote_item'), 'cart_promo_item_ids', [
                    'type' => Table::TYPE_TEXT,
                    'length' => '255',
                    'nullable' => true,
                    'comment' => 'Cart promo item ids',
                ]);
            }
            if ($installer->tableExists('quote_item')) {
                $connection->addColumn($installer->getTable('quote_item'), 'cart_promo_qty', [
                    'type' => Table::TYPE_TEXT,
                    'length' => '255',
                    'nullable' => true,
                    'comment' => 'Cart promo qty',
                ]);
            }
        }

        if (version_compare($context->getVersion(), '1.2.2', '<')) {
            if ($installer->tableExists('sales_order_item')) {
                $connection->addColumn($installer->getTable('sales_order_item'), 'cart_promo_parent_item_id', [
                    'type' => Table::TYPE_TEXT,
                    'length' => '255',
                    'nullable' => true,
                    'comment' => 'Cart promo parent item id',
                ]);
            }
            if ($installer->tableExists('quote_item')) {
                $connection->addColumn($installer->getTable('quote_item'), 'cart_promo_parent_item_id', [
                    'type' => Table::TYPE_TEXT,
                    'length' => '255',
                    'nullable' => true,
                    'comment' => 'Cart promo parent item id',
                ]);
            }
        }

        $installer->endSetup();
    }
}
