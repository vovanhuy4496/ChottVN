<?php
declare(strict_types=1);

namespace Chottvn\Finance\Model\TransactionType;

class TransactionType extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    protected $transactionTypeCollectionFactory;
    
    /**
     * * @param CollectionFactory $transactionTypeCollectionFactory
     */

    public function __construct(
        \Chottvn\Finance\Model\ResourceModel\TransactionType\CollectionFactory $transactionTypeCollectionFactory
    ) {
        $this->transactionTypeCollectionFactory = $transactionTypeCollectionFactory;
    }

    /**
     * getAllOptions
     *
     * @return array
     */
    public function getAllOptions()
    {
        if ($this->_options === null) {
            $resultPage = $this->transactionTypeCollectionFactory->create();
            $collection = $resultPage->getData();
            $setData = [];
            foreach ($collection as $item) {
                $getData = ['value' => $item["transactiontype_id"], 'label' => $item["name"], 'sort' => $item["transactiontype_id"]];
                // var_dump($getData);
                array_push($setData, $getData);
            }
            $this->_options = $setData;
            // var_dump($this->_options);
        }
        return $this->_options;
    }
}

