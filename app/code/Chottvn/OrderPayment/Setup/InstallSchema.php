<?php
/**
 * Copyright (c) 2019 2020 ChottVN
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

namespace Chottvn\OrderPayment\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\InstallSchemaInterface;
use Chottvn\OrderPayment\Api\Data\BankAccountInterface;

/**
 * Class InstallSchema
 *
 * @package Chottvn\OrderPayment\Setup
 */
class InstallSchema implements InstallSchemaInterface
{
	const TABLE_BANK_ACCOUNT = 'chottvn_orderpayment_bankaccount';

    /**
     * {@inheritdoc}
     */
    public function install(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
    	$installer = $setup;
        // Start Setup
        $installer->startSetup();

        // 1. Create Table
        //setupBankAccount($installer);

        // End Setup
        $installer->endSetup();

    }

    public function setupBankAccount($installer){
        if (!$installer->tableExists(self::TABLE_BANK_ACCOUNT)) {
            $table = $installer->getConnection()
                ->newTable($installer->getTable(self::TABLE_BANK_ACCOUNT))
                ->addColumn(
                    BankAccountInterface::BANKACCOUNT_ID,
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'nullable' => false,
                        'primary'  => true,
                        'unsigned' => true
                    ],
                    'BankAccount ID'
                )
                ->addColumn(BankAccountInterface::BANK_NAME, Table::TYPE_TEXT, 255, ['nullable => false'], 'Bank Name')
                ->addColumn(BankAccountInterface::BANK_BRANCH, Table::TYPE_TEXT, 255, ['nullable => false'], 'Bank Branch')
                ->addColumn(BankAccountInterface::BANK_IMAGE, Table::TYPE_TEXT, 500, ['nullable => true'], 'Bank Image')
                ->addColumn(BankAccountInterface::ACCOUNT_OWNER, Table::TYPE_TEXT, 255, ['nullable => false'], 'Account Owner')
                ->addColumn(BankAccountInterface::ACCOUNT_NUMBER, Table::TYPE_TEXT, 255, ['nullable => false'], 'Account Number')

                ->addColumn(BankAccountInterface::STATUS, Table::TYPE_SMALLINT, null, ['nullable' => false, 'default' => '1'], 'Status')
                ->addColumn(BankAccountInterface::NOTE, Table::TYPE_TEXT, '64k', [], 'Note html')
                ->addColumn(BankAccountInterface::ORDER, Table::TYPE_SMALLINT, null, ['nullable' => false, 'default' => '1'], 'Order')
                ->addColumn(BankAccountInterface::CREATED_AT, Table::TYPE_TIMESTAMP, null, [], 'Created At')
                ->addColumn(BankAccountInterface::UPDATED_AT, Table::TYPE_TIMESTAMP, null, [], 'Updated At')                
                
                ->setComment('BankAccount Table');
            $installer->getConnection()->createTable($table);

            /*$installer->getConnection()->addIndex(
                $installer->getTable(self::TABLE_BANK_ACCOUNT),
                $setup->getIdxName(
                        $installer->getTable(self::TABLE_BANK_ACCOUNT),
                        ['col1', 'col2', 'col3'],
                        AdapterInterface::INDEX_TYPE_FULLTEXT
                    ),
                 ['col1', 'col2', 'col3'],
                AdapterInterface::INDEX_TYPE_FULLTEXT
            );*/            
        }
    }
}

