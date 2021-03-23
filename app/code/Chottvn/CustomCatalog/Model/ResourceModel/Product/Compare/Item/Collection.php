<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Chottvn\CustomCatalog\Model\ResourceModel\Product\Compare\Item;

use Magento\Catalog\Model\ResourceModel\Product\Compare\Item\Collection as PrimaryCollection;

class Collection extends PrimaryCollection
{

    /**
     * Retrieve Merged comparable attributes for compared product items
     *
     * @return array
     */
    public function getComparableAttributes()
    {
        if ($this->_comparableAttributes === null) {
            $this->_comparableAttributes = [];
            $setIds = $this->_getAttributeSetIds();
            if ($setIds) {
                $attributeIds = $this->_getAttributeIdsBySetIds($setIds);

                $select = $this->getConnection()->select()->from(
                    ['main_table' => $this->getTable('eav_attribute')]
                )->join(
                    ['additional_table' => $this->getTable('catalog_eav_attribute')],
                    'additional_table.attribute_id=main_table.attribute_id'
                )->joinLeft(
                    ['al' => $this->getTable('eav_attribute_label')],
                    'al.attribute_id = main_table.attribute_id AND al.store_id = ' . (int)$this->getStoreId(),
                    [
                        'store_label' => $this->getConnection()->getCheckSql(
                            'al.value IS NULL',
                            'main_table.frontend_label',
                            'al.value'
                        )
                    ]
                )->joinLeft(
                    ['eea' => $this->getTable('eav_entity_attribute')], 
                    'eea.attribute_id = main_table.attribute_id'
                )->where(
                    'additional_table.is_comparable=?',
                    1
                )->where(
                    'main_table.attribute_id IN(?)',
                    $attributeIds
                )->where(
                    'eea.attribute_set_id IN(?)', $setIds
                )->order(array('eea.attribute_group_id ASC', 'eea.sort_order ASC'));

                // print_r($select->__toString());
                // print_r(get_class_methods($select));exit;
                $attributesData = $this->getConnection()->fetchAll($select);
                if ($attributesData) {
                    $entityType = \Magento\Catalog\Model\Product::ENTITY;
                    $this->_eavConfig->importAttributesData($entityType, $attributesData);
                    foreach ($attributesData as $data) {
                        $attribute = $this->_eavConfig->getAttribute($entityType, $data['attribute_code']);
                        $this->_comparableAttributes[$attribute->getAttributeCode()] = $attribute;
                    }
                    unset($attributesData);
                }
            }
        }
        return $this->_comparableAttributes;
    }

}
