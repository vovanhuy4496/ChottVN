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
 * Class AffiliateLevel
 *
 * @package Chottvn\Affiliate\Model\Customer\Attribute\Source
 */
class AffiliateLevel extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{

    /**
     * getAllOptions
     *
     * @return array
     */
    public function getAllOptions()
    {
        if ($this->_options === null) {
            $this->_options = [
                ['value' => 'ctv', 'label' => __('affiliate_level_ctv'), 'sort' => 1],
                ['value' => 'ctv_1', 'label' => __('affiliate_level_ctv_1'), 'sort' => 2],
                ['value' => 'ctv_2', 'label' => __('affiliate_level_ctv_2'), 'sort' => 3],
                ['value' => 'ctv_3', 'label' => __('affiliate_level_ctv_3'), 'sort' => 4],
                ['value' => 'ctv_4', 'label' => __('affiliate_level_ctv_4'), 'sort' => 5],
                ['value' => 'ctv_5', 'label' => __('affiliate_level_ctv_5'), 'sort' => 6]
            ];
        }
        return $this->_options;
    }
}

