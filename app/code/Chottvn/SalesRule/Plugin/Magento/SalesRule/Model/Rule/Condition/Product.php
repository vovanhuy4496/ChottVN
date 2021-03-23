<?php
/**
 * Copyright © (c) chotructuyen.co All rights reserved.
 * See COPYING.txt for license details.
 */


namespace Chottvn\SalesRule\Plugin\Magento\SalesRule\Model\Rule\Condition;

use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Additional attr for validator.
 */
class Product
{
    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
    ) {
        $this->productRepository = $productRepository;      
    }

    /**
     * @param \Magento\Rule\Model\Condition\Product\AbstractProduct $subject
     * @return 
     */
    public function afterGetValueElementType(
        \Magento\Rule\Model\Condition\Product\AbstractProduct $subject,
        $result
    ) {
        if ($subject->getAttribute() == "product_brand"){
            $result = "multiselect";
        }

        return $result;
    }
     /**
     * @param \Magento\Rule\Model\Condition\Product\AbstractProduct $subject
     * @return 
     * OVERWRITE this function not work
     */
    /*public function afterGetOperatorByInputType(
        \Magento\Rule\Model\Condition\Product\AbstractProduct $subject,
        $result
    ) {
        if ($subject->getAttribute() == "product_brand"){
            $result['select'] = ['==', '!=', '<=>', '()', '!()'];
        }

        return $result;
    }*/

    public function afterGetOperatorSelectOptions(
        \Magento\Rule\Model\Condition\Product\AbstractProduct $subject,
        $result
    ) {
        if ($subject->getAttribute() == "product_brand"){
            array_push($result, [
                "value" => "()",
                "label" => "is one of"
            ]);
            array_push($result, [
                "value" => "!()",
                "label" => "is not one of"
            ]);
        }

        return $result;
    }

}
