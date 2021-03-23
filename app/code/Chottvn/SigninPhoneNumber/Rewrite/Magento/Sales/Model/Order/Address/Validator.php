<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Chottvn\SigninPhoneNumber\Rewrite\Magento\Sales\Model\Order\Address;

use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Directory\Model\CountryFactory;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Framework\App\ObjectManager;
use Magento\Sales\Model\Order\Address;

/**
 * Class Validator
 */
class Validator extends \Magento\Sales\Model\Order\Address\Validator
{
    /**
     * @var array
     */
    protected $required = [
        // 'parent_id' => 'Parent Order Id',
        // 'postcode' => 'Zip code',
        // 'lastname' => 'Last name',
        // 'street' => 'Street',
        // 'city' => 'City',
        // 'email' => 'Email',
        // 'country_id' => 'Country',
        // 'firstname' => 'First Name',
        // 'address_type' => 'Address Type',
    ];
    /**
     * Validate address.
     *
     * @param \Magento\Sales\Model\Order\Address $address
     * @return array
     */
    public function validate(Address $address)
    {
        $warnings = [];

        if ($this->isTelephoneRequired()) {
            $this->required['telephone'] = 'Phone Number';
        }

        if ($this->isCompanyRequired()) {
            $this->required['company'] = 'Company';
        }

        if ($this->isFaxRequired()) {
            $this->required['fax'] = 'Fax';
        }

        // foreach ($this->required as $code => $label) {
        //     if (!$address->hasData($code)) {
        //         $warnings[] = sprintf('"%s" is required. Enter and try again.', $label);
        //     }
        // }
        // if (!filter_var($address->getEmail(), FILTER_VALIDATE_EMAIL)) {
        //     $warnings[] = 'Email has a wrong format';
        // }
        if (!filter_var(in_array($address->getAddressType(), [Address::TYPE_BILLING, Address::TYPE_SHIPPING]))) {
            $warnings[] = 'Address type doesn\'t match required options';
        }
        $warnings = [];

        return $warnings;
    }

    /**
     * Validate address attribute for customer creation
     *
     * @return bool|array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     *
     * @param Address $address
     */
    public function validateForCustomer(Address $address)
    {
        if ($address->getShouldIgnoreValidation()) {
            return true;
        }

        $errors = [];

        if ($this->isEmpty($address->getFirstname())) {
            $errors[] = __('Please enter the first name.');
        }
        if ($this->isEmpty($address->getLastname())) {
            $errors[] = __('Please enter the last name.');
        }
        if ($this->isEmpty($address->getStreetLine(1))) {
            $errors[] = __('Please enter the street.');
        }
        if ($this->isEmpty($address->getCity())) {
            $errors[] = __('Please enter the city.');
        }

        if ($this->isTelephoneRequired()) {
            if ($this->isEmpty($address->getTelephone())) {
                $errors[] = __('Please enter the phone number.');
            }
        }

        if ($this->isCompanyRequired()) {
            if ($this->isEmpty($address->getCompany())) {
                $errors[] = __('Please enter the company.');
            }
        }

        if ($this->isFaxRequired()) {
            if ($this->isEmpty($address->getFax())) {
                $errors[] = __('Please enter the fax number.');
            }
        }

        $countryId = $address->getCountryId();

        if ($this->isZipRequired($countryId) && $this->isEmpty($address->getPostcode())) {
            $errors[] = __('Please enter the zip/postal code.');
        }
        if ($this->isEmpty($countryId)) {
            $errors[] = __('Please enter the country.');
        }
        if ($this->isStateRequired($countryId) && $this->isEmpty($address->getRegionId())) {
            $errors[] = __('Please enter the state/province.');
        }

        $errors = [];

        return empty($errors) ? true : $errors;
    }

    
}
