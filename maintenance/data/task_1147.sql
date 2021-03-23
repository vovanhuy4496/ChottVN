/*
-- SQL to get attribute option values
select ea.attribute_id, ea.attribute_code, eaov.*, eao.sort_order
from eav_attribute ea
inner join eav_attribute_option eao on (eao.attribute_id = ea.attribute_id)
inner join eav_attribute_option_value eaov on (eaov.option_id = eao.option_id)
where ea.attribute_code = 'product_kind' and store_id = 0
order by eao.sort_order
;
*/

-- QA

TRUNCATE chottvn_rma_rule;

-- Insert
INSERT INTO `chottvn_rma_rule` (`id`, `name`, `status`, `start_date`, `product_kind`, `conditions`, `priority`, `discard_subsequent_rules`) VALUES (1, 'All products', '1', '2020-09-01 07:00:00', '5742', '{"return_period": 30}', '0', 'no');
INSERT INTO `chottvn_rma_rule` (`id`, `name`, `status`, `start_date`, `product_kind`, `conditions`, `priority`, `discard_subsequent_rules`) VALUES (2, 'All accessories', '1', '2020-09-01 07:00:00', '5743', '{return_period: 30}', '0', 'no')


-- Prod

TRUNCATE chottvn_rma_rule;

-- Insert
INSERT INTO `chottvn_rma_rule` (`id`, `name`, `status`, `start_date`, `product_kind`, `conditions`, `priority`, `discard_subsequent_rules`) VALUES (1, 'All products', '1', '2020-09-01 07:00:00', '5740', '{"return_period": 30}', '0', 'no');
INSERT INTO `chottvn_rma_rule` (`id`, `name`, `status`, `start_date`, `product_kind`, `conditions`, `priority`, `discard_subsequent_rules`) VALUES (2, 'All accessories', '1', '2020-09-01 07:00:00', '5741', '{"return_period": 30}', '0', 'no')
