<?php
namespace Chottvn\SalesRule\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

interface GiftRuleInterface extends ExtensibleDataInterface
{
    const RULE_NAME = 'cttpromo_rule';
    const EXTENSION_CODE = self::RULE_NAME;

    /**#@+
     * Sales Rule Simple Action values
     */
    const ORDER_VOUCHER = 'cttpromo_order_voucher'; // promo voucher for next order
    /**#@-*/

    /**#@+
     * Constants defined for keys of data array
     */
    const ENTITY_ID = 'entity_id';
    const SALESRULE_ID = 'salesrule_id';
    const SKU = 'sku';
    const TYPE = 'type';
    const TOP_BANNER_SHOW_GIFT_IMAGES = 'top_banner_show_gift_images';
    const AFTER_PRODUCT_BANNER_SHOW_GIFT_IMAGES = 'after_product_banner_show_gift_images';
    const ITEMS_DISCOUNT = 'items_discount';
    const MINIMAL_ITEMS_PRICE = 'minimal_items_price';
    const APPLY_TAX = 'apply_tax';
    const APPLY_SHIPPING = 'apply_shipping';
    /**#@-*/
}
