<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Chottvn\SigninPhoneNumber\Exception;

use Magento\Framework\Phrase;

/**
 * @api
 * @since 100.0.2
 */
class NotCustomerAccountException extends \Magento\Framework\Exception\LocalizedException
{
    /**
     * @param \Magento\Framework\Phrase $phrase
     * @param \Exception $cause
     * @param int $code
     */
    public function __construct(Phrase $phrase = null, \Exception $cause = null, $code = 0)
    {
        if ($phrase === null) {
            $phrase = new Phrase(__('Invalid phone number or password.'));
        }
        parent::__construct($phrase, $cause, $code);
    }
}