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

namespace Mageplaza\BannerSlider\Setup;

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
        if (version_compare($context->getVersion(), '2.0.1', '<')) {
            if ($installer->tableExists('mageplaza_bannerslider_banner')) {
                $connection->addColumn($installer->getTable('mageplaza_bannerslider_banner'), 'start_date', [
                    'type' => Table::TYPE_DATETIME, null,
                    'comment' => 'Banner Slider Start Date',
                    'after' => 'newtab'
                ]);
                $connection->addColumn($installer->getTable('mageplaza_bannerslider_banner'), 'end_date', [
                    'type' => Table::TYPE_DATETIME, null,
                    'comment' => 'Banner Slider End Date',
                    'after' => 'start_date'
                ]);
            }
        }
        $installer->endSetup();
    }
}
