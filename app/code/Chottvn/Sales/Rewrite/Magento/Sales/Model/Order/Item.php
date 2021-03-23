<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Chottvn\Sales\Rewrite\Magento\Sales\Model\Order;

use Magento\Framework\Api\AttributeValueFactory;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Model\AbstractModel;

/**
 * Order Item Model
 *
 * @api
 * @method int getGiftMessageId()
 * @method \Magento\Sales\Model\Order\Item setGiftMessageId(int $value)
 * @method int getGiftMessageAvailable()
 * @method \Magento\Sales\Model\Order\Item setGiftMessageAvailable(int $value)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class Item extends \Magento\Sales\Model\Order\Item implements OrderItemInterface
{

    public function getProductNameLong()
    {
        return $this->getName();
    }

    public function getProductNameLongHtml()
    {
        $longName =  $this->getProductNameLong();
        $shortName = $this->getProductNameShort() ? $this->getProductNameShort():$this->getProductNameLong();
        $shortNameStrong = "<strong>".$shortName."</strong>";
        return str_replace($shortName, $shortNameStrong, $longName);
    }
}