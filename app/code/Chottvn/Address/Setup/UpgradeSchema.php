<?php

namespace Chottvn\Address\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '0.1.1') < 0) {
            $setup->startSetup();

            $setup->getConnection()->addColumn(
                $setup->getTable('customer_address_entity'),
                'email',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => '255',
                    'nullable' => true,
                    'default' => null,
                    'comment' => 'Email',
                    'after' => 'telephone'
                ]
            );

            $setup->endSetup();
        }
        if (version_compare($context->getVersion(), '0.1.2') < 0) {
            $setup->startSetup();

            $setup->getConnection()->addColumn(
                $setup->getTable('sales_order'),
                'original_total',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length' => '20,4',
                    'nullable' => true,
                    'default' => null,
                    'comment' => 'Original Total',
                    'after' => 'base_subtotal'
                ]
            );

            $setup->getConnection()->addColumn(
                $setup->getTable('quote'),
                'original_total',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length' => '20,4',
                    'nullable' => true,
                    'default' => null,
                    'comment' => 'Original Total',
                    'after' => 'base_subtotal'
                ]
            );

            $setup->getConnection()->addColumn(
                $setup->getTable('sales_order'),
                'base_original_total',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length' => '20,4',
                    'nullable' => true,
                    'default' => null,
                    'comment' => 'Base Original Total',
                    'after' => 'base_subtotal'
                ]
            );

            $setup->getConnection()->addColumn(
                $setup->getTable('quote'),
                'base_original_total',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length' => '20,4',
                    'nullable' => true,
                    'default' => null,
                    'comment' => 'Base Original Total',
                    'after' => 'base_subtotal'
                ]
            );

            $setup->endSetup();
        }
    }
}