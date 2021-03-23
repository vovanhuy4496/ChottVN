<?php
/**
 * Created by PhpStorm.
 * User: lhttram
 * Date: 3/12/18
 * Time: 2:47 PM
 */

namespace Ves\Brand\Api\Data;


interface BrandProductInterface
{

    const BRAND_ID = 'brand_id';
    const PRODUCT_ID = 'product_id';
    const POSITOIN = 'position';

    /**
     * @return int
     */
    public function getBrandId();

    /**
     * @param int $brand_id
     * @return BrandProductInterface
     */
    public function setBrandId($brand_id);

    /**
     * @return int
     */
    public function getProductId();

    /**
     * @param int $product_id
     * @return BrandProductInterface
     */
    public function setProductId($product_id);

    /**
     * @return int
     */
    public function getPosition();


    /**
     * @param int $position
     * @return BrandProductInterface
     */
    public function setPosition($position);
}