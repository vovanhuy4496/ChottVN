-- Update affiliate_level for legacy orders
UPDATE  sales_order a,  (
	SELECT DISTINCT order_id, affiliate_level
	FROM sales_order_item
	WHERE affiliate_level IS NOT NULL
) b
SET a.affiliate_level = b.affiliate_level
WHERE a.entity_id = b.order_id;
