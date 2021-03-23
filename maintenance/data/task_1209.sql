-- View vw_sales_order_complete_date
CREATE OR REPLACE   VIEW vw_sales_order_return_period
AS (
  SELECT soi.order_id AS order_id, MAX(COALESCE(soi.return_period, 30) ) AS return_period
  FROM sales_order_item soi
  GROUP BY soi.order_id
);

-- View vw_sales_order_complete_date
CREATE OR REPLACE  VIEW vw_sales_order_complete_date
AS (
  SELECT order_id, created_at AS order_complete_at
  FROM chottvn_log_sales_order
  WHERE order_status = 'complete'
);

-- View vw_sales_order_complete
CREATE OR REPLACE VIEW vw_sales_order_complete
AS (
  SELECT so.entity_id AS order_id
       , affiliate_account_id
       , so.status AS order_status
       , orp.return_period AS return_period
       , COALESCE(ocd.order_complete_at, so.updated_at) AS order_completed_at
       , DATE_ADD(COALESCE(ocd.order_complete_at, so.updated_at), INTERVAL orp.return_period + 0 DAY) AS order_finished_at
       , ABS(TIMESTAMPDIFF(SECOND, COALESCE(ocd.order_complete_at, so.updated_at), NOW())) / (24 * 60 * 60) AS order_days
  FROM sales_order so
  JOIN vw_sales_order_return_period orp ON (so.entity_id = orp.order_id)
  LEFT JOIN vw_sales_order_complete_date ocd ON (so.entity_id = ocd.order_id)
  WHERE so.status IN ('complete')
    AND ABS(TIMESTAMPDIFF(SECOND, COALESCE(ocd.order_complete_at, so.updated_at), NOW())) / (24 * 60 * 60) > orp.return_period
  ORDER BY order_finished_at
);

-- View vw_sales_order_info
CREATE OR REPLACE VIEW vw_sales_order_info
AS (
  SELECT so.entity_id AS order_id
       , affiliate_account_id
       , so.status AS order_status
       , orp.return_period AS return_period
       , COALESCE(ocd.order_complete_at, so.updated_at) AS order_completed_at
       , DATE_ADD(COALESCE(ocd.order_complete_at, so.updated_at), INTERVAL orp.return_period + 0 DAY) AS order_finished_at
       , ABS(TIMESTAMPDIFF(SECOND, COALESCE(ocd.order_complete_at, so.updated_at), NOW())) / (24 * 60 * 60) AS order_days
  FROM sales_order so
  JOIN vw_sales_order_return_period orp ON (so.entity_id = orp.order_id)
  LEFT JOIN vw_sales_order_complete_date ocd ON (so.entity_id = ocd.order_id)
);