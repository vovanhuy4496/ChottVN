<?php

namespace Chottvn\Address\Model\ResourceModel\Address\Attribute\Source;

class City extends \Magento\Eav\Model\Entity\Attribute\Source\Table
{
    /**
     * @var \Chottvn\Address\Model\ResourceModel\City\CollectionFactory
     */
    private $cityFactory;

    /**
     * @var array
     */
    private $options;

    /**
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory $attrOptionFactory
     * @param \Chottvn\Address\Model\ResourceModel\City\CollectionFactory $cityFactory
     */
    public function __construct(
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory $attrOptionFactory,
        \Chottvn\Address\Model\ResourceModel\City\CollectionFactory $cityFactory
    ) {
        $this->cityFactory = $cityFactory;
        parent::__construct($attrOptionCollectionFactory, $attrOptionFactory);
    }

    /**
     * Retrieve all region options
     *
     * @return array
     */
    public function getAllOptions($withEmpty = true, $defaultValues = false)
    {
        if (!$this->options) {
            $this->options = $this->_createCollection()->toOptionArray();
        }
        return $this->options;
    }

    /**
     * @return \Chottvn\Checkout\Model\ResourceModel\City\Collection
     */
    protected function _createCollection()
    {
        return $this->cityFactory->create();
    }
}
