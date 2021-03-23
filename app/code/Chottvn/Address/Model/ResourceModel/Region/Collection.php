<?php

namespace Chottvn\Address\Model\ResourceModel\Region;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Define resource model.
     */
    protected function _construct()
    {
        $this->_init(\Chottvn\Address\Model\Region::class, \Chottvn\Address\Model\ResourceModel\Region::class);

        $this->_idFieldName = 'region_id';
    }

    /**
     * Convert collection items to select options array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        foreach ($this as $item) {
            $option = [];
            $option['value'] = $item->getRegionId();
            $option['label'] = $item->getCountryId() . ' - ' . $item->getDefaultName();
            $options[] = $option;
        }

        if (!empty($options) > 0) {
            array_unshift(
                $options,
                ['title' => null, 'value' => null, 'label' => __('Please select a region.')]
            );
        }
        return $options;
    }
}
