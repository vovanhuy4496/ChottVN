<?php

namespace Chottvn\Address\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * install tables
     *
     * @param \Magento\Framework\Setup\SchemaSetupInterface $setup
     * @param \Magento\Framework\Setup\ModuleContextInterface $context
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $this->_addCityTables($setup);
        $this->_addTownshipTables($setup);
        $this->_updateRegionTables($setup);

        $connection = $setup->getConnection();
        $customer_address = $setup->getTable('customer_address_entity');
        $quote_address = $setup->getTable('quote_address');
        $order_address = $setup->getTable('sales_order_address');
        $columns = [
            'city_id' => [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'unsigned' => true,
                'nullable' => true,
                'comment' => 'City'
            ],
            'township' => [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'comment' => 'Township'
            ],
            'township_id' => [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                'unsigned' => true,
                'nullable' => true,
                'comment' => 'Township'
            ]
        ];
        if ($connection->isTableExists($customer_address) == true) {
            foreach ($columns as $name => $definition) {
                $connection->addColumn($customer_address, $name, $definition);
            }
        }
        if ($connection->isTableExists($quote_address) == true) {
            foreach ($columns as $name => $definition) {
                $connection->addColumn($quote_address, $name, $definition);
            }
        }
        if ($connection->isTableExists($order_address) == true) {
            foreach ($columns as $name => $definition) {
                $connection->addColumn($order_address, $name, $definition);
            }
        }
        $setup->endSetup();
    }

    /**
     * Add city tables
     * @param SchemaSetupInterface $setup
     * @return void
     */
    private function _addCityTables(SchemaSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $regionTable = $setup->getTable('directory_country_region');
        $cityTable = $setup->getTable('directory_region_city');
        $cityNameTable = $setup->getTable('directory_region_city_name');

        if ($connection->isTableExists($cityTable) == false) {
            $tableCity = $connection
                ->newTable($cityTable)
                ->addColumn(
                    'city_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'City Id'
                )
                ->addColumn(
                    'region_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false,],
                    'Region Id'
                )
                ->addColumn(
                    'code',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    ['default' => null],
                    'Code'
                )
                ->addColumn(
                    'default_name',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    ['default' => null],
                    'City Name'
                )
                ->addIndex(
                    $setup->getIdxName(
                        $cityTable,
                        ['default_name'],
                        AdapterInterface::INDEX_TYPE_FULLTEXT
                    ),
                    ['default_name'],
                    ['type' => AdapterInterface::INDEX_TYPE_FULLTEXT]
                )
                ->addForeignKey(
                    $setup->getFkName(
                        $cityTable,
                        'region_id',
                        $regionTable,
                        'region_id'
                    ),
                    'region_id',
                    $regionTable,
                    'region_id',
                    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                );
            $connection->createTable($tableCity);
        }
        if ($connection->isTableExists($cityNameTable) == false) {
            $tableCityName = $connection
                ->newTable($cityNameTable)
                ->addColumn(
                    'locale',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    8,
                    ['default' => null],
                    'Locale'
                )
                ->addColumn(
                    'city_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false,],
                    'City Id'
                )
                ->addColumn(
                    'name',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    ['default' => null],
                    'City Name'
                )
                ->addForeignKey(
                    $setup->getFkName($cityNameTable, 'city_id', $cityTable, 'city_id'),
                    'city_id',
                    $cityTable,
                    'city_id',
                    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                )
                ->addIndex(
                    $setup->getIdxName(
                        $cityNameTable,
                        ['city_id', 'locale'],
                        AdapterInterface::INDEX_TYPE_UNIQUE
                    ),
                    ['city_id', 'locale'],
                    ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
                );
            $connection->createTable($tableCityName);
        }
    }

    /**
     * Add township tables
     * @param SchemaSetupInterface $setup
     * @return void
     */
    private function _addTownshipTables(SchemaSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $cityTable = $setup->getTable('directory_region_city');
        $townshipTable = $setup->getTable('directory_city_township');
        $townshipNameTable = $setup->getTable('directory_city_township_name');

        if ($connection->isTableExists($townshipTable) == false) {
            $tableTownship = $connection
                ->newTable($townshipTable)
                ->addColumn(
                    'township_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Township Id'
                )
                ->addColumn(
                    'city_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false,],
                    'City Id'
                )
                ->addColumn(
                    'code',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    ['default' => null],
                    'Code'
                )
                ->addColumn(
                    'default_name',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    ['default' => null],
                    'Township Name'
                )
                ->addColumn(
                    'postcode',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    '64k',
                    ['default' => null],
                    'Postcode'
                )
                ->addIndex(
                    $setup->getIdxName(
                        $townshipTable,
                        ['default_name'],
                        AdapterInterface::INDEX_TYPE_FULLTEXT
                    ),
                    ['default_name'],
                    ['type' => AdapterInterface::INDEX_TYPE_FULLTEXT]
                )
                ->addIndex(
                    $setup->getIdxName(
                        $townshipTable,
                        ['postcode'],
                        AdapterInterface::INDEX_TYPE_FULLTEXT
                    ),
                    ['postcode'],
                    ['type' => AdapterInterface::INDEX_TYPE_FULLTEXT]
                )
                ->addForeignKey(
                    $setup->getFkName(
                        $townshipTable,
                        'city_id',
                        $cityTable,
                        'city_id'
                    ),
                    'city_id',
                    $cityTable,
                    'city_id',
                    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                );
            $connection->createTable($tableTownship);
        }
        if ($connection->isTableExists($townshipNameTable) == false) {
            $tableTownshipName = $connection
                ->newTable($townshipNameTable)
                ->addColumn(
                    'locale',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    8,
                    ['default' => null],
                    'Locale'
                )
                ->addColumn(
                    'township_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false,],
                    'Township Id'
                )
                ->addColumn(
                    'name',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    ['default' => null],
                    'Township Name'
                )
                ->addForeignKey(
                    $setup->getFkName($townshipNameTable, 'township_id', $townshipTable, 'township_id'),
                    'township_id',
                    $townshipTable,
                    'township_id',
                    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                )
                ->addIndex(
                    $setup->getIdxName(
                        $townshipNameTable,
                        ['township_id', 'locale'],
                        AdapterInterface::INDEX_TYPE_UNIQUE
                    ),
                    ['township_id', 'locale'],
                    ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
                );
            $connection->createTable($tableTownshipName);
        }
    }

    /**
     * Update region name tables
     * @param SchemaSetupInterface $setup
     * @return void
     */
    private function _updateRegionTables(SchemaSetupInterface $setup)
    {
        $connection = $setup->getConnection();
        $tableRegionName = $setup->getTable('directory_country_region_name');
        $connection->addIndex(
            $tableRegionName,
            $connection->getIndexName(
                $tableRegionName, 
                ['region_id', 'locale'], 
                AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['region_id', 'locale'],
            AdapterInterface::INDEX_TYPE_UNIQUE
        );
    }
}
