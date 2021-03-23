<?php
declare(strict_types=1);

namespace Chottvn\Finance\Model\Transaction;

class Status extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
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
            $this->_options = [
                0 => [
                    'label' => 'Pending',
                    'value' => 0
                ],
                1 => [
                    'label' => 'Processing',
                    'value' => 1
                ],
                2  => [
                    'label' => 'Completed',
                    'value' => 10
                ],
                3 => [
                    'label' => 'Canceled',
                    'value' => 20
                ],
            ];
        }
        return $this->_options;
    }
}

