<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Orderattr
 */


namespace Amasty\Orderattr\Model\Webapi;

/**
 * Replaces a "%amasty_cart_id%" value with the current authenticated customer's cart
 */
class ParamOverriderAmastyCartId  extends \Magento\Quote\Model\Webapi\ParamOverriderCartId
{

}
