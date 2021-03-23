<?php

/**
 * Copyright © © 2020 chotructuyen.co All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Chottvn\Inventory\Block\Adminhtml\Export;

class NewBlock extends \Magento\Backend\Block\Template
{

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Template\Context  $context
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Eav\Model\Config $eavConfig,
        array $data = []
    ) {
        $this->urlBuilder = $context->getUrlBuilder();
        $this->eavConfig = $eavConfig;
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
}
