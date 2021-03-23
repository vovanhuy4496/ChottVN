<?php

/**
 * A Magento 2 module named Chottvn/SigninPhoneNumber
 * Copyright (C) 2020 Chottvn
 *
 * This file included in Chottvn/SigninWithPhoneNumber is licensed under OSL 3.0
 *
 * http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * Please see LICENSE.txt for the full text of the OSL 3.0 license
 */

namespace Chottvn\SigninPhoneNumber\Api;

/**
 * @api
 */
interface SigninInterface
{
    /**
     * Return if module is enabled.
     *
     * @see \Chottvn\SigninPhoneNumber\Helper\Data\Helper
     * @param string|null $scopeCode
     * @return bool
     */
    public function isEnabled($scopeCode);
    
    /**
     * @see \Chottvn\SigninPhoneNumber\Helper\Data\Helper
     * @param string|null $scopeCode
     * @return string
     */
    public function getSigninMode($scopeCode);

    /**
     * Load customer object by phone attribute.
     *
     * @param string $phone
     * @return boolean|object
     */
    public function getByPhoneNumber(string $phone);
}
