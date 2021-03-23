<?php

namespace Chottvn\Address\Model\ResourceModel;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;

class Region extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * construct
     * @return void
     */
    protected function _construct()
    {
        $this->_init('directory_country_region', 'region_id');
    }

    /**
     * Perform operations before object save
     *
     * @param AbstractModel $object
     * @return $this
     * @throws LocalizedException
     */
    protected function _beforeSave(AbstractModel $object)
    {
        if (!$this->_isRegionExist($object)) {
            throw new LocalizedException(__('The region already exists.'));
        }
    }

    /**
     * Save store labels.
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterSave(AbstractModel $object)
    {
        if ($object->hasStoreLabels()) {
            $this->saveStoreLabels($object->getId(), $object->getStoreLabels());
        }
        return parent::_afterSave($object);
    }

    /**
     * Save rule labels for different store views
     *
     * @param int $regionId
     * @param array $labels
     * @throws \Exception
     * @return $this
     */
    public function saveStoreLabels($regionId, $labels)
    {
        $deleteByLocale = [];
        $table = $this->getTable('directory_country_region_name');
        $connection = $this->getConnection();

        $data = [];
        foreach ($labels as $locale => $label) {
            if ($label != "") {
                $data[] = ['region_id' => $regionId, 'locale' => $locale, 'name' => $label];
            } else {
                $deleteByLocale[] = $locale;
            }
        }

        $connection->beginTransaction();
        try {
            if (!empty($data)) {
                $connection->insertOnDuplicate($table, $data, ['name']);
            }

            if (!empty($deleteByLocale)) {
                $connection->delete($table, ['region_id = ?' => $regionId, 'locale IN (?)' => $deleteByLocale]);
            }
        } catch (\Exception $e) {
            $connection->rollBack();
            throw $e;
        }
        $connection->commit();

        return $this;
    }

    /**
     * Get all existing rule labels
     *
     * @param int $regionId
     * @return array
     */
    public function getStoreLabels($regionId)
    {
        $select = $this->getConnection()->select()->from(
            $this->getTable('directory_country_region_name'),
            ['locale', 'name']
        )->where(
            'region_id = :region_id'
        );
        return $this->getConnection()->fetchPairs($select, [':region_id' => $regionId]);
    }

    /**
     * Validate region.
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return bool
     */
    private function _isRegionExist(AbstractModel $object)
    {
        if (!$this->_checkRegionAlreadyExist($object, 'default_name') ||
            !$this->_checkRegionAlreadyExist($object, 'code')
        ) {
            return false;
        }
        return true;
    }

    /**
     * Check unique region.
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return bool
     */
    private function _checkRegionAlreadyExist(AbstractModel $object, $field)
    {
        $select = $this->getConnection()->select()
            ->from(['dcr' => $this->getMainTable()])
            ->where('dcr.country_id = ?', $object->getData('country_id'))
            ->where('dcr.'.$field.' = ?', $object->getData($field));
        if ($object->getData('region_id')) {
            $select->where('dcr.region_id != ?', $object->getData('region_id'));
        }
        if ($this->getConnection()->fetchRow($select)) {
            return false;
        }
        return true;
    }
}
