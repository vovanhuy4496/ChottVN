<?php declare(strict_types = 1);

/**
 * @author    Aurélien Jourquin <aurelien@growzup.com>
 * @link      http://www.ajourquin.com
 */

namespace Chottvn\Customer\Block\Adminhtml\Edit\Tab\View;

use Magento\Customer\Block\Adminhtml\Edit\Tab\View\PersonalInfo as MagentoPersonalInfo;
use Magento\Framework\Phrase;

class PersonalInfo extends MagentoPersonalInfo
{
    /**
     * @return Phrase
     */
    public function getAccountDisabled(): Phrase
    {
        $accountDisabled = 'No';

        if ($this->getCustomer()->getCustomAttribute('is_disabled')->getValue() === '1') {
            $accountDisabled = 'Yes';
        }

        return __($accountDisabled);
    }

    /**
     * Retrieve Status
     *
     * @return string|null
     */
    public function getStatus()
    {
        $customerModel = $this->getCustomerRegistry()->retrieve($this->getCustomerId());

        return $customerModel->getData('is_disabled');
    }

    public function getUrlLock() 
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $context = $objectManager->get('\Magento\Backend\Block\Template\Context');
        $urlBuilder = $context->getUrlBuilder();

        return $urlBuilder->getUrl('*/*/lock', ['id' => $this->getCustomerId()]);
    }

    public function getUrlUnLock() 
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $context = $objectManager->get('\Magento\Backend\Block\Template\Context');
        $urlBuilder = $context->getUrlBuilder();

        return $urlBuilder->getUrl('*/*/unlock', ['id' => $this->getCustomerId()]);
    }
}
