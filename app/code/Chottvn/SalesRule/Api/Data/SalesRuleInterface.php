<?php
namespace Chottvn\SalesRule\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

interface SalesRuleInterface extends ExtensibleDataInterface
{
    const CACHE_TAG = 'chottvn_salesrule';
    const TABLE_NAME = 'chottvn_salesrule';
    const COLLECTION = 'chottvn_salesrule_collection';
    const OBJ_COLLECTION = 'salesrule_collection';

    const COLUMN_ENTITY_ID = 'entity_id';
    const COLUMN_SALESRULE_ID = 'salesrule_id';
    const COLUMN_IS_HIDE_CATALOG_PRODUCT_DETAIL = 'is_hide_catalog_product_detail';
    const COLUMN_IS_HIDE_CHECKOUT = 'is_hide_checkout';
}
