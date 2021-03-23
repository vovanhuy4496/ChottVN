UPDATE sales_order a, 
			(SELECT order_id, SUM(original_price * qty_ordered) AS original_total, SUM(base_original_price * qty_ordered) AS base_original_total
			FROM sales_order_item
			GROUP BY order_id) b
SET a.original_total =  b.original_total,
        a.base_original_total = b.base_original_total,
        a.savings_amount = a.original_total - a.subtotal,
        a.base_savings_amount = a.base_original_total - a.base_subtotal
WHERE (a.original_total IS NULL OR  a.savings_amount IS NULL )
AND a.entity_id = b.order_id;