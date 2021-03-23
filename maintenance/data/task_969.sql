-- View vw_chottvn_affiliate_revenue
CREATE OR REPLACE VIEW vw_chottvn_affiliate_revenue
AS (
  SELECT so.affiliate_account_id AS affiliate_account_id, so.customer_id AS customer_id,
    so.entity_id AS order_id, so.created_at AS created_at, so.updated_at AS updated_at, so.status AS order_status,
    soi.item_id AS order_item_id, soi.product_brand_id AS product_brand_id,
    soi.price AS price, soi.base_price, soi.original_price AS original_price, soi.base_original_price AS base_original_price,
      soi.qty_ordered AS qty_ordered,  soi.qty_refunded AS qty_refunded,
    (soi.qty_ordered -  soi.qty_refunded) *  soi.affiliate_amount_item AS affiliate_reward_amount,
        (soi.qty_ordered -  soi.qty_refunded) *  soi.base_affiliate_amount_item AS base_affiliate_reward_amount,
    (soi.qty_ordered -  soi.qty_refunded) *  soi.price  AS affiliate_revenue_amount,
      (soi.qty_ordered -  soi.qty_refunded) *  soi.base_price  AS base_affiliate_revenue_amount
  FROM sales_order so
  JOIN sales_order_item soi ON so.entity_id = soi.order_id
  WHERE so.affiliate_account_id IS NOT NULL && so.status IN ('finished', 'returned_and_finished')
  OR (  so.status IN ('complete')  AND  ABS(DATEDIFF(so.updated_at, NOW()))  > COALESCE(soi.return_period, 30)  ) --  OR (  so.status IN ('complete')  AND  DATE_ADD( so.updated_at,  INTERVAL 30 DAY) < NOW())
);

-- View vw_chottvn_affiliate_statistic_monthly
CREATE OR REPLACE VIEW vw_chottvn_affiliate_statistic_monthly
AS (
  SELECT so.affiliate_account_id AS affiliate_account_id, so.customer_id AS customer_id
       , so.entity_id AS order_id
       , DATE_FORMAT(so.updated_at, "%Y-%m") AS month, DATE_FORMAT(so.updated_at, "%m/%Y") AS month_label
       , DATE_FORMAT(
             CASE
                 WHEN so.status IN ('complete', 'finished', 'returned_and_finished', 'replaced_and_finished', 'replaced_and_returned_and_finished')
                  AND ABS(TIMESTAMPDIFF(SECOND, so.updated_at, NOW())) / (24 * 60 * 60) > COALESCE(soi.return_period, 30)
                     THEN DATE_ADD(so.updated_at, INTERVAL COALESCE(soi.return_period, 30) + 0 DAY)
                 ELSE so.updated_at
             END
       , "%Y-%m") AS finished_month
       , DATE_FORMAT(
             CASE
                 WHEN so.status IN ('complete', 'finished', 'returned_and_finished', 'replaced_and_finished', 'replaced_and_returned_and_finished')
                 AND ABS(TIMESTAMPDIFF(SECOND, so.updated_at, NOW())) / (24 * 60 * 60) > COALESCE(soi.return_period, 30)
                     THEN DATE_ADD(so.updated_at, INTERVAL COALESCE(soi.return_period, 30) + 0 DAY)
                 ELSE so.updated_at
             END
       , "%m/%Y") AS finished_month_label
       , CASE
             WHEN so.status IN ('complete', 'finished', 'returned_and_finished', 'replaced_and_finished', 'replaced_and_returned_and_finished')
             AND ABS(TIMESTAMPDIFF(SECOND, so.updated_at, NOW())) / (24 * 60 * 60) > COALESCE(soi.return_period, 30)
                 THEN DATE_ADD(so.updated_at, INTERVAL COALESCE(soi.return_period, 30) + 0 DAY)
             ELSE so.updated_at
         END AS finished_at
       , so.created_at AS created_at
       , so.updated_at AS updated_at
       , so.status AS order_status, soi.item_id AS order_item_id, soi.product_brand_id AS product_brand_id
       , COALESCE(soi.return_period, 30) AS return_period
       , soi.price AS price, soi.base_price
       , soi.original_price AS original_price, soi.base_original_price AS base_original_price
       , soi.qty_ordered AS qty_ordered, soi.qty_refunded AS qty_refunded
       , soi.affiliate_amount_item AS affiliate_amount_item, soi.base_affiliate_amount_item AS base_affiliate_amount_item
       , (soi.qty_ordered - soi.qty_refunded) * soi.affiliate_amount_item AS affiliate_reward_amount
       , (soi.qty_ordered - soi.qty_refunded) * soi.base_affiliate_amount_item AS base_affiliate_reward_amount
       , (soi.qty_ordered - soi.qty_refunded) * soi.price AS affiliate_revenue_amount
       , (soi.qty_ordered - soi.qty_refunded) * soi.base_price AS base_affiliate_revenue_amount
       , soi.qty_refunded * soi.affiliate_amount_item AS affiliate_reward_refunded_amount
       , soi.qty_refunded * soi.base_affiliate_amount_item AS base_affiliate_reward_refunded_amount
       , soi.qty_refunded * soi.price AS affiliate_revenue_refunded_amount
       , soi.qty_refunded * soi.base_price AS base_affiliate_revenue_refunded_amount
  FROM sales_order so
  JOIN sales_order_item soi ON so.entity_id = soi.order_id
  WHERE so.affiliate_account_id IS NOT NULL
);


-- View vw_chottvn_finance_transaction
CREATE OR REPLACE VIEW vw_chottvn_finance_transaction
AS (
  SELECT t.*, tt.code AS transaction_type_code
        FROM chottvn_finance_transaction t
        LEFT JOIN chottvn_finance_transactiontype tt 
          ON t.transaction_type_id = tt.transactiontype_id
);


-- Sample Query for View vw_chottvn_affiliate_statistic_monthly

/*
  SELECT so.affiliate_account_id AS affiliate_account_id, so.customer_id AS customer_id
       , so.entity_id AS order_id
       , DATE_FORMAT(
             CASE
                 WHEN so.status IN ('complete') AND ABS(DATEDIFF(so.updated_at, NOW())) > COALESCE(soi.return_period, 30)
                     THEN DATE_ADD(so.updated_at, INTERVAL COALESCE(soi.return_period, 30) + 0 DAY)
                 ELSE so.updated_at
             END
		   , "%Y-%m") AS month
       , DATE_FORMAT(
             CASE
                 WHEN so.status IN ('complete') AND ABS(DATEDIFF(so.updated_at, NOW())) > COALESCE(soi.return_period, 30)
                     THEN DATE_ADD(so.updated_at, INTERVAL COALESCE(soi.return_period, 30) + 0 DAY)
                 ELSE so.updated_at
             END
		   , "%m/%Y") AS month_label
       , CASE
             WHEN so.status IN ('complete') AND ABS(DATEDIFF(so.updated_at, NOW())) > COALESCE(soi.return_period, 30)
                 THEN DATE_ADD(so.updated_at, INTERVAL COALESCE(soi.return_period, 30) + 0 DAY)
             ELSE so.updated_at
         END AS finished_at
       , so.created_at AS created_at
       , so.updated_at AS updated_at
       , so.status AS order_status, soi.item_id AS order_item_id, soi.product_brand_id AS product_brand_id
       , COALESCE(soi.return_period, 30) AS return_period
       , soi.price AS price, soi.base_price
       , soi.original_price AS original_price, soi.base_original_price AS base_original_price
       , soi.qty_ordered AS qty_ordered, soi.qty_refunded AS qty_refunded
       , soi.affiliate_amount_item AS affiliate_amount_item, soi.base_affiliate_amount_item AS base_affiliate_amount_item
       , (soi.qty_ordered - soi.qty_refunded) * soi.affiliate_amount_item AS affiliate_reward_amount
       , (soi.qty_ordered - soi.qty_refunded) * soi.base_affiliate_amount_item AS base_affiliate_reward_amount
       , (soi.qty_ordered - soi.qty_refunded) * soi.price AS affiliate_revenue_amount
       , (soi.qty_ordered - soi.qty_refunded) * soi.base_price AS base_affiliate_revenue_amount
       , soi.qty_refunded * soi.affiliate_amount_item AS affiliate_reward_refunded_amount
       , soi.qty_refunded * soi.base_affiliate_amount_item AS base_affiliate_reward_refunded_amount
       , soi.qty_refunded * soi.price AS affiliate_revenue_refunded_amount
       , soi.qty_refunded * soi.base_price AS base_affiliate_revenue_refunded_amount
  FROM sales_order so
  JOIN sales_order_item soi ON so.entity_id = soi.order_id
  WHERE so.affiliate_account_id = 118
*/


-- Sample Query for Stats by month
/*
SELECT data_root.month, data_root.month_label
     , COALESCE(pending.order_count, 0) AS order_count_pending
     , COALESCE(pending.revenue, 0) AS revenue_pending
     , COALESCE(pending.reward, 0) AS reward_pending
     , COALESCE(finished_count.order_count, 0) AS order_count_finished
     , COALESCE(finished_revenue.revenue, 0) AS revenue_finished
     , COALESCE(finished_revenue.reward, 0) AS reward_finished
     , COALESCE(returned_count.order_count, 0) AS order_count_returned
     , COALESCE(returned_revenue.revenue, 0) AS revenue_returned
     , COALESCE(returned_revenue.reward, 0) AS reward_returned
FROM (
    SELECT finished_month AS `month`, finished_month_label AS month_label
    FROM vw_chottvn_affiliate_statistic_monthly
    WHERE affiliate_account_id = 118
     AND finished_at between '2020-09-01' AND '2020-09-30'
    GROUP BY `month`, month_label
) data_root
LEFT JOIN (
    SELECT finished_month AS `month`, finished_month_label AS month_label
         , COUNT(DISTINCT order_id) AS order_count
         , SUM(affiliate_revenue_amount) AS revenue
         , SUM(affiliate_reward_amount) AS reward
    FROM vw_chottvn_affiliate_statistic_monthly
    WHERE affiliate_account_id = 118
      AND finished_at between '2020-09-01' AND '2020-09-30'
      AND ( order_status IN ('pending', 'processing', 'packaging', 'delivery') )
    GROUP BY `month`, month_label
) pending ON (data_root.month = pending.month)
LEFT JOIN (
    SELECT finished_month AS `month`, finished_month_label AS month_label
         , COUNT(DISTINCT order_id) AS order_count
    FROM vw_chottvn_affiliate_statistic_monthly
    WHERE affiliate_account_id = 118
      AND finished_at between '2020-09-01' AND '2020-09-30'
      AND order_status IN ('complete','finished')
    GROUP BY `month`, month_label
) finished_count ON (data_root.month = finished_count.month)
LEFT JOIN (
    SELECT finished_month AS `month`, finished_month_label AS month_label
         , SUM(affiliate_revenue_amount) AS revenue
         , SUM(affiliate_reward_amount) AS reward
    FROM vw_chottvn_affiliate_statistic_monthly
    WHERE affiliate_account_id = 118
    AND finished_at between '2020-09-01' AND '2020-09-30'
    AND order_status IN ('complete', 'finished', 'returned_and_finished', 'replaced_and_finished', 'replaced_and_returned_and_finished')
    GROUP BY `month`, month_label
) finished_revenue ON (data_root.month = finished_revenue.month)
LEFT JOIN (
    SELECT finished_month AS `month`, finished_month_label AS month_label
         , COUNT(DISTINCT order_id) AS order_count
    FROM vw_chottvn_affiliate_statistic_monthly
    WHERE affiliate_account_id = 118
      AND finished_at between '2020-09-01' AND '2020-09-30'
      AND ( order_status IN ('canceled','returned','returned_and_finished','replaced','replaced_and_finished','replaced_and_returned','replaced_and_returned_and_finished') )
    GROUP BY `month`, month_label
) returned_count ON (data_root.month = returned_count.month)
LEFT JOIN (
    SELECT finished_month AS `month`, finished_month_label AS month_label
         , SUM(
					     CASE
						       WHEN order_status = 'canceled' THEN affiliate_revenue_amount
						       WHEN order_status = 'returned' THEN affiliate_revenue_amount
						       WHEN order_status = 'returned_and_finished' THEN affiliate_revenue_refunded_amount
						       WHEN order_status = 'replaced' THEN affiliate_revenue_amount
						       WHEN order_status = 'replaced_and_finished' THEN affiliate_revenue_refunded_amount
						       WHEN order_status = 'replaced_and_returned' THEN affiliate_revenue_amount
						       WHEN order_status = 'replaced_and_returned_and_finished' THEN affiliate_revenue_refunded_amount
                   ELSE affiliate_revenue_amount
					     END
				   ) AS revenue
         , SUM(
					     CASE
						       WHEN order_status = 'canceled' THEN affiliate_reward_amount
						       WHEN order_status = 'returned' THEN affiliate_reward_amount
						       WHEN order_status = 'returned_and_finished' THEN affiliate_reward_refunded_amount
						       WHEN order_status = 'replaced' THEN affiliate_reward_amount
						       WHEN order_status = 'replaced_and_finished' THEN affiliate_reward_refunded_amount
						       WHEN order_status = 'replaced_and_returned' THEN affiliate_reward_amount
						       WHEN order_status = 'replaced_and_returned_and_finished' THEN affiliate_reward_refunded_amount
                   ELSE affiliate_reward_amount
					     END
				   ) AS reward
    FROM vw_chottvn_affiliate_statistic_monthly
    WHERE affiliate_account_id = 118
      AND finished_at between '2020-09-01' AND '2020-09-30'
      AND ( order_status IN ('canceled','returned','returned_and_finished','replaced','replaced_and_finished','replaced_and_returned','replaced_and_returned_and_finished') )
    GROUP BY `month`, month_label
) returned_revenue ON (data_root.month = returned_revenue.month)
ORDER BY month ASC
;
*/

-- Backup View vw_chottvn_affiliate_statistic_monthly on 2020-09-28
/*
CREATE OR REPLACE VIEW vw_chottvn_affiliate_statistic_monthly
AS (
  SELECT so.affiliate_account_id AS affiliate_account_id, so.customer_id AS customer_id
       , DATE_FORMAT(so.updated_at, "%Y-%m") AS month, DATE_FORMAT(so.updated_at, "%m/%Y") AS month_label
       , so.entity_id AS order_id, so.created_at AS created_at, so.updated_at AS updated_at
       , so.status AS order_status, soi.item_id AS order_item_id, soi.product_brand_id AS product_brand_id
       , COALESCE(soi.return_period, 30) AS return_period
       , soi.price AS price, soi.base_price
       , soi.original_price AS original_price, soi.base_original_price AS base_original_price
       , soi.qty_ordered AS qty_ordered, soi.qty_refunded AS qty_refunded
       , soi.affiliate_amount_item AS affiliate_amount_item, soi.base_affiliate_amount_item AS base_affiliate_amount_item
       , (soi.qty_ordered - soi.qty_refunded) * soi.affiliate_amount_item AS affiliate_reward_amount
       , (soi.qty_ordered - soi.qty_refunded) * soi.base_affiliate_amount_item AS base_affiliate_reward_amount
       , (soi.qty_ordered - soi.qty_refunded) * soi.price AS affiliate_revenue_amount
       , (soi.qty_ordered - soi.qty_refunded) * soi.base_price AS base_affiliate_revenue_amount
       , soi.qty_refunded * soi.affiliate_amount_item AS affiliate_reward_refunded_amount
       , soi.qty_refunded * soi.base_affiliate_amount_item AS base_affiliate_reward_refunded_amount
       , soi.qty_refunded * soi.price AS affiliate_revenue_refunded_amount
       , soi.qty_refunded * soi.base_price AS base_affiliate_revenue_refunded_amount
  FROM sales_order so
  JOIN sales_order_item soi ON so.entity_id = soi.order_id
);
*/
