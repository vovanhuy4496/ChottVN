<?php
/**
 * Created by PhpStorm.
 * User: lhttram
 * Date: 3/1/19
 * Time: 3:28 PM
 */

namespace Ves\Brand\Model\ResourceModel\BrandProduct;


class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Initialize resource model for collection
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('Ves\Brand\Model\BrandProduct', 'Ves\Brand\Model\ResourceModel\BrandProduct');
    }

    /**
     * @param array $brandIds
     * @return mixed
     */
    public function loadProductIdsByBrandIds($brandIds)
    {
        $collection = $this->getCollection()
            ->addFieldToFilter('brand_id', ['in' => $brandIds]);
        return $collection;
    }
}