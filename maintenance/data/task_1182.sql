-- TRUNCATE chottvn_finance_transactiontype;

INSERT INTO `chottvn_finance_transactiontype` (`transactiontype_id`, `code`, `rate`, `name`, `created_at`, `updated_at`) VALUES ('1', 'deposit_margin', '1', 'Nộp tiền ký quỹ', '2020-09-01 07:00:00', '2020-09-01 07:00:00');
INSERT INTO `chottvn_finance_transactiontype` (`transactiontype_id`, `code`, `rate`, `name`, `created_at`, `updated_at`) VALUES ('2', 'deposit_cash', '1', 'Nộp tiền vào tài khoản', '2020-10-01 07:00:00', '2020-09-01 07:00:00');
INSERT INTO `chottvn_finance_transactiontype` (`transactiontype_id`, `code`, `rate`, `name`, `created_at`, `updated_at`) VALUES ('3', 'withdrawal_margin', '-1', 'Rút tiền ký quỹ', '2020-09-01 07:00:000', '2020-09-01 07:00:005');
INSERT INTO `chottvn_finance_transactiontype` (`transactiontype_id`, `code`, `rate`, `name`, `created_at`, `updated_at`) VALUES ('4', 'withdrawal_reward', '-1', 'Rút tiền chiết khấu', '2020-09-01 07:00:00', '2020-09-01 07:00:00');
INSERT INTO `chottvn_finance_transactiontype` (`transactiontype_id`, `code`, `rate`, `name`, `created_at`, `updated_at`) VALUES ('5', 'handle_responsibility', '-1', 'Tiền xử lý trách nhiệm', '2020-09-01 07:00:00', '2020-09-01 07:00:00');
INSERT INTO `chottvn_finance_transactiontype` (`transactiontype_id`, `code`, `rate`, `name`, `created_at`, `updated_at`) VALUES ('6', 'affiliate_gift', '1', 'Tiền thưởng', '2020-09-01 07:00:00', '2020-09-01 07:00:00');
