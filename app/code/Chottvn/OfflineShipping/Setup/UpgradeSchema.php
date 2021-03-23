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

namespace Chottvn\OfflineShipping\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

/**
 * Class UpgradeSchema
 *
 * @package Chottvn\OfflineShipping\Setup
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
                $connection->addColumn($installer->getTable('sales_order'), 'fee_shipping_contact', [
                    'type' => Table::TYPE_BOOLEAN,
                    'nullable' => true,
                    'comment' => 'Fee Shipping Contact',
                ]);
            }
        }
        if (version_compare($context->getVersion(), '1.0.2', '<')) {
            if ($installer->tableExists('shipping_tablerate')) {
                $connection->addColumn($installer->getTable('shipping_tablerate'), 'max_delivery_dates', [
                    'type' => Table::TYPE_INTEGER,
                    'nullable' => true,
                    'comment' => 'Max delivery dates',
                ]);
            }
        }
        if (version_compare($context->getVersion(), '1.0.3', '<')) {
            if ($installer->tableExists('shipping_tablerate')) {
                $connection->addColumn($installer->getTable('shipping_tablerate'), 'min_delivery_dates', [
                    'type' => Table::TYPE_INTEGER,
                    'nullable' => true,
                    'comment' => 'Min delivery dates',
                ]);
            }
        }
        
        
        $installer->endSetup();
    }
}

