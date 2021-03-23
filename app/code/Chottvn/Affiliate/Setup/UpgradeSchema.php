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

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class UpgradeSchema
 *
 * @package Chottvn\Affiliate\Setup
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
        $setup->startSetup();

        if (version_compare($context->getVersion(), "0.1.4", "<")) {
            // get table customer_entity
            $eavTable = $setup->getTable('customer_entity');

            // Check if the table already exists
            if ($setup->getConnection()->isTableExists($eavTable) == true) {
                $connection = $setup->getConnection();

                // del_flg = column name which you want to delete
                $connection->dropColumn($eavTable, 'affiliate_code');
                $connection->dropColumn($eavTable, 'affiliate_status');
            }
        }

        if (version_compare($context->getVersion(), "0.1.7", "<")) {
            /**
            * create view affiliate_info
            */
            $createViewAffiliateInfoSql = "CREATE VIEW `affiliate_info` AS SELECT `a`.`entity_id` AS `entity_id`, max( ( CASE WHEN ( `c`.`attribute_code` = 'phone_number' ) THEN `b`.`value` ELSE NULL END ) ) AS `phone_number`, max( ( CASE WHEN ( `c`.`attribute_code` = 'affiliate_code' ) THEN `b`.`value` ELSE NULL END ) ) AS `affiliate_code`, max( ( CASE WHEN ( `c`.`attribute_code` = 'affiliate_status' ) THEN `b`.`value` ELSE NULL END ) ) AS `affiliate_status`, max( ( CASE WHEN ( `c`.`attribute_code` = 'affiliate_level' ) THEN `b`.`value` ELSE NULL END ) ) AS `affiliate_level`, max( ( CASE WHEN ( `c`.`attribute_code` = 'customer_email' ) THEN `b`.`value` ELSE NULL END ) ) AS `customer_email`, max( ( CASE WHEN (`d`.`event` = 'activated') THEN `d`.`value` ELSE NULL END ) ) AS `activated_at` FROM ( ( ( `customer_entity` `a` LEFT JOIN `customer_entity_varchar` `b` ON ( ( `b`.`entity_id` = `a`.`entity_id` ) ) ) LEFT JOIN `eav_attribute` `c` ON ( ( `c`.`attribute_id` = `b`.`attribute_id` ) ) ) LEFT JOIN `chottvn_log_affiliate` `d` ON ( ( `d`.`account_id` = `a`.`entity_id` ) ) ) GROUP BY `a`.`entity_id` ORDER BY `a`.`entity_id`;";
            $setup->run($createViewAffiliateInfoSql);
        }

        if (version_compare($context->getVersion(), "0.2.1", "<")) {
            /**
            * drop view affiliate_info
            */
            $dropViewAffiliateInfoSql = "DROP VIEW `affiliate_info`;";
            $setup->run($dropViewAffiliateInfoSql);

            /**
            * create view affiliate_info
            */
            $createViewAffiliateInfoSql = "CREATE OR REPLACE VIEW `vw_affiliate_info` AS SELECT `a`.`entity_id` AS `entity_id`, max( ( CASE WHEN ( `c`.`attribute_code` = 'phone_number' ) THEN `b`.`value` ELSE NULL END ) ) AS `phone_number`, max( ( CASE WHEN ( `c`.`attribute_code` = 'affiliate_code' ) THEN `b`.`value` ELSE NULL END ) ) AS `affiliate_code`, max( ( CASE WHEN ( `c`.`attribute_code` = 'affiliate_status' ) THEN `b`.`value` ELSE NULL END ) ) AS `affiliate_status`, max( ( CASE WHEN ( `c`.`attribute_code` = 'affiliate_level' ) THEN `b`.`value` ELSE NULL END ) ) AS `affiliate_level`, max( ( CASE WHEN ( `c`.`attribute_code` = 'customer_email' ) THEN `b`.`value` ELSE NULL END ) ) AS `customer_email`, max( ( CASE WHEN (`d`.`event` = 'activated') THEN `d`.`value` ELSE NULL END ) ) AS `activated_at`, max( ( CASE WHEN (`d`.`event` = 'registered') THEN `d`.`value` ELSE NULL END ) ) AS `registered_at` FROM ( ( ( `customer_entity` `a` LEFT JOIN `customer_entity_varchar` `b` ON ( ( `b`.`entity_id` = `a`.`entity_id` ) ) ) LEFT JOIN `eav_attribute` `c` ON ( ( `c`.`attribute_id` = `b`.`attribute_id` ) ) ) LEFT JOIN `chottvn_log_affiliate` `d` ON ( ( `d`.`account_id` = `a`.`entity_id` ) ) ) GROUP BY `a`.`entity_id` ORDER BY `a`.`entity_id`;";
            $setup->run($createViewAffiliateInfoSql);
        }


        $setup->endSetup();
    }
}

