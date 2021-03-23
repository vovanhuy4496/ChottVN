<?php

namespace Chottvn\Sms\Model\Config\Source;

/**
 * Class SMSType
 * Define options to the login process.
 */
class SMSType
{
    const TYPE_SMS_VERIFY_PHONE = 1;
    const TYPE_SMS_FORGOT_PASSWORD = 2;
    const TYPE_SMS_VOUCHER = 3;

    /**
     * Retrieve possible sign in options.
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            self::TYPE_SMS_VERIFY_PHONE => __('Send Verify OTP'),
            self::TYPE_SMS_FORGOT_PASSWORD => __('Send Forgot Password OTP'),
            self::TYPE_SMS_VOUCHER => __('Send Voucher'),
        ];
    }
}
