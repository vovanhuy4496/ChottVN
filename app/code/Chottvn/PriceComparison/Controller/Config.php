<?php

namespace Chottvn\PriceComparison\Controller\Index;

class Config extends \Magento\Framework\App\Action\Action
{

	protected $helperData;

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Chottvn\PriceComparison\Helper\Data $helperData

	)
	{
		$this->helperData = $helperData;
		return parent::__construct($context);
	}

	public function execute()
	{

		// TODO: Implement execute() method.

		echo $this->helperData->getGeneralConfig('url_api_price_comparison').'<br>';
		echo $this->helperData->getGeneralConfig('authorization_price_comparison');
		exit();

	}
}