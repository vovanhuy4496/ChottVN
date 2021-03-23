<?php
namespace Chottvn\SalesRule\Plugin;

class CalculatorFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    protected $classByType = [
        \Chottvn\SalesRule\Api\Data\GiftRuleInterface::ORDER_VOUCHER   => 'Chottvn\SalesRule\Model\Rule\Action\Discount\Voucher'
    ];

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->_objectManager = $objectManager;
    }

    public function aroundCreate(
        \Magento\SalesRule\Model\Rule\Action\Discount\CalculatorFactory $subject,
        \Closure $proceed,
        $type
    ) {
        if (isset($this->classByType[$type])) {
            return $this->_objectManager->create($this->classByType[$type]);
        }

        return $proceed($type);
    }
}
