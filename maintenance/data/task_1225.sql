# Insert lable log for legacy complete order
INSERT INTO chottvn_log_sales_order(
  order_id, order_status, value, created_at
)
SELECT entity_id, 'complete', '{}', updated_at
FROM sales_order
WHERE status IN ('complete', 'finished'  , 'returned_and_finished')
  AND entity_id NOT IN (
    SELECT DISTINCT order_id
    FROM chottvn_log_sales_order
    WHERE order_status = 'complete'
  )
;
