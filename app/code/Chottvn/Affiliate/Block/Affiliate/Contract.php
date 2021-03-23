<?php
/**
 * Copyright © (c) Cho Truc Tuyen 2020 All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Chottvn\Affiliate\Block\Affiliate;

class Contract extends \Magento\Framework\View\Element\Template
{
    protected $storeManager;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context  $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->storeManager = $storeManager;
    }

	/**
     * Prepare layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {        
        return parent::_prepareLayout();
    }

    public function getAffiliateContractUrl()
    {
        // get url contract affiliate
        $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $contract_url = $mediaUrl.'affiliate_contract/contract.pdf';

        return $contract_url;
    }
}