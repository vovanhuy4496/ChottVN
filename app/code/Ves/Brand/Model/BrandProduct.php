<?php
/**
 * Created by PhpStorm.
 * User: lhttram
 * Date: 3/12/18
 * Time: 2:31 PM
 */

namespace Ves\Brand\Model;

use Ves\Brand\Api\Data\BrandProductInterface;

/**
 * Class BrandProduct
 * @package Ves\Brand\Model
 */
class BrandProduct extends \Magento\Framework\Model\AbstractModel implements BrandProductInterface
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct() {
        $this->_init(\Ves\Brand\Model\ResourceModel\BrandProduct::class);
    }

    /**
     * @return int
     */
    public function getBrandId()
    {
        return $this->getData(self::BRAND_ID);
    }

    /**
     * @param int $brand_id
     * @return BrandProductInterface
     */
    public function setBrandId($brand_id)
    {
        return $this->setData(self::BRAND_ID, $brand_id);
    }

    /**
     * @return int
     */
    public function getProductId()
    {
        return $this->getData(self::PRODUCT_ID);
    }

    /**
     * @param int $product_id
     * @return BrandProductInterface
     */
    public function setProductId($product_id)
    {
        return $this->setData(self::PRODUCT_ID, $product_id);
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->getData(self::POSITOIN);
    }

    /**
     * @param int $position
     * @return BrandProductInterface
     */
    public function setPosition($position)
    {
        return $this->setData(self::POSITOIN, $position);
    }

    /**
     * @param array $brandIds
     * @return \Magento\Framework\DataObject
     */
    public function loadProductIdsByBrandIds($brandIds)
    {
        $collection = $this->getCollection()
            ->addFieldToFilter('brand_id', ['in' => $brandIds])->addFieldToSelect('product_id');
        $collection->getSelect()->group('product_id');
        $productIds = $collection->getColumnValues('product_id');
        return $productIds;
    }

    /**
     * @param array $productIds
     * @param array $brandIds
     * @return \Magento\Framework\DataObject
     */
    public function loadBrandIdsByProductIds($brandIds, $productIds)
    {
        $collection = $this->getCollection()
            ->addFieldToFilter('brand_id', ['in' => $brandIds])
            ->addFieldToFilter('product_id', ['in' => $productIds])->addFieldToSelect('brand_id');
        $collection->getSelect()->group('brand_id');
        $brandIds = $collection->getColumnValues('brand_id');
        return $brandIds;
    }
}