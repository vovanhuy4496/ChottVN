<?php

namespace Chottvn\Sms\Model\Config\Source;

/**
 * Class SMSBrandname
 * Define options to the login process.
 */
class SMSBrandname
{
    /**
     * @var int Value using eSMS provider to send SMS.
     */
    const PROVIDER_ESMS = 1;

    /**
     * @var int Value using VMG provider to send SMS.
     */
    const PROVIDER_VMG = 2;

    /**
     * Retrieve possible sign in options.
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            self::PROVIDER_ESMS => __('eSMS'),
            self::PROVIDER_VMG => __('VMG'),
        ];
    }
}
