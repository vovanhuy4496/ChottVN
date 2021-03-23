<?php

namespace Chottvn\OfflineShipping\Api;


interface TablerateRepositoryInterface
{
    /**
     * Save a Tablerate
     *
     * @param \Chottvn\OfflineShipping\Api\Data\TablerateInterface $tablerate
     * @return \Chottvn\OfflineShipping\Api\Data\TablerateInterface
     */
    public function save(Data\TablerateInterface  $tablerate);

    /**
     * Get Tablerate by an PK
     *
     * @param int $pk
     * @return \Blackbird\DataModelSample\Api\Data\TablerateInterface
     */
    public function getById($pk);

    /**
     * Delete a Tablerate
     *
     * @param \Blackbird\DataModelSample\Api\Data\TablerateInterface $pk
     * @return bool
     */
    public function delete(Data\TablerateInterface $pk);

    /**
     * Delete a Tablerate by an PK
     *
     * @param int $pk
     * @return bool
     */
    public function deleteById($pk);
}