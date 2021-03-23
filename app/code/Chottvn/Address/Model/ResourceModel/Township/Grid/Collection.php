<?php

namespace Chottvn\Address\Model\ResourceModel\Township\Grid;

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
            ['drc' => $this->getTable('directory_region_city')],
            'main_table.city_id = drc.city_id',
            ['city_name' => 'drc.default_name']
        );
        $this->addFilterToMap(
            "city_name",
            "drc.default_name"
        );
        $this->addFilterToMap(
            "default_name",
            "main_table.default_name"
        );
        return $this;
    }
}
