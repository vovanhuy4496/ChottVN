<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Orderattr
 */


namespace Amasty\Orderattr\Model\Config\Source;

class Boolean extends \Magento\Eav\Model\Entity\Attribute\Source\Boolean
{
    const EMPTY_VALUE = -1;

    public function __construct(\Magento\Eav\Model\ResourceModel\Entity\AttributeFactory $eavAttrEntity)
    {
        $this->_options = [
            ['label' => ' ', 'value' => self::EMPTY_VALUE],
            ['label' => __('Yes'), 'value' => self::VALUE_YES],
            ['label' => __('No'), 'value' => self::VALUE_NO]
        ];
        parent::__construct($eavAttrEntity);
    }
}
