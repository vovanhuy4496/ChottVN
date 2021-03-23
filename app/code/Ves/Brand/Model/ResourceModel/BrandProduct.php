<?php
/**
 * Created by PhpStorm.
 * User: lhttram
 * Date: 3/12/18
 * Time: 2:36 PM
 */

namespace Ves\Brand\Model\ResourceModel;


class BrandProduct extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct() {
        $this->_init('ves_brand_product', 'brand_id');
    }
}