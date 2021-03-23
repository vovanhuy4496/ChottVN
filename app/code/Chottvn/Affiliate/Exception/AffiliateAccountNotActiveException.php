<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Chottvn\Affiliate\Exception;

use Magento\Framework\Phrase;

/**
 * @api
 * @since 100.0.2
 */
class AffiliateAccountNotActiveException extends \Magento\Framework\Exception\LocalizedException
{
    /**
     * @param \Magento\Framework\Phrase $phrase
     * @param \Exception $cause
     * @param int $code
     */
    public function __construct(Phrase $phrase = null, \Exception $cause = null, $code = 0)
    {
        if ($phrase === null) {
            $phrase = new Phrase(__('Your account is not active, please active account then continue login.'));
        }
        parent::__construct($phrase, $cause, $code);
    }
}