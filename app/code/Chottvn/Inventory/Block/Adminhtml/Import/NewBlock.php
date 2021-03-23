<?php
/**
 * Copyright © © 2020 chotructuyen.co All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Chottvn\Inventory\Block\Adminhtml\Import;

class NewBlock extends \Magento\Backend\Block\Template
{

	protected $_scopeConfig;
	
	protected $_logFactory;

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Template\Context  $context
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Chottvn\Inventory\Model\LogFactory $logFactory,
        array $data = []
    ) {
        $this->urlBuilder = $context->getUrlBuilder();
        $this->eavConfig = $eavConfig;
		$this->_scopeConfig = $scopeConfig;
		$this->_logFactory = $logFactory;
        parent::__construct($context, $data);
    }

    public function getSaveUrl()
    {
        return $this->urlBuilder->getUrl('*/*/save');
    }

    public function getDistributorOptions()
    {
        $attribute = $this->eavConfig->getAttribute('catalog_product', 'distributor');
        return $attribute->getSource()->getAllOptions();
    }

    public function getMaxDefaultStock()
    {
        return $this->_scopeConfig->getValue('cataloginventory/item_options/default_stock_max');
    }

    public function isProcessing() {
		$log = $this->_logFactory->create();
		$collection = $log->getCollection()
            ->addFieldToFilter('log_type', 'import')
			->addFieldToFilter('status', 'processing')
			->setOrder('log_id', 'ASC');
		$itemLog = $collection->getLastItem();

		if ($itemLog->getId()) {
			return true;
        }
        
        return false;
    }
}

