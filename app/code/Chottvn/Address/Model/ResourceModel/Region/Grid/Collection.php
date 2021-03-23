<?php

namespace Chottvn\Address\Model\ResourceModel\Region\Grid;

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

        $this->addFilterToMap(
            "default_name",
            "main_table.default_name"
        );
    }
}
