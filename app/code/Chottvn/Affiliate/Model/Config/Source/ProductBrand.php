<?php
namespace Chottvn\Affiliate\Model\Config\Source;

class ProductBrand implements \Magento\Framework\Data\OptionSourceInterface
{
    protected $collectionFactory;
    protected $options;

    public function __construct(
        \Ves\Brand\Model\ResourceModel\Brand\CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @return array
     */
    public function toOptionArray() {
        if ($this->options === null) {
            $collection = $this->collectionFactory->create()->addFieldToFilter('status', '1');

            $this->options = [['label' => '', 'value' => '']];

            foreach ($collection as $brand) {
                $this->options[] = [
                    'label' => $brand->getName(),
                    'value' => $brand->getId()
                ];
            }
        }

        return $this->options;
    }
}