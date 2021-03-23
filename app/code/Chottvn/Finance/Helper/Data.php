<?php
declare(strict_types=1);

namespace Chottvn\Finance\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{
    /**
     * @var TransactionTypeFactory
     */
    public $transactionTypeFactory;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Chottvn\Finance\Model\TransactionTypeFactory $transactionTypeFactory
    ) {
        $this->transactionTypeFactory = $transactionTypeFactory;
        parent::__construct($context);
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return true;
    }
       /**
     * @param 
     *
     * @return Collection
     */
    public function getTransactionTypeCollection()
    {
        $collection = $this->transactionTypeFactory->create()->getCollection();
        return $collection;
    }
}

