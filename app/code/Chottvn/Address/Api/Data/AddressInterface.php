<?php

namespace Chottvn\Address\Api\Data;

interface AddressInterface extends \Magento\Customer\Api\Data\AddressInterface
{
    const CITY_ID = 'city_id';
    const TOWNSHIP = 'township';
    const TOWNSHIP_ID = 'township_id';
    const EMAIL = 'email';
    
    /**
     * Get Address City Id
     * @return string
     */
    public function getCityId();
    
    /**
     * Set Address City Id
     * @param string $township
     * @return $this
     */
    public function setCityId($cityId);

    /**
     * Get Address Township
     * @return string
     */
    public function getTownship();
    
    /**
     * Set Address Township
     * @param string $township
     * @return $this
     */
    public function setTownship($township);

    /**
     * Get Address Township Id
     * @return int
     */
    public function getTownshipId();
    
    /**
     * Set Address Township Id
     * @param int $townshipId
     * @return $this
     */
    public function setTownshipId($townshipId);

    /**
     * Get email
     *
     * @return string|null
     */
    public function getEmail();

    /**
     * Set email
     *
     * @param string $email
     * @return $this
     */
    public function setEmail($email);
}
