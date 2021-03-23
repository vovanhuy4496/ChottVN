<?php

namespace Chottvn\Address\Model\ResourceModel;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;

class Township extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * construct
     * @return void
     */
    protected function _construct()
    {
        $this->_init('directory_city_township', 'township_id');
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
     * @param int $townshipId
     * @param array $labels
     * @throws \Exception
     * @return $this
     */
    public function saveStoreLabels($townshipId, $labels)
    {
        $deleteByLocale = [];
        $table = $this->getTable('directory_city_township_name');
        $connection = $this->getConnection();

        $data = [];
        foreach ($labels as $locale => $label) {
            if ($label != "") {
                $data[] = ['township_id' => $townshipId, 'locale' => $locale, 'name' => $label];
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
                $connection->delete($table, ['township_id = ?' => $townshipId, 'locale IN (?)' => $deleteByLocale]);
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
     * @param int $townshipId
     * @return array
     */
    public function getStoreLabels($townshipId)
    {
        $select = $this->getConnection()->select()->from(
            $this->getTable('directory_city_township_name'),
            ['locale', 'name']
        )->where(
            'township_id = :township_id'
        );
        return $this->getConnection()->fetchPairs($select, [':township_id' => $townshipId]);
    }
}
