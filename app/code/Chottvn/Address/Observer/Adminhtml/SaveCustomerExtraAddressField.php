<?php

namespace Chottvn\Address\Observer\Adminhtml;

class SaveCustomerExtraAddressField implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Chottvn\Address\Model\CityFactory
     */
    protected $cityFactory;

    /**
     * @var \Chottvn\Address\Model\ResourceModel\City
     */
    protected $cityResource;

    /**
     * @var \Chottvn\Address\Model\TownshipFactory
     */
    protected $townshipFactory;

    /**
     * @var \Chottvn\Address\Model\ResourceModel\Township
     */
    protected $townshipResource;

    /**
     * @var \Chottvn\Address\Model\ResourceModel\Address
     */
    private $addressResource;

    /**
     * @param \Chottvn\Address\Model\CityFactory $cityFactory
     * @param \Chottvn\Address\Model\ResourceModel\City $cityResource
     * @param \Chottvn\Address\Model\TownshipFactory $townshipFactory
     * @param \Chottvn\Address\Model\ResourceModel\Township $townshipResource
     * @param \Chottvn\Address\Model\ResourceModel\Address $addressResource
     */
    public function __construct(
        \Chottvn\Address\Model\CityFactory $cityFactory,
        \Chottvn\Address\Model\ResourceModel\City $cityResource,
        \Chottvn\Address\Model\TownshipFactory $townshipFactory,
        \Chottvn\Address\Model\ResourceModel\Township $townshipResource,
        \Chottvn\Address\Model\ResourceModel\Address $addressResource
    ) {
        $this->cityFactory = $cityFactory;
        $this->cityResource = $cityResource;
        $this->townshipFactory = $townshipFactory;
        $this->townshipResource = $townshipResource;
        $this->addressResource = $addressResource;
    }

    /**
     * Save customer address attribute
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(
        \Magento\Framework\Event\Observer $observer
    ) {
        $request = $observer->getEvent()->getRequest();
        if ($addresses = $request->getParam('address')) {
            foreach ($addresses as $address) {
                if (array_key_exists('entity_id', $address)) {
                    if (array_key_exists('city_id', $address)) {
                        $city = $this->cityFactory->create();
                        $this->cityResource->load($city, $address['city_id']);
                        $address['city'] = $city->getDefaultName();
                    }
                    if (array_key_exists('township_id', $address)) {
                        $township = $this->townshipFactory->create();
                        $this->townshipResource->load($township, $address['township_id']);
                        $address['township'] = $township->getDefaultName();
                    }
                    $this->addressResource->updateAddressData($address['entity_id'], $address);
                }
            }
        }
    }
}
