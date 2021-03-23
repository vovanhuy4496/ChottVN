<?php

namespace Chottvn\SalesRule\Model\Rule\Action\Discount;

/**
 * Action name: Auto add promo items with products
 */
class Voucher extends \Magento\SalesRule\Model\Rule\Action\Discount\AbstractDiscount
{

  /**
     * {@inheritdoc}
     */
    public function calculate($rule, $item, $qty)
    {
      // echo $rule->getSimpleAction();
      /** @var \Magento\SalesRule\Model\Rule\Action\Discount\Data $discountData */
      $discountData = $this->discountFactory->create();

      //$this->_addFreeItems($rule, $item, $qty);

      return $discountData;
    }
}
