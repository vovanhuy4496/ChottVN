<?php

namespace Chottvn\Address\Model\ResourceModel\City\Grid;

class Collection extends \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult
{
    /**
     * init select
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        
        $this->getSelect()->joinLeft(
            ['dcr' => $this->getTable('directory_country_region')],
            'main_table.region_id = dcr.region_id',
            ['region_name' => 'dcr.default_name']
        );
        $this->addFilterToMap(
            "region_name",
            "dcr.default_name"
        );
        $this->addFilterToMap(
            "default_name",
            "main_table.default_name"
        );
        return $this;
    }
}
