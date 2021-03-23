<?php

/**
 * A Magento 2 module named Chottvn/Affiliate
 * Copyright (C) 2020 Chottvn
 *
 * This file included in Chottvn/SigninWithPhoneNumber is licensed under OSL 3.0
 *
 * http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * Please see LICENSE.txt for the full text of the OSL 3.0 license
 */

namespace Chottvn\Affiliate\Block;

/**
 * Link Expired
 *
 * @api
 * @since 100.0.2
 */
class LinkExpired extends \Magento\Framework\View\Element\Template
{
	public function __construct(
        \Magento\Framework\View\Element\Template\Context $context
        )
	{
		parent::__construct($context);
	}
}
