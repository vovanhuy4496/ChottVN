<?php

namespace Chottvn\Address\Ui\Component\Form\Township;

use Magento\Framework\App\Request\DataPersistorInterface;
use Chottvn\Address\Model\ResourceModel\Township\Collection;

class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var Collection
     */
    protected $collection;
    
    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var array
     */
    private $loadedData;

    /**
     * Construct
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param Collection $collection
     * @param DataPersistorInterface $dataPersistor
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        Collection $collection,
        DataPersistorInterface $dataPersistor,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collection;
        $this->dataPersistor = $dataPersistor;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (!$this->loadedData) {
            $items = $this->collection->getItems();
            foreach ($items as $item) {
                $this->loadedData[$item->getId()] = $item->getData();
            }

            $data = $this->dataPersistor->get('township_item');
            if (!empty($data)) {
                $townshipItem = $this->collection->getNewEmptyItem();
                $townshipItem->setData($data);
                $this->loadedData[$townshipItem->getId()] = $townshipItem->getData();
                $this->dataPersistor->clear('township_item');
            }
        }
        return $this->loadedData;
    }
}
