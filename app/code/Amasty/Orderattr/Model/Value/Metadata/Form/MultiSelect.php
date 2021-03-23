<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Orderattr
 */


namespace Amasty\Orderattr\Model\Value\Metadata\Form;

class MultiSelect extends \Magento\Eav\Model\Attribute\Data\Multiselect
{
    /**
     * @inheritdoc
     */
    public function compactValue($value)
    {
        if ($value === false) {
            $value = '';
        }

        return parent::compactValue($value);
    }
}
