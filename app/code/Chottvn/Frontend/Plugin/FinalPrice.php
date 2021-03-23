<?php
 
namespace Chottvn\Frontend\Plugin;
 
class FinalPrice
{
	protected $_helperData;

	public function __construct(\Chottvn\Frontend\Helper\Data $helperData)
	{
		$this->_helperData = $helperData;
	}

    public function afterGetValue(\Magento\Catalog\Pricing\Price\FinalPrice $subject, $result)
    {
    	$precision = $this->_helperData->getPrecisionRoundPrice();
        return round($result,$precision);
    }
}