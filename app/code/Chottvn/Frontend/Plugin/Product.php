<?php
 
namespace Chottvn\Frontend\Plugin;
 
class Product
{
	protected $_helperData;

	public function __construct(\Chottvn\Frontend\Helper\Data $helperData)
	{
		$this->_helperData = $helperData;
	}


    public function afterGetPrice(\Magento\Catalog\Model\Product $subject, $result)
    {
    	$precision = $this->_helperData->getPrecisionRoundPrice();
        return round($result,$precision);
    }

    public function afterGetFinalPrice(\Magento\Catalog\Model\Product $subject, $result)
    {
    	$precision = $this->_helperData->getPrecisionRoundPrice();
        return round($result,$precision);
    }
}