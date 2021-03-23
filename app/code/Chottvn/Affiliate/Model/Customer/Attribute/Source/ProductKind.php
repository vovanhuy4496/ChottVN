<?php
/**
 * Copyright (c) 2019 ChottVN
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace Chottvn\Affiliate\Model\Customer\Attribute\Source;

/**
 * Class ProductKind
 *
 * @package Chottvn\Affiliate\Model\Customer\Attribute\Source
 */
class ProductKind extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{

    /**
     * Constructor
     */
    public function __construct(
        \Magento\Eav\Model\Config $eavConfig
    ) {
        $this->eavConfig = $eavConfig;
    }

    /**
     * getAllOptions
     *
     * @return array
     */
    public function getAllOptions()
    {
        // if ($this->_options === null) {
        //     $this->_options = [
        //         ['value' => 'product', 'label' => __('Accessory')],
        //         ['value' => 'accessory', 'label' => __('Product')]
        //     ];
        // }
        // return $this->_options;
        $attribute = $this->eavConfig->getAttribute('catalog_product', 'product_kind');
        return $attribute->getSource()->getAllOptions();
    }

    public function getCodeFromId($productKindId){
        foreach ($this->getAllOptions() as $option) {
            if($option["value"] == $productKindId){
                return $option["label"];
            }
        }
        return "";
    }
}

