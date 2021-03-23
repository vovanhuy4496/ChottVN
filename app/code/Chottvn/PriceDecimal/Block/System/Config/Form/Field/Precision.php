<?php
/**
 * @category   Chottvn
 * @package    Chottvn_PriceDecimal
 * @copyright  Copyright (c) 2018 Chottvn (http://www.Chottvn.com)
 * @author     Chottvn Developer <devops@chottructuyen.co>
 */

namespace Chottvn\PriceDecimal\Block\System\Config\Form\Field;


class Precision implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        return [
            ['value' => 1, 'label' => '1'],
            ['value' => 2, 'label' => '2'],
            ['value' => 3, 'label' => '3'],
            ['value' => 4, 'label' => '4'],
        ];
    }
}