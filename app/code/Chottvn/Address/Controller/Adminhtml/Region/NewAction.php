<?php

namespace Chottvn\Address\Controller\Adminhtml\Region;

class NewAction extends \Chottvn\Address\Controller\Adminhtml\Address
{
    /**
     * Create new Region
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $this->_forward('edit');
    }
}
