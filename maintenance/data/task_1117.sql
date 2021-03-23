TRUNCATE TABLE `chottvn_affiliate_level_rule`;

INSERT INTO `chottvn_affiliate_level_rule` (`id`, `name`, `status`, `start_date`, `affiliate_level`, `conditions`, `description`, `priority`, `discard_subsequent_rules`)
VALUES ('1', 'group 1 - ctv', '1', '2020-09-01 07:00:00', 'ctv', '{"code": "margin_or_revenue","agg_type": "or","items": [{"code": "revenue","period": {"type": "months","value": 6},"kind": "amount","value": 0,"operator": ">="}]}', NULL, '0', 'no');

INSERT INTO `chottvn_affiliate_level_rule` (`id`, `name`, `status`, `start_date`, `affiliate_level`, `conditions`, `description`, `priority`, `discard_subsequent_rules`)
VALUES ('2', 'group 2 - ctv 1', '1', '2020-09-01 07:00:00', 'ctv_1', '{"code": "margin_or_revenue","agg_type": "or","items": [{"code": "revenue","period": {"type": "months","value": 6},"kind": "amount","value": 1,"operator": ">="}]}', '<p>Điều kiện đạt <strong>Cộng tác viên nhóm 2</strong> là xét doanh thu 6 tháng liền kề đạt: <strong>1.000.000đ</strong></p>', '0', 'no');

INSERT INTO `chottvn_affiliate_level_rule` (`id`, `name`, `status`, `start_date`, `affiliate_level`, `conditions`, `description`, `priority`, `discard_subsequent_rules`)
VALUES ('3', 'group 3 - ctv 2', '1', '2020-09-01 07:00:00', 'ctv_2', '{"code": "margin_or_revenue","agg_type": "or","items": [{"code": "margin","kind": "amount","value": 2,"operator": ">="},{"code": "revenue","period": {"type": "months","value": 6},"kind": "amount","value": 20,"operator": ">="}]}', '<p>Điều kiện đạt <strong>Cộng tác viên nhóm 3</strong> là ký quỹ: <strong>2.000.000đ</strong> hoặc xét doanh thu 6 tháng liền kề đạt: <strong>20.000.000đ</strong></p>', '0', 'no');

INSERT INTO `chottvn_affiliate_level_rule` (`id`, `name`, `status`, `start_date`, `affiliate_level`, `conditions`, `description`, `priority`, `discard_subsequent_rules`)
VALUES ('4', 'group 4 - ctv 3', '1', '2020-09-01 07:00:00', 'ctv_3', '{"code": "margin_or_revenue","agg_type": "or","items": [{"code": "margin","kind": "amount","value": 5,"operator": ">="},{"code": "revenue","period": {"type": "months","value": 6},"kind": "amount","value": 50,"operator": ">="}]}', '<p>Điều kiện đạt <strong>Cộng tác viên nhóm 4</strong> là ký quỹ: <strong>5.000.000đ</strong> hoặc xét doanh thu 6 tháng liền kề đạt: <strong>50.000.000đ</strong></p>', '0', 'no');

INSERT INTO `chottvn_affiliate_level_rule` (`id`, `name`, `status`, `start_date`, `affiliate_level`, `conditions`, `description`, `priority`, `discard_subsequent_rules`)
VALUES ('5', 'group 5 - ctv 4', '1', '2020-09-01 07:00:00', 'ctv_4', '{"code": "margin_or_revenue","agg_type": "or","items": [{"code": "margin","kind": "amount","value": 10,"operator": ">="},{"code": "revenue","period": {"type": "months","value": 6},"kind": "amount","value": 100,"operator": ">="}]}', '<p>Điều kiện đạt <strong>Cộng tác viên nhóm 5</strong> là ký quỹ: <strong>10.000.000đ</strong> hoặc xét doanh thu 6 tháng liền kề đạt: <strong>100.000.000đ</strong></p>', '0', 'no');

INSERT INTO `chottvn_affiliate_level_rule` (`id`, `name`, `status`, `start_date`, `affiliate_level`, `conditions`, `description`, `priority`, `discard_subsequent_rules`)
VALUES ('6', 'group 6 - ctv 5', '1', '2020-09-01 07:00:00', 'ctv_5', '{"code": "margin_or_revenue","agg_type": "or","items": [{"code": "margin","kind": "amount","value": 20,"operator": ">="},{"code": "revenue","period": {"type": "months","value": 6},"kind": "amount","value": 200,"operator": ">="}]}', '<p>Điều kiện đạt <strong>Cộng tác viên nhóm 6</strong> là ký quỹ: <strong>20.000.000đ</strong> hoặc xét doanh thu 6 tháng liền kề đạt: <strong>200.000.000đ</strong></p>', '0', 'no');
