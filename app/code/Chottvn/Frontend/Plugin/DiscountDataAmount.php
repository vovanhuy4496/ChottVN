<?php
 
namespace Chottvn\Frontend\Plugin;
 
class DiscountDataAmount
{
    protected $_helperData;

    public function __construct(\Chottvn\Frontend\Helper\Data $helperData)
    {
        $this->_helperData = $helperData;
    }

    public function afterGetAmount(\Magento\SalesRule\Model\Rule\Action\Discount\Data $subject, $result)
    {
        $precision = $this->_helperData->getPrecisionRoundPrice();
        return round($result,$precision);
    }

    public function afterGetBaseAmount(\Magento\SalesRule\Model\Rule\Action\Discount\Data $subject, $result)
    {
        $precision = $this->_helperData->getPrecisionRoundPrice();
        return round($result,$precision);
    }

    public function afterGetOriginalAmount(\Magento\SalesRule\Model\Rule\Action\Discount\Data $subject, $result)
    {
        $precision = $this->_helperData->getPrecisionRoundPrice();
        return round($result,$precision);
    }

    public function afterGetBaseOriginalAmount(\Magento\SalesRule\Model\Rule\Action\Discount\Data $subject, $result)
    {
        $precision = $this->_helperData->getPrecisionRoundPrice();
        return round($result,$precision);
    }
}