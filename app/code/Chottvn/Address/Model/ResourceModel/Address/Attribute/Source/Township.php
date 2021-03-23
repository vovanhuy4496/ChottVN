<?php

namespace Chottvn\Address\Model\ResourceModel\Address\Attribute\Source;

class Township extends \Magento\Eav\Model\Entity\Attribute\Source\Table
{
    /**
     * @var \Chottvn\Address\Model\ResourceModel\Township\CollectionFactory
     */
    private $townshipFactory;

    /**
     * @var array
     */
    private $options;

    /**
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory $attrOptionFactory
     * @param \Chottvn\Address\Model\ResourceModel\Township\CollectionFactory $townshipFactory
     */
    public function __construct(
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory $attrOptionFactory,
        \Chottvn\Address\Model\ResourceModel\Township\CollectionFactory $townshipFactory
    ) {
        $this->townshipFactory = $townshipFactory;
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
        return [];
    }

    /**
     * @return \Chottvn\Checkout\Model\ResourceModel\Township\Collection
     */
    protected function _createCollection()
    {
        return $this->townshipFactory->create();
    }
}
