<?php

namespace Chottvn\Address\Model\ResourceModel;

class Address extends \Magento\Customer\Model\ResourceModel\Address
{
    /**
     * Update customer address data.
     *
     * @param int $addressId
     * @param array $addressData
     * @return void
     */
    public function updateAddressData($addressId, $addressData)
    {
        $update = [];
        if (array_key_exists('city_id', $addressData)) {
            $update['city_id'] = $addressData['city_id'];
        }
        if (array_key_exists('township_id', $addressData)) {
            $update['township_id'] = $addressData['township_id'];
        }
        if (array_key_exists('township', $addressData)) {
            $update['township'] = $addressData['township'];
        }
        if (!empty($update) > 0) {
            $where = ['entity_id = ?' => $addressId];
            $this->getConnection()->update($this->getEntityTable(), $update, $where);
        }
    }

    /**
     * Get regions by country id.
     *
     * @param int $countryId
     * @return array
     */
    public function getListRegions($countryId)
    {
        $connection = $this->getConnection();
        $regionTable = $this->getTable('directory_country_region');
        $select = $connection->select()
                    ->from(['dcr' => $regionTable], ['code', 'region_id'])
                    ->where('country_id = ?', $countryId);
        return $connection->fetchPairs($select);
    }

    /**
     * Import region data.
     *
     * @param array $importRawData
     * @return void
     */
    public function importRegion($importRawData)
    {
        $connection = $this->getConnection();
        $table = $this->getTable('directory_country_region');
        $insert = [];
        foreach ($importRawData as $data) {
            $select = $connection->select()
                        ->from(['dcr' => $table], 'COUNT(*)')
                        ->where('country_id = ?', $data[0])
                        ->where('code = ?', $data[1]);
            $count = $connection->fetchOne($select);
            if ($count > 0) {
                $update = ['default_name' => $data[2]];
                $where = ['country_id = ?' => $data[0],'code = ?' => $data[1]];
                $connection->update($table, $update, $where);
            } else {
                $insert[] = ['country_id' => $data[0], 'code' => $data[1], 'default_name' => $data[2]];
            }
        }
        if (!empty($insert)) {
            $connection->insertMultiple($table, $insert);
        }
    }

    /**
     * Import city data.
     *
     * @param array $regions
     * @param array $importRawData
     * @return void
     */
    public function importCity($regions, $importRawData)
    {
        $connection = $this->getConnection();
        $table = $this->getTable('directory_region_city');
        $insert = [];
        foreach ($importRawData as $data) {
            $select = $connection->select()->from(['drc' => $table], ['city_id'])->where('default_name = ?', $data[1]);
            $city_id = $connection->fetchOne($select);
            if ($city_id > 0) {
                $update = ['region_id' => $regions[$data[0]], 'default_name' => $data[1]];
                $where = ['city_id = ?' => $city_id];
                $connection->update($table, $update, $where);
            } else {
                $insert[] = ['default_name' => $data[1], 'region_id' => $regions[$data[0]]];
            }
        }
        if (!empty($insert)) {
            $connection->insertMultiple($table, $insert);
        }
    }

    /**
     * Import township data.
     *
     * @param array $importRawData
     * @return void
     */
    public function importTownship($importRawData)
    {
        $connection = $this->getConnection();
        $regionTable = $this->getTable('directory_country_region');
        $cityTable = $this->getTable('directory_region_city');
        $townshipTable = $this->getTable('directory_city_township');
        $insert = [];
        foreach ($importRawData as $data) {
            $select = $connection->select()
                                ->from(['drc' => $cityTable], ['city_id'])
                                ->joinLeft(
                                    ['dcr' => $regionTable],
                                    'drc.region_id = dcr.region_id',
                                    []
                                )
                                ->where('dcr.default_name = ?', $data[0])
                                ->where('drc.default_name = ?', $data[1]);

            $city_id = $connection->fetchOne($select);
            if ($city_id) {
                $query = $connection->select()
                            ->from(['dct' => $townshipTable], ['township_id'])
                            ->where('city_id = ?', $city_id)
                            ->where('default_name = ?', $data[2]);
                $township_id = $connection->fetchOne($query);
                if ($township_id) {
                    $update = ['city_id' => $city_id,'default_name' => $data[2],'postcode' => $data[3]];
                    $where = ['township_id = ?' => $township_id];
                    $connection->update($townshipTable, $update, $where);
                } else {
                    $insert[] = ['city_id' => $city_id, 'default_name' => $data[2], 'postcode' => $data[3]];
                }
            }
        }
        if (!empty($insert)) {
            $connection->insertMultiple($townshipTable, $insert);
        }
    }
}
