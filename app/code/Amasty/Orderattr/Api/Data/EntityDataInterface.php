<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Orderattr
 */


namespace Amasty\Orderattr\Api\Data;

use Amasty\Orderattr\Api\Data\CheckoutEntityInterface;
use Magento\Framework\Api\CustomAttributesDataInterface;

interface EntityDataInterface extends CheckoutEntityInterface, CustomAttributesDataInterface
{

}
