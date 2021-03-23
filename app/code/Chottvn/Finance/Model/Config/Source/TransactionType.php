<?php
namespace Chottvn\Finance\Model\Config\Source;

class TransactionType implements \Magento\Framework\Option\ArrayInterface
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

  // get select option
  public function toOptionArray()
  {
    $options = [
      ['value' => '', 'label' => __('Select Transaction Type')],
    ];

    // get data transaction
    $resultPage = $this->transactionTypeCollectionFactory->create();
    $collection = $resultPage->getData();
    foreach ($collection as $item) {
        $getData = ['value' => $item["transactiontype_id"], 'label' => $item["name"]];
        array_push($options, $getData);
    }

    return $options;
  }
}