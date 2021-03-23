<?php

namespace Chottvn\Address\Model\ResourceModel;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;

class City extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * construct
     * @return void
     */
    protected function _construct()
    {
        $this->_init('directory_region_city', 'city_id');
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
        if (!$this->_isCityExist($object)) {
            throw new LocalizedException(__('The city already exists.'));
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
     * @param int $cityId
     * @param array $labels
     * @throws \Exception
     * @return $this
     */
    public function saveStoreLabels($cityId, $labels)
    {
        $deleteByLocale = [];
        $table = $this->getTable('directory_region_city_name');
        $connection = $this->getConnection();

        $data = [];
        foreach ($labels as $locale => $label) {
            if ($label != "") {
                $data[] = ['city_id' => $cityId, 'locale' => $locale, 'name' => $label];
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
                $connection->delete($table, ['city_id = ?' => $cityId, 'locale IN (?)' => $deleteByLocale]);
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
     * @param int $cityId
     * @return array
     */
    public function getStoreLabels($cityId)
    {
        $select = $this->getConnection()->select()->from(
            $this->getTable('directory_region_city_name'),
            ['locale', 'name']
        )->where(
            'city_id = :city_id'
        );
        return $this->getConnection()->fetchPairs($select, [':city_id' => $cityId]);
    }

    /**
     * Validate city.
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return bool
     */
    private function _isCityExist(AbstractModel $object)
    {
        if (!$this->_checkCityAlreadyExist($object, 'default_name') ||
            !$this->_checkCityAlreadyExist($object, 'code')
        ) {
            return false;
        }
        return true;
    }

    /**
     * Check unique city.
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return bool
     */
    private function _checkCityAlreadyExist(AbstractModel $object, $field)
    {
        $select = $this->getConnection()->select()
            ->from(['drc' => $this->getMainTable()])
            ->where('drc.region_id = ?', $object->getData('region_id'))
            ->where('drc.'.$field.' = ?', $object->getData($field));
        if ($object->getData('city_id')) {
            $select->where('drc.city_id != ?', $object->getData('city_id'));
        }
        if ($this->getConnection()->fetchRow($select)) {
            return false;
        }
        return true;
    }
}
